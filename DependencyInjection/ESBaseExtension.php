<?php

namespace ES\Bundle\BaseBundle\DependencyInjection;

use Braincrafted\Bundle\BootstrapBundle\DependencyInjection\BraincraftedBootstrapExtension;
use ES\Bundle\BaseBundle\Security\Listener\StagingListener;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ESBaseExtension extends Extension implements PrependExtensionInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config        = $this->processConfiguration($configuration, $configs);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');
		$loader->load('form.yml');
		$loader->load('theme_renderer.yml');

		$container->setParameter('es_base.db_driver', $config['db_driver']);
		$container->setAlias('es_base.object_manager', $this->getDefaultObjectManagerService($config['db_driver']));

		foreach ($configuration::$globals as $key) {
			$container->setParameter('es_base.' . $key, $config[$key]);
		}

		$objectRegistry = array(
			'orm'     => 'doctrine',
			'mongodb' => 'doctrine_mongodb',
		);
		$container->setAlias('es_base.manager_registry', $objectRegistry[$config['db_driver']]);

		if (isset($config['google_analytics'])) {
			$this->loadGoogleAnalytics($loader, $container, $config);
		}
		if (isset($config['mailer'])) {
			$container->setParameter('es_base.mailer.sender_address', $config['mailer']['sender_address']);
			$container->setParameter('es_base.mailer.sender_name', $config['mailer']['sender_name']);
			$loader->load('mailer.yml');
		}
		if (isset($config['contact'])) {
			$this->loadContact($config['contact'], $container, $loader);
		}
		if (isset($config['feedback'])) {
			$this->loadFeedback($config['feedback'], $container, $loader);
		}

		$twigBaseExtension = $container->getDefinition('es_base.twig.extension.base');
		$twigBaseExtension->addMethodCall('setGlobal', array(
			'google_analytics_enabled',
			isset($config['google_analytics']),
		));

		$config['host_env'] = strpos($config['host_env'], '%') === 0 ?
			$container->getParameter(substr($config['host_env'], 1, -1)) : $config['host_env'];

		if (in_array($config['host_env'], $config['staging']['secured_environments'])) {
			$this->loadStaging($loader, $container, $config['staging']);
		}

		$container->setParameter('es_base.templating.bootstrap.use_cdn', $config['templating']['bootstrap']['use_cdn']);
		$container->setParameter('es_base.object_mapping', $config['object_mapping']);

		$bundles = $container->getParameter('kernel.bundles');
		if (isset($bundles['FOSUserBundle'])) {
			$definition = $container->getDefinition('es_base.assets.extension.authentication');
			$definition->addArgument($config['auth']['login_route']);
		}
	}

	private function configureTwigBundle(ContainerBuilder $container, array $config)
	{
		$bundles = $container->getParameter('kernel.bundles');
		$formTemplates = array();
		if (isset($bundles['BraincraftedBootstrapBundle'])) {
			$formTemplates[] = 'BraincraftedBootstrapBundle:Form:bootstrap.html.twig';
		}
		if (isset($bundles['ESFileUploadBundle'])) {
			$formTemplates[] = 'ESFileUploadBundle:Form:types.html.twig';
		}
		$formTemplates[] = $config['templating']['form'];

		foreach (array_keys($container->getExtensions()) as $name) {
			switch ($name) {
				case 'twig':
					$container->prependExtensionConfig(
						$name,
						array('form' => array('resources' => $formTemplates))
					);
					break;
			}
		}
	}

	private function getDefaultObjectManagerService($dbDriver)
	{
		switch ($dbDriver) {
			case 'orm':
				return 'doctrine.orm.entity_manager';
				break;
			case 'mongodb':
				return 'doctrine.odm.mongodb.document_manager';
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Undefined DB driver "%s"', $dbDriver));
		}
	}

	private function loadGoogleAnalytics($loader, ContainerBuilder $container, array $config)
	{
		$googleAnalytics = $config['google_analytics'];
		if (!$googleAnalytics['website_name']) {
			throw new InvalidConfigurationException('You must configure the website_name at ' . $this->getAlias() . '.google_analytics');
		}

		if (count($googleAnalytics['trackers']) === 0) {
			throw new InvalidConfigurationException('You must configure at least one tracker at ' . $this->getAlias() . '.google_analytics.trackers');
		}

		$loader->load('google_analytics.yml');
		$container->setParameter('es_base.google_analytics.website_name', $googleAnalytics['website_name']);
		$container->setParameter('es_base.google_analytics.trackers', $googleAnalytics['trackers']);
		$container->setParameter('es_base.google_analytics.tracked_environments', $googleAnalytics['tracked_environments']);
	}

	private function loadStaging($loader, ContainerBuilder $container, array $config)
	{
		$askUsername = true;
		if ($config['password']) {
			$askUsername = false;
			if (count($config['users']) > 0) {
				throw new InvalidConfigurationException('You must not set "password" and "users" at the same time in "es_base.staging"');
			}
			if ($config['authtype'] === 'basic') {
				throw new InvalidConfigurationException('Basic authentication does not support default username. You must provide the "users" node instead of the "password" one.');
			}
			$config['users'] = array(
				StagingListener::DEFAULT_USERNAME => array(
					'password' => $config['password'],
					'roles'    => array('ROLE_STAGING')
				)
			);
		} elseif (count($config['users']) === 0) {
			throw new InvalidConfigurationException('You must either set "password" or define "users" in "es_base.staging"');
		}

		$authType = $config['authtype'];
		$loader->load('staging.yml');
		$loader->load(sprintf('auth/%s.yml', $authType));

		$exceptionListener = $container->getDefinition('es_base.security.exception_listener');
		$exceptionListener->replaceArgument(4, new Reference('es_base.security.entry_point.' . $authType));

		$this->renameParameters($container, 'es_base.security.staging', $config);
		$container->setParameter('es_base.security.staging.ask_username', $askUsername);
	}

	private function loadContact(array $config, ContainerBuilder $container, $loader)
	{
		$container->setParameter('es_base.contact.deliver_to', $config['deliver_to']);
		$model      = $config['model'];
		$templating = $config['templating'];
		$container->setParameter('es_base.contact.templating.mail', $templating['mail']);
		$container->setParameter('es_base.contact.templating.form', $templating['form']);
		$container->setParameter('es_base.model.contact_message.class', $model['contact_message_class']);
		$container->setParameter('es_base.model.contact_message.table', $model['contact_message_table']);
		$loader->load('contact.yml');
	}

	private function loadFeedback(array $config, ContainerBuilder $container, $loader)
	{
		$container->setParameter('es_base.feedback.options', $config['options']);

		$loader->load('feedback.yml');

		$container->setAlias('es_base.feedback.provider', 'es_base.feedback.' . $config['provider'] . '.provider');
		$provider = $container->getDefinition('es_base.feedback.' . $config['provider'] . '.provider');
		$provider->setAbstract(false);
	}

	private function renameParameters(ContainerBuilder $container, $prefix, array $config)
	{
		foreach ($config as $key => $value) {
			$container->setParameter($prefix . '.' . $key, $value);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepend(ContainerBuilder $container)
	{
		$bundles = $container->getParameter('kernel.bundles');

		$configs = $container->getExtensionConfig($this->getAlias());
		$config  = $this->processConfiguration(new Configuration(), $configs);

		if (isset($bundles['TwigBundle'])) {
			$this->configureTwigBundle($container, $config);
		}
	}
}
