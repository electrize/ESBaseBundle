<?php

namespace ES\Bundle\BaseBundle\Twig\Renderer;

abstract class AbstractRendererEngine
{
	/**
	 * @var array
	 */
	protected $defaultThemes;

	/**
	 * @var array
	 */
	protected $themes = array();

	/**
	 * @var array
	 */
	protected $resources;

	/**
	 * @var array
	 */
	private $resourceHierarchyLevels = array();

	/**
	 * Creates a new renderer engine.
	 *
	 * @param array $defaultThemes The default themes. The type of these
	 *                             themes is open to the implementation.
	 */
	public function __construct(array $defaultThemes = array())
	{
		$this->defaultThemes = $defaultThemes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTheme($themes)
	{
		// Do not cast, as casting turns objects into arrays of properties
		$this->themes = is_array($themes) ? $themes : array($themes);

		// Unset instead of resetting to an empty array, in order to allow
		// implementations (like TwigRendererEngine) to check whether $cacheKey
		// is set at all.
		$this->resources = null;
		$this->resourceHierarchyLevels = null;
	}

	public function getResourceForBlockName(ItemInterface $item, $blockName)
	{
		$cacheKey = $item->getUniqueId();

		if (!isset($this->resources[$cacheKey][$blockName])) {
			$this->loadResourceForBlockName($cacheKey, $item, $blockName);
		}

		return $this->resources[$cacheKey][$blockName];
	}

	public function getResourceForBlockNameHierarchy(array $blockNameHierarchy, $hierarchyLevel)
	{
		$blockName = $blockNameHierarchy[$hierarchyLevel];

		if (!isset($this->resources[$blockName])) {
			$this->loadResourceForBlockNameHierarchy($blockNameHierarchy, $hierarchyLevel);
		}

		return $this->resources[$blockName];
	}

	public function getResourceHierarchyLevel(array $blockNameHierarchy, $hierarchyLevel)
	{
		$blockName = $blockNameHierarchy[$hierarchyLevel];

		if (!isset($this->resources[$blockName])) {
			$this->loadResourceForBlockNameHierarchy($blockNameHierarchy, $hierarchyLevel);
		}

		// If $block was previously rendered loaded with loadTemplateForBlock(), the template
		// is cached but the hierarchy level is not. In this case, we know that the  block
		// exists at this very hierarchy level, so we can just set it.
		if (!isset($this->resourceHierarchyLevels[$blockName])) {
			$this->resourceHierarchyLevels[$blockName] = $hierarchyLevel;
		}

		return $this->resourceHierarchyLevels[$blockName];
	}

	/**
	 * Loads the cache with the resource for a given block name.
	 *
	 * @see getResourceForBlock()
	 *
	 * @param string $blockName The name of the block to load.
	 *
	 * @return bool    True if the resource could be loaded, false otherwise.
	 */
	abstract protected function loadResourceForBlockName($blockName);

	/**
	 * Loads the cache with the resource for a specific level of a block hierarchy.
	 *
	 * @see getResourceForBlockHierarchy()
	 *
	 *                                          themes.
	 * @param array $blockNameHierarchy         The block hierarchy, with the most
	 *                                          specific block name at the end.
	 * @param int   $hierarchyLevel             The level in the block hierarchy that
	 *                                          should be loaded.
	 *
	 * @return bool    True if the resource could be loaded, false otherwise.
	 */
	private function loadResourceForBlockNameHierarchy(array $blockNameHierarchy, $hierarchyLevel)
	{
		$blockName = $blockNameHierarchy[$hierarchyLevel];

		// Try to find a template for that block
		if ($this->loadResourceForBlockName($blockName)) {
			// If loadTemplateForBlock() returns true, it was able to populate the
			// cache. The only missing thing is to set the hierarchy level at which
			// the template was found.
			$this->resourceHierarchyLevels[$blockName] = $hierarchyLevel;

			return true;
		}

		if ($hierarchyLevel > 0) {
			$parentLevel     = $hierarchyLevel - 1;
			$parentBlockName = $blockNameHierarchy[$parentLevel];

			// The next two if statements contain slightly duplicated code. This is by intention
			// and tries to avoid execution of unnecessary checks in order to increase performance.

			if (isset($this->resources[$parentBlockName])) {
				// It may happen that the parent block is already loaded, but its level is not.
				// In this case, the parent block must have been loaded by loadResourceForBlock(),
				// which does not check the hierarchy of the block. Subsequently the block must have
				// been found directly on the parent level.
				if (!isset($this->resourceHierarchyLevels[$parentBlockName])) {
					$this->resourceHierarchyLevels[$parentBlockName] = $parentLevel;
				}

				// Cache the shortcuts for further accesses
				$this->resources[$blockName]               = $this->resources[$parentBlockName];
				$this->resourceHierarchyLevels[$blockName] = $this->resourceHierarchyLevels[$parentBlockName];

				return true;
			}

			if ($this->loadResourceForBlockNameHierarchy($blockNameHierarchy, $parentLevel)) {
				// Cache the shortcuts for further accesses
				$this->resources[$blockName]               = $this->resources[$parentBlockName];
				$this->resourceHierarchyLevels[$blockName] = $this->resourceHierarchyLevels[$parentBlockName];

				return true;
			}
		}

		// Cache the result for further accesses
		$this->resources[$blockName]               = false;
		$this->resourceHierarchyLevels[$blockName] = false;

		return false;
	}
}
