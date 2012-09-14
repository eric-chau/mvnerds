<?php

namespace MVNerds\CoreBundle\Twig;

use Twig_Extension;
use Twig_Function_Method;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

use MVNerds\CoreBundle\Flash\FlashManager;

class MVNerdsFlashExtension extends Twig_Extension
{
	private $flashManager;
	private $translator;
	
	public function getFunctions()
	{
		return array(
			'render_flash_success'	=> new Twig_Function_Method($this, 'renderFlashSuccessMethod'),
			'render_flash_error'	=> new Twig_Function_Method($this, 'renderFlashErrorMethod'),
			'render_flash_warning'	=> new Twig_Function_Method($this, 'renderFlashWarningMethod'),
			'render_flash_info'	=> new Twig_Function_Method($this, 'renderFlashInfoMethod'),
		);
	}
	
	public function renderFlashSuccessMethod()
	{
		return $this->renderGenericFlashMethod(FlashManager::SUCCESS);
	}
	
	public function renderFlashErrorMethod()
	{
		return $this->renderGenericFlashMethod(FlashManager::ERROR);
	}
	
	public function renderFlashWarningMethod()
	{
		return $this->renderGenericFlashMethod(FlashManager::WARNING);
	}
	
	public function renderFlashInfoMethod()
	{
		return $this->renderGenericFlashMethod(FlashManager::INFO);
	}
	
	private function renderGenericFlashMethod($type)
	{
		$str = '';
		$method = 'get' . ucfirst($type) . 'Message';
		$msg = $this->flashManager->$method();
		if ($msg != null)
		{
			$str .= '<div class="alert alert-' . $type . '">';
			$str .= '<button type="button" class="close" data-dismiss="alert">×</button>';
			$str .= $this->translator->trans($msg) . '</div>';
		}
		
		return $str;
	}
	
	public function getName()
	{
		return 'mvnerds_flash_extension';
	}
	
	public function setFlashManager(FlashManager $flashManager)
	{
		$this->flashManager = $flashManager;
	}
	
	public function setTranslatorService(Translator $translator)
	{
		$this->translator = $translator;
	}
}