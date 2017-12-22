<?php


namespace ES\Bundle\BaseBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
use ES\Bundle\BaseBundle\DependencyInjection\Compiler\RegisterMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MappingRegistration
{
	static public function addRegisterMappingsPass(ContainerBuilder $container, $bundleDir, $bundleNamespace)
	{
		// the base class is only available since symfony 2.3
		$symfonyVersion = class_exists('Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass');

		$mappings = array(
			realpath($bundleDir . '/Resources/config/doctrine/model') => $bundleNamespace . '\Model',
		);

		if ($symfonyVersion && class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
			$container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver($mappings, array('es_base.model_manager_name'), 'es_base.backend_type_orm'));
		} else {
			$container->addCompilerPass(RegisterMappingsPass::createOrmMappingDriver($mappings));
		}

		if ($symfonyVersion && class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
			$container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver($mappings, array('es_base.model_manager_name'), 'es_base.backend_type_mongodb'));
		} else {
			$container->addCompilerPass(RegisterMappingsPass::createMongoDBMappingDriver($mappings));
		}

		if ($symfonyVersion && class_exists('Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass')) {
			$container->addCompilerPass(DoctrineCouchDBMappingsPass::createYamlMappingDriver($mappings, array('es_base.model_manager_name'), 'es_base.backend_type_couchdb'));
		} else {
			$container->addCompilerPass(RegisterMappingsPass::createCouchDBMappingDriver($mappings));
		}
	}
} 