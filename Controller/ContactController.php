<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller
{
	public function formAction(Request $request)
	{
		$user = $this->getUser();
		$form = $this->createForm('es_contact_form', null, array(
			'default_email' => $user ? $user->getEmail() : null,
		));

		if ('POST' === $request->getMethod()) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				/** @var \ES\Bundle\BaseBundle\Model\ContactMessage $message */
				$message = $form->getData();

				if ($user) {
					$message->setUser($user);
				}

				$em = $this->getDoctrine()->getManager();
				$em->persist($message);
				$em->flush();

				$translator = $this->get('translator');
				$this->get('session')->getFlashBag()->add('success', $translator->trans('contact.flashes.message.success', array(), 'ESBaseBundle'));

				if ($email = $this->container->getParameter('es_base.contact.deliver_to')) {
					$this->container->get('es_base.mailer')->send(
						$this->container->getParameter('es_base.contact.templating.mail'),
						$email,
						array('message' => $message),
						$message->getEmail());
				}

				return $this->redirect($request->getRequestUri());
			}
		}

		return $this->render($this->container->getParameter('es_base.contact.templating.form'), array(
			'contact_form' => $form->createView(),
		));
	}
} 