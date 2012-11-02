<?php

namespace MVNerds\LaunchSiteBundle\Form\Model;

use MVNerds\CoreBundle\User\UserManager;

class ResetPasswordModel 
{
	private $user = null;
	private $password;
	private $passwordConfirm;
	
	private $userManager;
	
	public function __construct(UserManager $userManager, $userSlug = null)
	{
		$this->userManager = $userManager;
		if (null != $userSlug) {
			$this->user = $this->userManager->findBySlug($userSlug);
		}
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function setPassword($password)
	{
		$this->password = $password;
	}
	
	public function getPasswordConfirm()
	{
		return $this->passwordConfirm;
	}
	
	public function setPasswordConfirm($passwordConfirm)
	{
		$this->passwordConfirm = $passwordConfirm;
	}
	
	public function save()
	{
		if ($this->user != null) {
			return $this->userManager->changeUserPassword($this->user, $this->password);
		}
		
		return null;
	}
	
	public function isPasswordMatching()
	{
		return $this->password == $this->passwordConfirm;
	}
}
