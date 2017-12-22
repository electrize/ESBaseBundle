<?php

namespace ES\Bundle\BaseBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;

class GoogleAnalyticsExtension extends \Twig_Extension
{
	/**
	 * @var string
	 */
	protected $webSiteName;

	/**
	 * @var array
	 */
	protected $trackers = array();

	protected $hostEnv;

	protected $trackedEnvironments;

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	function __construct(ContainerInterface $container, $webSiteName, array $trackers = array(), $hostEnv, array $trackedEnvironments = array('prod'))
	{
		$this->container           = $container;
		$this->webSiteName         = $webSiteName;
		$this->trackers            = $trackers;
		$this->hostEnv             = $hostEnv;
		$this->trackedEnvironments = $trackedEnvironments;
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('google_analytics_code', array(
				$this,
				'getGoogleAnalyticsCode'
			), array('is_safe' => array('html'))),
		);
	}

	public function getGoogleAnalyticsCode($tracker = null)
	{
		if (null === $tracker) {
			reset($this->trackers);
			$tracker = key($this->trackers);
		}

		if (!isset($this->trackers[$tracker])) {
			throw new \InvalidArgumentException(sprintf('Google Analytics tracker "%s" not found. Availables are "%s"',
				$tracker,
				implode('", "', array_keys($this->trackers))
			));
		}
		return $this->container->get('templating')->render('ESBaseBundle:GoogleAnalytics:code.html.twig', array(
			'website_name' => $this->webSiteName,
			'tracker'      => $this->trackers[$tracker],
			'send'         => in_array($this->hostEnv, $this->trackedEnvironments)
		));
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'es_google_analytics';
	}
}