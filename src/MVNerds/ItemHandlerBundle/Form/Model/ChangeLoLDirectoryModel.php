<?php

namespace MVNerds\ItemHandlerBundle\Form\Model;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Preference\PreferenceManager;

class ChangeLoLDirectoryModel 
{
	private $lolDirectory;
	
	private $preferenceManager;
	
	private $user;
	
	public function __construct(PreferenceManager $preferenceManager, User $user)
	{
		$this->preferenceManager = $preferenceManager;
		$this->user = $user;
		
		// On effectue un try catch car nous ne sommes pas sûr que l'utilisateur ait changé le dossier d'emplacement de son League of legends ou non
		try {
			$userPreference = $this->preferenceManager->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $this->user->getId());
			$this->lolDirectory = $userPreference->getValue();
		}
		catch(InvalidArgumentException $e) {
			
		}
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
