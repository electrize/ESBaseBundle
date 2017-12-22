<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormTypeExtension extends AbstractTypeExtension
{
	/**
	 * Returns the name of the type being extended.
	 *
	 * @return string The name of the type being extended
	 */
	public function getExtendedType()
	{
		return 'form';
	}

	/**
	 * Add the file_path option
	 *
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
		//	'is_prototype'       => null,
			'csrf_message'       => 'form.csrf_token.invalid',
		));
	}

//	public function finishView(FormView $view, FormInterface $form, array $options)
//	{
//		$isPrototype = false;
//		if (null !== $options['is_prototype']) {
//			$isPrototype                = $options['is_prototype'];
//			$view->vars['is_prototype'] = $isPrototype;
//		} else {
//			$parent = $form;
//			while ($parent = $parent->getParent()) {
//				if (null !== $_isPrototype = $parent->getConfig()->getOption('is_prototype')) {
//					$isPrototype = $_isPrototype;
//					break;
//				}
//			}
//		}
//		$view->vars['is_prototype'] = $isPrototype;
//	}
} 