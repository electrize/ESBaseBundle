<?php

namespace ES\Bundle\BaseBundle\Controller;

use Doctrine\Common\Util\Debug;
use ES\Bundle\BaseBundle\Security\Listener\StagingListener;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;

class StagingSecurityController extends ContainerAware
{
	public function loginAction(Request $request)
	{
		if (!$this->container->hasParameter('es_base.security.staging.ask_username')) {
			throw new NotFoundHttpException('Staging is not enabled');
		}

		if (($token = $this->container->get('es_base.security.context')->getToken())
			&& $token->getUsername() === '_cameleon_staging_user'
		) {
			return new RedirectResponse($request->getUriForPath('/'));
		}

		/** @var $session \Symfony\Component\HttpFoundation\Session\Session */
		$session = $request->getSession();

		// get the error if any (works with forward and redirect -- see below)
		if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
		} elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
			$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
			$session->remove(SecurityContext::AUTHENTICATION_ERROR);
		} else {
			$error = '';
		}

		if ($error) {
			// TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
			$error = $error->getMessage();
		}
		// last username entered by the user
		$lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

		$csrfToken = $this->container->has('form.csrf_provider')
			? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
			: null;

		return $this->renderLogin(array(
			'ask_username'     => $this->container->getParameter('es_base.security.staging.ask_username'),
			'invite'           => $this->container->getParameter('es_base.security.staging.invite'),
			'default_username' => StagingListener::DEFAULT_USERNAME,
			'last_username'    => $lastUsername,
			'error'            => $error,
			'csrf_token'       => $csrfToken,
		));
	}

	/**
	 * Renders the login template with the given parameters. Overwrite this function in
	 * an extended controller to provide additional data for the login template.
	 *
	 * @param array $data
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderLogin(array $data)
	{
		return $this->container->get('templating')->renderResponse('ESBaseBundle:Staging:login.html.twig', $data);
	}

	public function checkAction()
	{
		throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
	}

	public function logoutAction()
	{
		throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
	}
}
