<?php

namespace MVNerds\CoreBundle\Twig;

use Twig_Extension;
use Twig_Function_Method;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

use MVNerds\CoreBundle\Flash\FlashManager;

/**
 * Extension Twig qui permet de rendre depuis un template twig directement les messages flash
 * à l'aide des méthodes suivantes :
 * 
 * {{ render_success_flash() }}
 * {{ render_error_flash() }}
 * {{ render_warning_flash() }}
 * {{ render_info_flash() }}
 * 
 * A noter que cet extension gère également la traduction du contenu, il est donc conseillé de mettre
 * des tokens de traduction dans les messages flash
 */
class MVNerdsFlashExtension extends Twig_Extension
{
	private $flashManager;
	private $translator;
	
	public function getFunctions()
	{
		return array(
			'render_success_flash'	=> new Twig_Function_Method($this, 'renderFlashSuccessMethod'),
			'render_error_flash'	=> new Twig_Function_Method($this, 'renderFlashErrorMethod'),
			'render_warning_flash'	=> new Twig_Function_Method($this, 'renderFlashWarningMethod'),
			'render_info_flash'	=> new Twig_Function_Method($this, 'renderFlashInfoMethod'),
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
	
	/**
	 * --> Méthode privée <--
	 * Méthode générique qui se base sur le $type passé en paramètre pour rendre de manière
	 * uniforme le message flash avec le bon type de div.alert ainsi que la traduction du message
	 * s'il en existe une dans le dictionnaire de traduction
	 * 
	 * @param string $type correspond au type de message que l'on veut afficher
	 * @return string la chaîne HTML à afficher dans un template twig; à noter qu'il faut utiliser
	 * les filtres {% autoespace false %} {% endautoescape %} pour que le code html soit interprété
	 */
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
	
	
	/**
	 * Utilisé lors de l'instanciation du service dans le service container pour injecter
	 * le service FlashManager
	 * 
	 * @param MVNerds\CoreBundle\Flash\FlashManager $flashManager le service à setter à $this->flashManager
	 */
	public function setFlashManager(FlashManager $flashManager)
	{
		$this->flashManager = $flashManager;
	}
	
	/**
	 * Utilisé lors de l'instanciation du service dans le service container pour injecter
	 * le service translator de Symfony2
	 * 
	 * @param Symfony\Bundle\FrameworkBundle\Translation\Translator $translator le service à setter à $this->translator
	 */
	public function setTranslatorService(Translator $translator)
	{
		$this->translator = $translator;
	}
}