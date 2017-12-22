<?php

namespace ES\Bundle\BaseBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FeedbackExtension extends \Twig_Extension
{
	private $container;

	function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('render_feedback', array(
				$this,
				'renderFeedback'
			), array('is_safe' => array('html'))),
		);
	}

	public function renderFeedback(array $options = array())
	{
		return $this->container->get('es_base.feedback.provider')->render($options);
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'es_feedback';
	}
}