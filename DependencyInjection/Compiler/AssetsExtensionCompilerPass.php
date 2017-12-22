<?php
namespace ES\Bundle\BaseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AssetsExtensionCompilerPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		$assetsStack = 'es_base.templating.assets.stack';
		if (!$container->hasDefinition($assetsStack)) {
			return;
		}

		$definition = $container->getDefinition($assetsStack);

		$taggedServices = $container->findTaggedServiceIds('es_base.assets_extension');
		foreach ($taggedServices as $id => $service) {
			$definition->addMethodCall(
				'addExtension',
				array(new Reference($id))
			);
		}
	}
}