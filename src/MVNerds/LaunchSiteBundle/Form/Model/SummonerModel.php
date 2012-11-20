<?php

namespace MVNerds\LaunchSiteBundle\Form\Model;

use MVNerds\CoreBundle\Model\UserPeer;
use MVNerds\CoreBundle\User\UserManager;
class SummonerModel 
{
	private $username;
	private $password;
	private $passwordConfirm;
	private $email;
	
	private $userManager;
	
	public function __construct(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function setUsername($username)
	{
		$this->username = $username;
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
		return $this->userManager->createUser(array(
			'username'	=> $this->username,
			'password'	=> $this->password,
			'email'		=> $this->email
		));
	}
	
	public function isPasswordMatching()
	{
		return $this->password == $this->passwordConfirm;
	}
	
	public function isEmailAlreadyInUse()
	{
		return $this->userManager->isEmailAvailable($this->email);
	}
	
	public function isUsernameAlreadyInUse()
	{
		return UserPeer::isUsernameAlreadyInUse($this->username, null);
	}
}
