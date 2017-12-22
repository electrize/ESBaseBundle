<?php


namespace ES\Bundle\BaseBundle\Mailer;

use Symfony\Component\Security\Core\User\UserInterface;

interface MailerInterface
{
	/**
	 * @param string       $templateName
	 * @param string|array $toEmail
	 * @param array        $params
	 * @param string|array $fromEmail
	 * @param array        $attachments An array of paths eg: ['/var/www/uploads/a.jpg', '/var/www/static/demo.pdf']
	 */
	public function send($templateName, $toEmail, array $params = array(), $fromEmail = null, array $attachments = array());

	/**
	 * @param string        $templateName
	 * @param array         $params
	 * @param UserInterface $user
	 * @param array|string  $fromEmail
	 * @param array         $attachments An array of paths eg: ['/var/www/uploads/a.jpg', '/var/www/static/demo.pdf']
	 */
	public function sendToUser($templateName, array $params = array(), UserInterface $user = null, $fromEmail = null, array $attachments = array());
} 