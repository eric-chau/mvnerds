<?php

namespace MVNerds\ItemHandlerBundle\Form\Model;

use MVNerds\CoreBundle\User\UserManager;

class ChangeLoLDirectoryModel 
{
	private $lolDirectory;
	
	private $userManager;
	
	private $user;
	
	public function __construct(UserManager $userManager, User $user)
	{
		$this->userManager = $userManager;
		$this->user = $user;
		
		
	}
	
	public function getLolDirectory()
	{
		return $this->lolDirectory;
	}
	
	public function setLolDirectory($lolDirectory)
	{
		$this->lolDirectory = $lolDirectory;
	}
	
	public function save()
	{
		return $this->userManager->createUser(array(
			'username'	=> $this->username,
			'password'	=> $this->password,
			'email'		=> $this->email
		));
	}
}
