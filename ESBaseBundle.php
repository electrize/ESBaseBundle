<?php

namespace ES\Bundle\BaseBundle;

use ES\Bundle\BaseBundle\DependencyInjection\Compiler\AssetsExtensionCompilerPass;
use ES\Bundle\BaseBundle\DependencyInjection\Compiler\CameleonGlobalsCompilerPass;
use ES\Bundle\BaseBundle\DependencyInjection\MappingRegistration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ESBaseBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		MappingRegistration::addRegisterMappingsPass($container, __DIR__, __NAMESPACE__);
		$container->addCompilerPass(new CameleonGlobalsCompilerPass());
		$container->addCompilerPass(new AssetsExtensionCompilerPass());
	}
}
