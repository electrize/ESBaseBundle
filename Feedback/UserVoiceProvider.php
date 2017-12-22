<?php


namespace ES\Bundle\BaseBundle\Feedback;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserVoiceProvider implements FeedbackProviderInterface
{
	/**
	 * @var \Twig_Environment
	 */
	private $templating;

	/**
	 * @var array
	 */
	private $options;

	function __construct(\Twig_Environment $templating, array $options = array())
	{
		$this->templating = $templating;

		$resolver = new OptionsResolver();
		$this->configureOptions($resolver);
		$this->options = $resolver->resolve($options);
	}

	protected function configureOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(array('key'));
		$resolver->setDefaults(array(
			'template_name'            => 'ESBaseBundle:Feedback:uservoice.html.twig',
			'accent_color'             => '#CCCCCC',
			'trigger_color'            => 'white',
			'trigger_background_color' => '#CCCCCC',
			'mode'                     => 'contact',
			'style'                    => 'tab',
			'position'                 => 'left',
		));
	}

	public function render(array $options = array())
	{
		$options = array_merge($this->options, $options);

		$templateName = $options['template_name'];
		unset($options['template_name']);

		return $this->templating->render($templateName, $options);
	}
}