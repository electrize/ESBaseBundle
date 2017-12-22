<?php

namespace ES\Bundle\BaseBundle\Form\Type;

use ES\Bundle\BaseBundle\Util\Javascript;
use ES\Bundle\BaseBundle\Assets\AssetsStack;
use ES\Bundle\BaseBundle\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Templating\Asset\PackageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Select2Type extends AbstractType
{
	/**
	 * @var AssetsStack
	 */
	private $assetsStack;

	/**
	 * @var PackageInterface
	 */
	private $assetsHelper;

	/**
	 * @var TranslatorInterface
	 */
	private $translator;

	static private $assetsIncluded = false;

	public function __construct(AssetsStack $assetsStack, PackageInterface $assetsHelper, TranslatorInterface $translator)
	{
		$this->assetsStack  = $assetsStack;
		$this->assetsHelper = $assetsHelper;
		$this->translator   = $translator;

		if (!self::$assetsIncluded) {
			self::$assetsIncluded = true;
			$this->assetsStack->appendCSSInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/select2/select2.css'));
			$this->assetsStack->appendJavascriptInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/select2/select2.js'));
		}
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if ($options['multiple']) {
			$builder->addViewTransformer(new ArrayToStringTransformer());
		}
	}

	public function finishView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['js'] = Javascript::encodeJS($this->getJSOptions($options), true);
	}

	protected function getJSOptions(array $options)
	{
		$translator  = $this->translator;
		$transDomain = $options['select2_translation_domain'];

		$jsOptions = array(
			'maximumSelectionSize' => $options['maximum_selection'],
			'placeholder'          => $translator->trans($options['placeholder'], array(), $transDomain),
			'labelSearching'       => $translator->trans($options['label_searching'], array(), $transDomain),
			'labelNoMatches'       => $translator->trans($options['label_no_matches'], array(), $transDomain),
			'formatResult'         => $options['format_result'],
			'formatSelection'      => $options['format_selection'],
			'allowClear'           => !$options['required'],
			'multiple'             => $options['multiple'],
			'url'                  => $options['url'],
			'allowFreeEntries'     => $options['free_entries'],
		);

		if (!$options['url']) {
			$jsOptions['data'] = array('results' => $options['values'], 'text' => 'name');
		}

		return $jsOptions;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'choices'                    => [],
			'values'                     => function (Options $options) {
					$choices = [];
					if (is_array($options['choices'])) {
						foreach ($options['choices'] as $k => $value) {
							$choices[] = [
								'id'   => $k,
								'text' => $value,
							];
						}
					}

					return $choices;
				},
			'maximum_selection'          => null,
			'url'                        => null,
			'multiple'                   => false,
			'free_entries'               => false,
			'select2_translation_domain' => 'ESBaseBundle',
			'placeholder'                => 'form.select2.placeholder',
			'label_searching'            => 'form.select2.searching',
			'label_no_matches'           => 'form.select2.no_matches',
			'format_result'              => 'function (item){return item.text}',
			'format_selection'           => function (Options $options) {
					return $options['format_result'];
				},
		));
	}

	public function getParent()
	{
		return 'text';
	}

	public function getName()
	{
		return 'es_select2';
	}
} 