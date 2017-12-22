<?php

namespace ES\Bundle\BaseBundle\Twig\Renderer;

class ThemeRendererEngine extends AbstractRendererEngine
{
	/**
	 * @var \Twig_Environment
	 */
	private $environment;

	/**
	 * @var \Twig_Template
	 */
	private $template;

	/**
	 * {@inheritdoc}
	 */
	public function setEnvironment(\Twig_Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function renderBlock($resource, $blockName, array $variables = array())
	{
		$context = $this->environment->mergeGlobals($variables);

		ob_start();

		// By contract,This method can only be called after getting the resource
		// (which is passed to the method). Getting a resource for the first time
		// (with an empty cache) is guaranteed to invoke loadResourcesFromTheme(),
		// where the property $template is initialized.

		// We do not call renderBlock here to avoid too many nested level calls
		// (XDebug limits the level to 100 by default)
		$this->template->displayBlock($blockName, $context, $this->resources);

		return ob_get_clean();
	}

	/**
	 * Loads the cache with the resource for a given block name.
	 *
	 * This implementation eagerly loads all blocks of the themes assigned to the given view
	 * and all of its ancestors views. This is necessary, because Twig receives the
	 * list of blocks later. At that point, all blocks must already be loaded, for the
	 * case that the function "block()" is used in the Twig template.
	 *
	 * @see getResourceForBlock()
	 *
	 * @param string $blockName The name of the block to load.
	 *
	 * @return bool    True if the resource could be loaded, false otherwise.
	 */
	protected function loadResourceForBlockName($blockName)
	{
		// The caller guarantees that $this->resources[$cacheKey][$block] is
		// not set, but it doesn't have to check whether $this->resources[$cacheKey]
		// is set. If $this->resources[$cacheKey] is set, all themes for this
		// $cacheKey are already loaded (due to the eager population, see doc comment).
		if (null !== $this->resources) {
			// As said in the previous, the caller guarantees that
			// $this->resources[$cacheKey][$block] is not set. Since the themes are
			// already loaded, it can only be a non-existing block.
			$this->resources[$blockName] = null;

			return false;
		}

		// Recursively try to find the block in the themes assigned to $view,
		// then of its parent view, then of the parent view of the parent and so on.
		// When the root view is reached in this recursion, also the default
		// themes are taken into account.

		// Check each theme whether it contains the searched block
		if (isset($this->themes)) {
			for ($i = count($this->themes) - 1; $i >= 0; --$i) {
				$this->loadResourcesFromTheme($this->themes[$i]);
				// CONTINUE LOADING (see doc comment)
			}
		}

		for ($i = count($this->defaultThemes) - 1; $i >= 0; --$i) {
			$this->loadResourcesFromTheme($this->defaultThemes[$i]);
			// CONTINUE LOADING (see doc comment)
		}

		// Proceed with the themes of the parent view

		// Even though we loaded the themes, it can happen that none of them
		// contains the searched block
		if (!isset($this->resources[$blockName])) {
			// Cache that we didn't find anything to speed up further accesses
			$this->resources[$blockName] = false;
		}

		return false !== $this->resources[$blockName];
	}

	/**
	 * Loads the resources for all blocks in a theme.
	 *
	 * @param mixed  $theme    The theme to load the block from. This parameter
	 *                         is passed by reference, because it might be necessary
	 *                         to initialize the theme first. Any changes made to
	 *                         this variable will be kept and be available upon
	 *                         further calls to this method using the same theme.
	 */
	protected function loadResourcesFromTheme(&$theme)
	{
		if (!$theme instanceof \Twig_Template) {
			/* @var \Twig_Template $theme */
			$theme = $this->environment->loadTemplate($theme);
		}

		if (null === $this->template) {
			// Store the first \Twig_Template instance that we find so that
			// we can call displayBlock() later on. It doesn't matter *which*
			// template we use for that, since we pass the used blocks manually
			// anyway.
			$this->template = $theme;
		}

		// Use a separate variable for the inheritance traversal, because
		// theme is a reference and we don't want to change it.
		$currentTheme = $theme;

		$context = $this->environment->mergeGlobals(array());

		// The do loop takes care of template inheritance.
		// Add blocks from all templates in the inheritance tree, but avoid
		// overriding blocks already set.
		do {
			foreach ($currentTheme->getBlocks() as $block => $blockData) {
				if (!isset($this->resources[$block])) {
					// The resource given back is the key to the bucket that
					// contains this block.
					$this->resources[$block] = $blockData;
				}
			}
		} while (false !== $currentTheme = $currentTheme->getParent($context));
	}
}
