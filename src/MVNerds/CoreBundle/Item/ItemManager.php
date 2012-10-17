<?php

namespace MVNerds\CoreBundle\Item;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\Item;
use MVNerds\CoreBundle\Model\ItemQuery;
use MVNerds\CoreBundle\Model\ItemPeer;
use MVNerds\CoreBundle\Model\ItemI18nPeer;

class ItemManager
{
	private $userLocale;
	
	/**
	 * Permet de récupérer le noms des champions de la base dans un tableau
	 */
	public function getItemsName()
	{
		return ItemQuery::create()
			->joinI18n($this->userLocale, 'i18n')
			->withColumn('i18n.name', 'name')
			->select(array('name'))
			->OrderBy('name')
		->find();
	}

	/**
	 * Supprime un item en fonction de son id $id
	 * 
	 * @param integer $id l'id de l'item à supprimer
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé à l'id $id
	 */
	public function deleteById($id)
	{
		$item = ItemQuery::create()
			->add(ItemPeer::ID, $id)
		->findOne();

		if (null === $item)
		{
			throw new InvalidArgumentException('Item with id:' . $id . ' does not exist!');
		}

		// Finally
		$item->delete();
	}
	
	/**
	 * Supprime un item en fonction de son slug $slug
	 * 
	 * @param strin $slug le slug de l'item  à supprimer
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé au slug $slug
	 */
	public function deleteBySlug($slug)
	{
		$item = ItemQuery::create()
			->add(ItemPeer::SLUG, $slug)
		->findOne();

		if (null === $item)
		{
			throw new InvalidArgumentException('Item with slug:' . $slug . ' does not exist!');
		}

		// Finally
		$item->delete();
	}

	/**
	 * Récupère un objet Item à partir de son identifiant $id 
	 * 
	 * @param integer $id l'id de l'item dont on souhaite récupérer l'objet Item associé 
	 * @return MVNerds\CoreBundle\Model\Item l'objet Item qui correspond à l'id $id 
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé à l'id $id
	 */
	public function findOneById($id)
	{
		$item = ItemQuery::create()
			->add(ItemPeer::ID, $id)
		->findOne();

		if (null === $item)
		{
			throw new InvalidArgumentException('No item with id:' . $id . '!');
		}

		return $item;
	}
	
	/**
	 * Récupère un objet Item à partir de son slug $slug
	 * 
	 * @param string $slug le slug de l'item dont on souhaite récupérer l'objet Item associé 
	 * @return MVNerds\CoreBundle\Model\Item l'objet Item qui correspond au slug $slug 
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé au slug $slug
	 */
	public function findBySlug($slug)
	{
		$item = ItemQuery::create()
			->joinWithI18n($this->userLocale)
			->add(ItemPeer::SLUG, $slug)
		->findOne();

		if (null === $item)
		{
			throw new InvalidArgumentException('No item with slug:' . $slug . '!');
		}

		return $item;
	}
	
	/**
	 * Récupère un objet Item à partir de son nom $name
	 * 
	 * @param string $name le nom du item dont on souhaite récupérer l'objet Item associé 
	 * @return MVNerds\CoreBundle\Model\Item l'objet Item qui correspond au nom $name
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé au nom  $name
	 */
	public function findByName($name)
	{
		$item = ItemQuery::create()
			->joinWithI18n($this->userLocale)
			->add(ItemI18nPeer::NAME, $name)
		->findOne();

		if (null === $item)
		{
			throw new InvalidArgumentException('No item with name:' . $name . '!');
		}

		return $item;
	}

	/**
	 * Récupère un item à partir de son riot code $code
	 * 
	 * @param type $code le code associé à l'item à récupérer
	 * @return MVNerds\CoreBundle\Model\Item
	 */
	public function findByCode($code)
	{
		$item = ItemQuery::create()
			->joinWithI18n($this->userLocale)
			->add(ItemPeer::RIOT_CODE, $code)
		->findOne();

		if (null === $item)
		{
			throw new InvalidArgumentException('No item with code:' . $code. '!');
		}

		return $item;
	}
	
	/**
	 * Récupère tous les items de la base de données
	 * 
	 * @return PropelCollection<MVNerds\CoreBundle\Model\Item> retourne un objet PropelCollection qui contient
	 * tous les items de la base de données
	 */
	public function findAll()
	{
		return ItemQuery::create()
			->joinWithI18n($this->userLocale)
			->OrderBy(ItemPeer::ID)
		->find();
	}
	
	/**
	 * Récupère tous les items de la base de données avec leurs tags
	 * 
	 * @return PropelCollection<MVNerds\CoreBundle\Model\Item> retourne un objet PropelCollection qui contient
	 * tous les items de la base de données avec leurs tags
	 */
	public function findAllWithTags($locale)
	{
		return ItemQuery::create()
			->joinWithI18n($this->userLocale)
			->joinWith('ItemTag', \Criteria::LEFT_JOIN)
			->joinWith('ItemTag.Tag', \Criteria::LEFT_JOIN)
			->joinWith('Tag.TagI18n', \Criteria::LEFT_JOIN)
			->OrderBy(ItemPeer::ID)
		->find();
	}

	/**
	 * Permet de faire persister en base de données l'item $item
	 * 
	 * @param \MVNerds\CoreBundle\Model\Item $item l'objet Item à faire persister en base de données
	 */
	public function save(Item $item)
	{
		$item->save();
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
