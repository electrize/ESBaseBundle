<?php


namespace ES\Bundle\BaseBundle\Assets;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AssetsStack extends ContainerAware
{
	private $javascriptIncludes = [];

	private $javascriptCode = [];

	private $cssIncludes = [];

	/**
	 * @var AssetsExtensionInterface[]
	 */
	private $extensions = [];

	function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	public function addExtension($extensions)
	{
		$this->extensions[] = $extensions;
	}

	public function appendJavascriptInclude($src, $key = null)
	{
		if (null === $key) {
			$key = md5($src);
		}

		if (isset($this->javascriptIncludes[$key])) {
			throw new \RuntimeException(sprintf('Javascript source with key "%s" has already be included.'));
		}

		$this->javascriptIncludes[$key] = $src;
	}

	public function appendCSSInclude($src, $key = null)
	{
		if (null === $key) {
			$key = md5($src);
		}

		if (isset($this->cssIncludes[$key])) {
			throw new \RuntimeException(sprintf('CSS source with key "%s" has already be included.', $key));
		}

		$this->cssIncludes[$key] = $src;
	}

	public function appendJavascriptCode($code)
	{
		if ($this->container->isScopeActive('request') && $this->container->get('request')->isXmlHttpRequest()) {
			return $code;
		}

		$key = md5($code);
		if (isset($this->javascriptCode[$key])) {
			throw new \RuntimeException(sprintf('Javascript code has already be included.'));
		}

		$this->javascriptCode[$key] = $code;
	}

	public function getCSSIncludes()
	{
		foreach ($this->extensions as $extension) {
			foreach ($extension->getCSSIncludes() as $src) {
				$this->appendCSSInclude($src);
			}
		}

		return $this->cssIncludes;
	}

	public function getJavascriptIncludes()
	{
		foreach ($this->extensions as $extension) {
			foreach ($extension->getJavascriptIncludes() as $src) {
				$this->appendJavascriptInclude($src);
			}
		}

		return $this->javascriptIncludes;
	}

	public function getJavascriptCode()
	{
		foreach ($this->extensions as $extension) {
			foreach ($extension->getJavascriptCode() as $code) {
				$this->appendJavascriptCode($code);
			}
		}

		return preg_replace('#</script>\s*<script>#ius', '', implode("\n", $this->javascriptCode));
	}
} 