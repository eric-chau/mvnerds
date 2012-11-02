<?php

namespace MVNerds\LaunchSiteBundle\Form\Model;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MVNerds\CoreBundle\User\UserManager;

class ForgotPasswordModel 
{
	private $email;
	private $user;
	private $userManager;
	
	public function __construct(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function setEmail($email)
	{
		$this->email = $email;
	}
	
	public function save()
	{
		$this->userManager->initForgotPasswordProcess($this->user);
	}
	
	public function isEmailAlreadyInUse()
	{
		return $this->userManager->isEmailAvailable($this->email);
	}
	
	public function isUserAccountActivated()
	{
		try {
			$this->user = $this->userManager->findOneByEmail($this->email);
		}
		catch (InvalidArgumentException $e)	{
			// On retourne true car sinon le message dit que le compte utilisateur n'est pas activé alors qu'en réalité il n'existe pas
			return true;
		}
		
		return $this->user->isEnabled();
	}
}
