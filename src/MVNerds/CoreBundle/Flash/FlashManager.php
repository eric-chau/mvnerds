<?php

namespace MVNerds\CoreBundle\Flash;

use Symfony\Component\HttpFoundation\Session\Session;

class FlashManager
{
	private $session;

	const SUCCESS = 'success';
	const ERROR = 'error';
	const WARNING = 'warning';
	const INFO = 'info';
			
	public function setSuccessMessage($str)
	{
		$this->session->setFlash(self::SUCCESS, $str);
	}
	
	public function getSuccessMessage()
	{
		if ($this->session->hasFlash(self::SUCCESS))
		{
			return $this->session->getFlash(self::SUCCESS);
		}
		
		return null;
	}
	
	public function setErrorMessage($str)
	{
		$this->session->setFlash(self::ERROR, $str);
	}
	
	public function getErrorMessage()
	{
		if ($this->session->hasFlash(self::ERROR))
		{
			return $this->session->getFlash(self::ERROR);
		}
		
		return null;
	}
	
	public function setWarningMessage($str)
	{
		$this->session->setFlash('warning', $str);
	}
	
	public function getWarningMessage()
	{
		if ($this->session->hasFlash(self::WARNING))
		{
			return $this->session->getFlash(self::WARNING);
		}
		
		return null;
	}
	
	public function setInfoMessage($str)
	{
		$this->session->setFlash('info', $str);
	}
	
	public function getInfoMessage()
	{
		if ($this->session->hasFlash(self::INFO))
		{
			return $this->session->getFlash(self::INFO);
		}
		
		return null;
	}
	
	public function setSession(Session $session)
	{
		$this->session = $session;
	}
	
	public function getSession()
	{
		return $this->session;
	}
}