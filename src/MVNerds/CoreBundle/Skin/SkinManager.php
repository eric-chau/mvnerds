<?php

namespace MVNerds\CoreBundle\Skin;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\Skin;
use MVNerds\CoreBundle\Model\SkinQuery;
use MVNerds\CoreBundle\Model\SkinPeer;
use MVNerds\CoreBundle\Model\SkinI18nPeer;

class SkinManager
{
	private $userLocale;
	
	public function findBySlug($slug)
	{
		$skin = SkinQuery::create()
			->joinWithI18n($this->userLocale)
			->add(SkinPeer::SLUG, $slug)
		->findOne();

		if (null === $skin)
		{
			throw new InvalidArgumentException('No skin with slug:' . $slug . '!');
		}

		return $skin;
	}
	
	/**
	 * Récupère un objet Champion à partir de son nom $name
	 * 
	 * @param string $name le nom du champion dont on souhaite récupérer l'objet Champion associé 
	 * @return MVNerds\CoreBundle\Model\Champion l'objet Champion qui correspond au nom $name
	 * @throws InvalidArgumentException exception levé si aucun champion n'est associé au nom  $name
	 */
	public function findByName($name, $locale = null)
	{
		if (!$locale) {
			$locale = $this->userLocale;
		}
		
		$skin = SkinQuery::create()
			->joinWithI18n($locale)
			->add(SkinI18nPeer::NAME, $name)
		->findOne();

		if (null === $skin)
		{
			throw new InvalidArgumentException('No skin with name:' . $name . '!');
		}

		return $skin;
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
