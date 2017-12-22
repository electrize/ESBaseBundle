<?php

namespace ES\Bundle\BaseBundle\Form\Extension;

use ES\Bundle\BaseBundle\Assets\AssetsStack;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Templating\Asset\PackageInterface;

class DateTypeExtension extends AbstractTypeExtension
{
	/**
	 * @var AssetsStack
	 */
	private $assetsStack;

	/**
	 * @var PackageInterface
	 */
	private $assetsHelper;

	static private $assetsIncluded = false;

	function __construct(AssetsStack $assetsStack, PackageInterface $assetsHelper, Request $request)
	{
		$this->assetsStack  = $assetsStack;
		$this->assetsHelper = $assetsHelper;

		if (!self::$assetsIncluded) {
			self::$assetsIncluded = true;
			$this->assetsStack->appendCSSInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/bootstrap-datepicker/bootstrap-datepicker.css'));
			$this->assetsStack->appendJavascriptInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/bootstrap-datepicker/bootstrap-datepicker.js'));
			$locale = $request->getLocale();
			$this->assetsStack->appendJavascriptInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/bootstrap-datepicker/locales/bootstrap-datepicker.' . $locale . '.js'));
		}
	}

	public function getExtendedType()
	{
		return 'date';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'attr'   => [
				'class' => 'date-picker'
			],
			'widget' => 'single_text',
			'format' => 'dd/MM/yyyy',
		));
	}

	public function finishView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['format'] = strtolower($options['format']);
	}
}
