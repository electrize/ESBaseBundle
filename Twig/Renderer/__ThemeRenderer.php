<?php

namespace ES\Bundle\BaseBundle\Twig\Renderer;

class ___ThemeRenderer
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
	 * @var array
	 */
	protected $resources = array();

	/**
	 * @var string
	 */
	private $themes;

	public function __construct(\Twig_Environment $environment, array $themes)
	{
		$this->environment = $environment;
		$this->themes      = $themes;
	}

	/**
	 * @param array $blockNames A list of blocks ordered by priority
	 * @param array $parameters
	 * @return string
	 * @throws \RuntimeException
	 */
	public function searchAndRenderBlock(array $blockNames, array $parameters = array())
	{
		foreach ($blockNames as $blockName) {
			/** @var \Twig_Template $resource */
			if ($resource = $this->getResourceForBlockName($blockName)) {
				break;
			}
		}

		// Escape if no resource exists for this block
		if (!$resource) {
			throw new \RuntimeException(sprintf(
				'Unable to render the object as none of the following blocks exist: "%s".',
				implode('", "', $blockNames)
			));
		}

		var_dump($blockName, $resource[0]->getTemplateName());
		$html = $this->renderBlock($resource, $blockName, $parameters);
		var_dump($html);

		return $html;
	}

	public function getResourceForBlockName($blockName)
	{
		if (!isset($this->resources[$blockName])) {
			$this->loadResourceForBlockName($blockName);
		}

		return $this->resources[$blockName];
	}

	public function renderBlock($resource, $blockName, array $parameters = array())
	{
		$context = $this->environment->mergeGlobals($parameters);

		ob_start();

		$this->template->displayBlock($blockName, $context, $resource);

		return ob_get_clean();
	}

	protected function loadResourceForBlockName($blockName)
	{
		if (isset($this->resources[$blockName])) {
			return $this->resources[$blockName];
		}

		for ($i = count($this->themes) - 1; $i >= 0; --$i) {
			$this->loadResourcesFromTheme($this->themes[$i]);
		}

		if (!isset($this->resources[$blockName])) {
			$this->resources[$blockName] = false;
		}

		return false !== $this->resources[$blockName];
	}

	protected function loadResourcesFromTheme(&$theme)
	{
		if (!$theme instanceof \Twig_Template) {
			$theme = $this->environment->loadTemplate($theme);
		}

		if (null === $this->template) {
			// Store the first \Twig_Template instance that we find so that
			// we can call displayBlock() later on. It doesn't matter *which*
			// template we use for that, since we pass the used blocks manually
			// anyway.
			$this->template = $theme;
		}
		$currentTheme = $theme;
		$context      = $this->environment->mergeGlobals(array());

		do {
			foreach ($currentTheme->getBlocks() as $block => $blockData) {
				if (!isset($this->resources[$block])) {
					$this->resources[$block] = $blockData;
				}
			}
		} while (false !== $currentTheme = $currentTheme->getParent($context));
	}
}
