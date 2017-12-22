<?php

namespace ES\Bundle\BaseBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\Executor\MongoDBExecutor;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use ES\Bundle\BaseBundle\Tests\Utils;
use ES\Bundle\UserBundle\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseTest extends WebTestCase
{
	/**
	 * @var array
	 */
	protected $fixtureGroups = array();

	/**
	 * @return EntityManager
	 */
	static protected function getEntityManager()
	{
		return static::getContainer()->get('doctrine')->getManager();
	}

	static protected function getContainer()
	{
		if (!static::$kernel) {
			static::bootKernel();
		}

		return static::$kernel->getContainer();
	}

	/**
	 * @param string $userClass
	 * @param string $username
	 * @return User
	 */
	protected function getUser($userClass, $username)
	{
		return $this->getData($userClass, array('username' => $username));
	}

	protected function refresh(&$entity)
	{
		$em = self::getEntityManager();
		$em->detach($entity);
		$entity = $em->find(get_class($entity), $entity->getId());
	}

	protected function getData($dataClass, array $criteria)
	{
		$repo   = self::getEntityManager()->getRepository($dataClass);
		$entity = $repo->findOneBy($criteria);

		$this->assertNotNull($entity, implode(',', $criteria));
		$this->assertEquals($repo->getClassName(), get_class($entity));

		return $entity;
	}

	protected function setStagingAccess(Client $client)
	{
		list($name, $value) = Utils::getStagingCookie();
		$cookie = new Cookie($name, $value);
		$client->getCookieJar()->set($cookie);
	}

	protected function logIn(UserInterface $user, Client $client = null, array $roles = array('ROLE_USER'), $firewall = 'main')
	{
		$token = new UsernamePasswordToken($user->getUsername(), null, $firewall, $roles);
		$token->setUser($user);

		if ($client) {
			if (!$client->getContainer()) {
				$client->getKernel()->boot();
			}

			$session = $client->getContainer()->get('session');
			$session->set('_security_' . $firewall, serialize($token));
			$session->save();
			$cookie = new Cookie($session->getName(), $session->getId());
			$client->getCookieJar()->set($cookie);
		} else {
			/** @var SecurityContextInterface $securityContext */
			$securityContext = self::getContainer()->get('security.context');
			$securityContext->setToken($token);
		}
	}

	protected function setUp()
	{
		parent::setUp();

		if (count($this->fixtureGroups) === 0) {
			return;
		}

		$this->loadFixtures($this->fixtureGroups);
	}

	private function loadFixtures(array $groups)
	{
		$container = static::getContainer();
		if ($container->has('doctrine')) {
			$em     = $container->get('doctrine')->getManager();
			$purger = new ORMPurger($em);
			$em->getConnection()->executeQuery('SET foreign_key_checks = 0');
			$purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
			$executor = new ORMExecutor($em, $purger);
			$executor->execute(array());
		}
		if ($container->has('doctrine_mongodb')) {
			$dm       = $container->get('doctrine_mongodb')->getManager();
			$purger   = new MongoDBPurger($dm);
			$executor = new MongoDBExecutor($dm, $purger);
			$executor->execute(array());
		}

		$container->get('es_fixtures.loader.yaml')->load($this->fixtureGroups);
	}

	protected function tearDown()
	{
		parent::tearDown();

		static::$kernel = null;

		if (count($this->fixtureGroups) === 0) {
			return;
		}
		// Necessary?
		//$this->loadFixtures(array('default'));
	}
}
