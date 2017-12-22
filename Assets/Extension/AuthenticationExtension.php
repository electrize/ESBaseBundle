<?php
namespace ES\Bundle\BaseBundle\Assets\Extension;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class AuthenticationExtension extends AssetsExtension
{
	/**
	 * @var RouterInterface
	 */
	private $router;

	private $securityContext;

	private $loginRoute;

	function __construct(RouterInterface $router, SecurityContextInterface $securityContext, $loginRoute = null)
	{
		$this->router          = $router;
		$this->securityContext = $securityContext;
		$this->loginRoute      = $loginRoute;
	}

	function getJavascriptCode()
	{
		$config = [
			'loggedIn' => $this->isAuthenticated(),
		];

		if (null !== $this->loginRoute) {
			$config['loginUrl'] = $this->router->generate($this->loginRoute);
		}

		return [
			sprintf('<script>Cameleon.request.setConfig(%s);</script>', json_encode($config)),
		];
	}

	private function isAuthenticated()
	{
		return $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
	}
}