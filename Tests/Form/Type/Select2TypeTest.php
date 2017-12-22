<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\Type;

use ES\Bundle\BaseBundle\Form\Type\Select2Type;
use Symfony\Component\Form\Test\TypeTestCase;

class Select2TypeTest extends TypeTestCase
{
	public function testSelect2Basic()
	{
		$form = $this->factory->create($this->createSelect2Type(), null, array());

		$form->setData('foo@bar.com');
		$form->submit('foo@bar.com,bar@bar.com');

		$view = $form->createView();
		$this->assertEquals('foo@bar.com,bar@bar.com', $view->vars['value']);
		$this->assertEquals('foo@bar.com,bar@bar.com', $form->getData());
	}

	public function testSelect2Multiple()
	{
		$form = $this->factory->create($this->createSelect2Type(), null, array(
			'multiple' =>  true,
		));

		$form->setData(array('foo@bar.com'));
		$form->submit('foo@bar.com,bar@bar.com');
		$view = $form->createView();
		$this->assertEquals('foo@bar.com,bar@bar.com', $view->vars['value']);

		$this->assertEquals(array('foo@bar.com', 'bar@bar.com'), $form->getData());
	}

	private function createSelect2Type()
	{
		$container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$assetStack   = $this->getMock('ES\Bundle\BaseBundle\Assets\AssetsStack', null, array($container));
		$assetsHelper = $this->getMock('Symfony\Component\Templating\Asset\PackageInterface');

		$translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
		$select2Type  = new Select2Type($assetStack, $assetsHelper, $translator);

		return $select2Type;
	}
}
