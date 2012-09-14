<?php

namespace MVNerds\CoreBundle\Flash;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Classe qui permet de gérer les messages flash de l'application de manière uniforme 
 */
class FlashManager
{
    /**
	 * Variable contenant la session courante ce qui permet de setter ou de getter les messages
	 * flash depuis les controllers et les templates Twig
	 * 
	 * @var Symfony\Component\HttpFoundation\Session\Session la session courante 
	 */
	private $session;
	
	/**
	 * Constante accessible depuis l'extérieur pour centraliser l'appelation des différents types
	 * de message flash 
	 */
	const SUCCESS = 'success';
	const ERROR = 'error';
	const WARNING = 'warning';
	const INFO = 'info';
	
	
	/**
	 * Permet de mettre en session un message flash de succès
	 * 
	 * @param string $msg le message que l'on veut stocker en tant que flash de succès
	 */
	public function setSuccessMessage($msg)
	{
		$this->session->setFlash(self::SUCCESS, $msg);
	}
	
	/**
	 * Permet de récupérer un message de succès s'il y en a un en session
	 * 
	 * @return string renvoi le message flash de succès s'il y en a un, sinon null 
	 */
	public function getSuccessMessage()
	{
		if ($this->session->hasFlash(self::SUCCESS))
		{
			return $this->session->getFlash(self::SUCCESS);
		}
		
		return null;
	}
	
	
	/**
	 * Permet de mettre en session un message flash d'erreur
	 * 
	 * @param string $msg le message que l'on veut stocker en tant que flash d'erreur
	 */
	public function setErrorMessage($msg)
	{
		$this->session->setFlash(self::ERROR, $msg);
	}
	
	/**
	 * Permet de récupérer un message d'erreur s'il y en a un en session
	 * 
	 * @return string renvoi le message flash d'erreur s'il y en a un, sinon null 
	 */
	public function getErrorMessage()
	{
		if ($this->session->hasFlash(self::ERROR))
		{
			return $this->session->getFlash(self::ERROR);
		}
		
		return null;
	}
	
	
	/**
	 * Permet de mettre en session un message flash de prévention
	 * 
	 * @param string $msg le message que l'on veut stocker en tant que flash de prévention
	 */
	public function setWarningMessage($msg)
	{
		$this->session->setFlash('warning', $msg);
	}
	
	/**
	 * Permet de récupérer un message de prévention s'il y en a un en session
	 * 
	 * @return string renvoi le message flash de prévention s'il y en a un, sinon null 
	 */
	public function getWarningMessage()
	{
		if ($this->session->hasFlash(self::WARNING))
		{
			return $this->session->getFlash(self::WARNING);
		}
		
		return null;
	}
	
	
	/**
	 * Permet de mettre en session un message flash d'information
	 * 
	 * @param string $msg le message que l'on veut stocker en tant que flash d'infomation
	 */
	public function setInfoMessage($msg)
	{
		$this->session->setFlash('info', $msg);
	}
	
	/**
	 * Permet de récupérer un message d'information s'il y en a un en session
	 * 
	 * @return string renvoi le message flash d'information s'il y en a un, sinon null 
	 */
	public function getInfoMessage()
	{
		if ($this->session->hasFlash(self::INFO))
		{
			return $this->session->getFlash(self::INFO);
		}
		
		return null;
	}
	
	
	/**
	 * Méthode appelée lors de l'instanciation du service pour setter la session courante
	 * 
	 * @param Symfony\Component\HttpFoundation\Session\Session $session la session à sauvegarder
	 */
	public function setSession(Session $session)
	{
		$this->session = $session;
	}
}