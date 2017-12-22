<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Security\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

class StagingListener
{
	const DEFAULT_USERNAME = '_cameleon_staging_user';
	private $context;
	private $authManager;
	protected $httpUtils;
	private $options;
	private $rememberMeServices;

	public function __construct(SecurityContextInterface $context, AuthenticationManagerInterface $authManager, HttpUtils $httpUtils, RememberMeServicesInterface $rememberMeServices, array $options = array())
	{
		$this->context            = $context;
		$this->authManager        = $authManager;
		$this->httpUtils          = $httpUtils;
		$this->rememberMeServices = $rememberMeServices;
		$this->options            = array_merge(array(
			'check_path' => '/staging/login_check',
			'login_path' => '/staging/login',
		), $options);
	}

	public function handle(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		if ($this->httpUtils->checkRequestPath($request, $this->options['login_path'])) {
			return;
		}
		if (preg_match('#/(_wdt|_profiler)/#', $request->getRequestUri())) {
			return;
		}

		if (isset($this->options['allowed_path'])) {
			foreach ($this->options['allowed_path'] as $allowedPath) {
				if (preg_match('#' . $allowedPath . '#', $request->getPathInfo())) {
					return;
				}
			}
		}
		if (isset($this->options['allowed_user_agent'])) {
			foreach ($this->options['allowed_user_agent'] as $allowedUserAgent) {
				if (preg_match('#' . $allowedUserAgent . '#', $request->headers->get('user-agent'))) {
					return;
				}
			}
		}

		if (null === $token = $this->context->getToken()) {
			throw new AuthenticationCredentialsNotFoundException('A Token was not found in the SecurityContext.');
		}

		if (!$token->isAuthenticated()) {
			$token = $this->authManager->authenticate($token);
			$this->context->setToken($token);

			return;
		}

		if (!$token->isAuthenticated()) {
			throw new AccessDeniedException();
		}
	}
}