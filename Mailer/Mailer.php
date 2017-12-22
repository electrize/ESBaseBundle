<?php


namespace ES\Bundle\BaseBundle\Mailer;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Templating\EngineInterface;

class Mailer implements MailerInterface
{
	/**
	 * @var \Swift_Mailer
	 */
	protected $mailer;

	/**
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * @var SecurityContextInterface
	 */
	private $securityContext;

	/**
	 * @var string|array
	 */
	private $fromEmail;

	function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, SecurityContextInterface $securityContext, $fromEmail)
	{
		$this->mailer          = $mailer;
		$this->twig            = $twig;
		$this->securityContext = $securityContext;
		$this->fromEmail       = $fromEmail;
	}

	public function send($templateName, $toEmail, array $params = array(), $fromEmail = null, array $attachments = array())
	{
		if (null === $fromEmail) {
			$fromEmail = $this->fromEmail;
		}

		$this->sendMessage($templateName, $params, $fromEmail, $toEmail, $attachments);
	}

	public function sendToUser($templateName, array $params = array(), UserInterface $user = null, $fromEmail = null, array $attachments = array())
	{
		if (null === $user) {
			$user = $this->getUser();
			if (!$user instanceof UserInterface) {
				throw new AccessDeniedHttpException('User is not defined for mail');
			}
		}

		$this->send($templateName, $user->getEmail(), array_merge(array(
			'user' => $user,
		), $params), $fromEmail, $attachments);
	}

	protected function getUser()
	{
		if (null === $token = $this->securityContext->getToken()) {
			return null;
		}

		if (!is_object($user = $token->getUser())) {
			return null;
		}

		return $user;
	}

	/**
	 * @param string $templateName
	 * @param array  $context
	 * @param string $fromEmail
	 * @param string $toEmail
	 * @param array  $attachments An array of paths eg: ['/var/www/uploads/a.jpg', '/var/www/static/demo.pdf']
	 */
	protected function sendMessage($templateName, $context, $fromEmail, $toEmail, array $attachments = array())
	{
		$context  = $this->twig->mergeGlobals($context);
		$template = $this->twig->loadTemplate($templateName);
		$subject  = $template->renderBlock('subject', $context);
		$textBody = $template->renderBlock('body_text', $context);
		$htmlBody = $template->renderBlock('body_html', $context);

		$message = \Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom($fromEmail)
			->setTo($toEmail);

		if ($htmlBody) {
			$message
				->setBody($htmlBody, 'text/html')
				->addPart($textBody, 'text/plain');
		} else {
			$message->setBody($textBody);
		}

		foreach ($attachments as $src) {
			$message->attach(\Swift_Attachment::fromPath($src));
		}

		$this->mailer->send($message);
	}
}