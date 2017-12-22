<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Form\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollectionTypeExtension extends AbstractTypeExtension
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if ($options['allow_add'] && $options['prototype']) {
			$prototype = $builder->create($options['prototype_name'], $options['type'], array_replace(array(
				'label'        => ' ',
			), $options['options']));
			$builder->setAttribute('prototype', $prototype->getForm());
		}
	}

	/**
	 * Returns the name of the type being extended.
	 *
	 * @return string The name of the type being extended
	 */
	public function getExtendedType()
	{
		return 'collection';
	}

	/**
	 * Add the file_path option
	 *
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'allow_add'       => true,
			'allow_delete'    => true,
			'add_label'       => 'form.collection.add',
			'delete_label'    => 'form.collection.delete',
		));
	}

	public function finishView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['add_label'] = $options['add_label'];
		$view->vars['delete_label'] = $options['delete_label'];
	}
} 