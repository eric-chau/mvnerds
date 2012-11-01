<?php

namespace MVNerds\LaunchSiteBundle\Form\Model;

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
		$this->user = $this->userManager->findOneByEmail($this->email);
		
		return $this->user->isEnabled();
	}
}
