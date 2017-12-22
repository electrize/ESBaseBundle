<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class ContactMessage
{
	/**
	 * @var mixed
	 */
	protected $id;

	/**
	 * The sender email
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * The message body
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Optional if user is logged in
	 *
	 * @var UserInterface
	 */
	protected $user;

	/**
	 * @var \DateTime
	 */
	protected $createdAt;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @param string $email
	 * @return $this
	 */
	public function setEmail($email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param UserInterface $user
	 * @return $this
	 */
	public function setUser(UserInterface $user = null)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * @return UserInterface
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function setMessage($message)
	{
		$this->message = $message;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	function __toString()
	{
		return $this->email ? $this->email : 'Message';
	}
}