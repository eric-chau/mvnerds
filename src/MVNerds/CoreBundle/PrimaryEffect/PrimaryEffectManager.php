<?php

namespace MVNerds\CoreBundle\PrimaryEffect;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\PrimaryEffect;
use MVNerds\CoreBundle\Model\PrimaryEffectQuery;
use MVNerds\CoreBundle\Model\PrimaryEffectPeer;
use MVNerds\CoreBundle\Model\PrimaryEffectI18nPeer;

class PrimaryEffectManager
{
	private $userLocale;
	
	/**
	 * Récupère un objet PrimaryEffect à partir de son identifiant $id 
	 * 
	 * @param integer $id l'id du PrimaryEffect dont on souhaite récupérer l'objet PrimaryEffect associé 
	 * @return MVNerds\CoreBundle\Model\PrimaryEffect l'objet PrimaryEffect qui correspond à l'id $id 
	 * @throws InvalidArgumentException exception levé si aucun PrimaryEffect n'est associé à l'id $id
	 */
	public function findOneById($id)
	{
		$primaryEffect = PrimaryEffectQuery::create()
			->add(PrimaryEffectPeer::ID, $id)
		->findOne();

		if (null === $primaryEffect)
		{
			throw new InvalidArgumentException('No primary effect with id:' . $id . '!');
		}

		return $primaryEffect;
	}
	
	/**
	 * Récupère un objet primary effect à partir de son slug $slug
	 * 
	 * @param string $slug le slug du primary effect dont on souhaite récupérer l'objet PrimaryEffect associé 
	 * @return MVNerds\CoreBundle\Model\PrimaryEffect l'objet PrimaryEffect qui correspond au slug $slug 
	 * @throws InvalidArgumentException exception levé si aucun PrimaryEffect n'est associé au slug $slug
	 */
	public function findBySlug($slug)
	{
		$primaryEffect = PrimaryEffectQuery::create()
			->joinWithI18n($this->userLocale)
			->add(PrimaryEffectPeer::SLUG, $slug)
		->findOne();

		if (null === $primaryEffect)
		{
			throw new InvalidArgumentException('No primary effect with slug:' . $slug . '!');
		}

		return $primaryEffect;
	}
	
	/**
	 * Récupère un objet primary effect à partir de son label $label
	 * 
	 * @param string $name le nom du item dont on souhaite récupérer l'objet Item associé 
	 * @return MVNerds\CoreBundle\Model\Item l'objet Item qui correspond au nom $name
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé au nom  $name
	 */
	public function findByLabel($label)
	{
		$primaryEffect = PrimaryEffectQuery::create()
			->joinWithI18n($this->userLocale)
			->add(PrimaryEffectI18nPeer::LABEL, $label)
		->findOne();

		if (null === $primaryEffect)
		{
			throw new InvalidArgumentException('No primary effect with label:' . $label . '!');
		}

		return $primaryEffect;
	}
	
	/**
	 * Récupère tous les primary effects de la base de données
	 * 
	 * @return PropelCollection<MVNerds\CoreBundle\Model\Item> retourne un objet PropelCollection qui contient
	 * tous les items de la base de données
	 */
	public function findAll()
	{
		return PrimaryEffectQuery::create()
			->joinWithI18n($this->userLocale)
			->OrderBy(PrimaryEffectPeer::ID)
		->find();
	}

	/**
	 * Permet de faire persister en base de données le primary effect $primaryEffect
	 * 
	 * @param \MVNerds\CoreBundle\Model\PrimaryEffect $primaryEffect l'objet Item à faire persister en base de données
	 */
	public function save(PrimaryEffect $primaryEffect)
	{
		$primaryEffect->save();
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
