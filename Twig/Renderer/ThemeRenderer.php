<?php

namespace ES\Bundle\BaseBundle\Twig\Renderer;

class ThemeRenderer
{
	/**
	 * @var ThemeRendererEngine
	 */
	private $engine;

	/**
	 * @var array
	 */
	private $blockNameHierarchyMap = array();

	/**
	 * @var array
	 */
	private $hierarchyLevelMap = array();

	/**
	 * @var array
	 */
	private $variableStack;

	public function __construct(ThemeRendererEngine $engine)
	{
		$this->engine = $engine;
	}

	public function setTheme($themes)
	{
		$this->engine->setTheme($themes);
	}

	public function searchAndRenderBlock(array $typeHierarchy, $blockNameSuffix, array $variables = array())
	{
		$viewAndSuffixCacheKey = $blockNameSuffix;

		// In templates, we have to deal with two kinds of block hierarchies:
		//
		//   +---------+          +---------+
		//   | Theme B | -------> | Theme A |
		//   +---------+          +---------+
		//
		//   form_widget -------> form_widget
		//       ^
		//       |
		//  choice_widget -----> choice_widget
		//
		// The first kind of hierarchy is the theme hierarchy. This allows to
		// override the block "choice_widget" from Theme A in the extending
		// Theme B. This kind of inheritance needs to be supported by the
		// template engine and, for example, offers "parent()" or similar
		// functions to fall back from the custom to the parent implementation.
		//
		// The second kind of hierarchy is the form type hierarchy. This allows
		// to implement a custom "choice_widget" block (no matter in which theme),
		// or to fallback to the block of the parent type, which would be
		// "form_widget" in this example (again, no matter in which theme).
		// If the designer wants to explicitly fallback to "form_widget" in his
		// custom "choice_widget", for example because he only wants to wrap
		// a <div> around the original implementation, he can simply call the
		// widget() function again to render the block for the parent type.
		//
		// The second kind is implemented in the following blocks.
		if (!isset($this->blockNameHierarchyMap[$viewAndSuffixCacheKey])) {
			// INITIAL CALL
			// Calculate the hierarchy of template blocks and start on
			// the bottom level of the hierarchy (= "_<id>_<section>" block)
			$blockNameHierarchy = array();
			foreach ($typeHierarchy as $blockNamePrefix) {
				$blockNameHierarchy[] = $blockNamePrefix . '_' . $blockNameSuffix;
			}
			$hierarchyLevel = count($blockNameHierarchy) - 1;

			$hierarchyInit = true;
		} else {
			// RECURSIVE CALL
			// If a block recursively calls searchAndRenderBlock() again, resume rendering
			// using the parent type in the hierarchy.
			$blockNameHierarchy = $this->blockNameHierarchyMap[$viewAndSuffixCacheKey];
			$hierarchyLevel     = $this->hierarchyLevelMap[$viewAndSuffixCacheKey] - 1;

			$hierarchyInit = false;
		}

		// The variables are cached globally for a view (instead of for the
		// current suffix)
		if (null === $this->variableStack) {
			$this->variableStack = array();

			// The default variable scope contains all view variables, merged with
			// the variables passed explicitly to the helper
			$scopeVariables = array();

			$varInit = true;
		} else {
			// Reuse the current scope and merge it with the explicitly passed variables
			$scopeVariables = end($this->variableStack);

			$varInit = false;
		}

		// Load the resource where this block can be found
		$resource = $this->engine->getResourceForBlockNameHierarchy($blockNameHierarchy, $hierarchyLevel);

		// Escape if no resource exists for this block
		if (!$resource) {
			throw new \LogicException(sprintf(
				'Unable to render the form as none of the following blocks exist: "%s".',
				implode('", "', array_reverse($blockNameHierarchy))
			));
		}

		// Update the current hierarchy level to the one at which the resource was
		// found. For example, if looking for "choice_widget", but only a resource
		// is found for its parent "form_widget", then the level is updated here
		// to the parent level.
		$hierarchyLevel = $this->engine->getResourceHierarchyLevel($blockNameHierarchy, $hierarchyLevel);

		// The actually existing block name in $resource
		$blockName = $blockNameHierarchy[$hierarchyLevel];


		// In order to make recursive calls possible, we need to store the block hierarchy,
		// the current level of the hierarchy and the variables so that this method can
		// resume rendering one level higher of the hierarchy when it is called recursively.
		//
		// We need to store these values in maps (associative arrays) because within a
		// call to widget() another call to widget() can be made, but for a different view
		// object. These nested calls should not override each other.
		$this->blockNameHierarchyMap[$viewAndSuffixCacheKey] = $blockNameHierarchy;
		$this->hierarchyLevelMap[$viewAndSuffixCacheKey]     = $hierarchyLevel;

		// We also need to store the variables for the view so that we can render other
		// blocks for the same view using the same variables as in the outer block.
		$this->variableStack[] = $variables;

		// Do the rendering
		$html = $this->engine->renderBlock($resource, $blockName, $variables);

		// Clear the stack
		array_pop($this->variableStack);

		// Clear the caches if they were filled for the first time within
		// this function call
		if ($hierarchyInit) {
			unset($this->blockNameHierarchyMap[$viewAndSuffixCacheKey]);
			unset($this->hierarchyLevelMap[$viewAndSuffixCacheKey]);
		}

		if ($varInit) {
			unset($this->variableStack);
		}

		return $html;
	}
}
