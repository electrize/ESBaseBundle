<?php
namespace ES\Bundle\BaseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class CameleonGlobalsCompilerPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		$twigExtension = 'es_base.twig.extension.base';
		if (!$container->hasDefinition($twigExtension)) {
			return;
		}

		$definition = $container->getDefinition($twigExtension);

		$taggedServices = $container->findTaggedServiceIds(
			'es_base.cameleon_globals'
		);
		foreach ($taggedServices as $id => $service) {
			foreach ($service as $attributes) {
				$definition->addMethodCall(
					'setGlobal',
					array($attributes['key'], $attributes['value'])
				);
			}
		}
	}
}