<?php
namespace ES\Bundle\BaseBundle\Form\Type;

use ES\Bundle\BaseBundle\Assets\AssetsStack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GMapAutocompleteType extends AbstractType
{
	/**
	 * @var AssetsStack
	 */
	private $assetsStack;

	static private $assetsIncluded = false;

	function __construct(AssetsStack $assetsStack)
	{
		$this->assetsStack = $assetsStack;

		if (!self::$assetsIncluded) {
			self::$assetsIncluded = true;
			$this->assetsStack->appendJavascriptInclude('//maps.googleapis.com/maps/api/js?libraries=places&sensor=true');
		}
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$addHidden = function ($name) use ($builder) {
			$builder->add($name, 'hidden', array());

			return $this;
		};
		$builder->add('formatted_address', 'text', array_merge_recursive(array(
			'translation_domain' => 'ESBaseBundle',
			'attr'               => [
				'placeholder' => 'form.gmap.autocomplete.placeholder'
			],
			'label'              => false,
		), $options['text_options']));
		$addHidden('street_number');
		$addHidden('route');
		$addHidden('locality');
		$addHidden('postal_code');
		$addHidden('administrative_area_level_2');
		$addHidden('administrative_area_level_1');
		$addHidden('country');
		$addHidden('latitude');
		$addHidden('longitude');
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'text_options'  => [],
			'compound'      => true,
			'error_mapping' => array(
				'.' => 'formatted_address'
			)
		));
	}

	public function getParent()
	{
		return 'text';
	}

	public function getName()
	{
		return 'es_gmap_autocomplete';
	}
} 