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
	 * Permet de récupérer le noms des champions de la base dans un tableau
	 */
	public function getPublicItemsName()
	{
		return ItemQuery::create()
			->add(ItemPeer::IS_OBSOLETE, '0')
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
			->joinWith('ItemPrimaryEffect', \Criteria::LEFT_JOIN)
			->joinWith('ItemSecondaryEffect', \Criteria::LEFT_JOIN)
			->joinWith('ItemTag', \Criteria::LEFT_JOIN)
			->joinWith('ItemGameMode', \Criteria::LEFT_JOIN)
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
	 * Récupère un objet Item à partir de son slug $slug pour les popovers
	 * 
	 * @param string $slug le slug de l'item dont on souhaite récupérer l'objet Item associé 
	 * @return MVNerds\CoreBundle\Model\Item l'objet Item qui correspond au slug $slug 
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé au slug $slug
	 */
	public function findBySlugForPopover($slug)
	{
		$item = ItemQuery::create()
			->joinWith('ItemGeneologyRelatedByParentId igrbp', \Criteria::LEFT_JOIN)
			->joinWith('igrbp.ItemRelatedByChildId irbc', \Criteria::LEFT_JOIN)
			->addJoinCondition('irbc', 'irbc.IsObsolete = ?', '0')
			->joinWith('ItemGeneologyRelatedByChildId igrbc', \Criteria::LEFT_JOIN)
			->joinWith('igrbc.ItemRelatedByParentId irbp', \Criteria::LEFT_JOIN)
			->addJoinCondition('irbp', 'irbp.IsObsolete = ?', '0')
			->joinWithI18n($this->userLocale, \Criteria::LEFT_JOIN)
			->joinWith('ItemPrimaryEffect ipe', \Criteria::LEFT_JOIN)
			->joinWith('ipe.PrimaryEffect pe', \Criteria::LEFT_JOIN)
			->joinWith('pe.PrimaryEffectI18n pei', \Criteria::LEFT_JOIN)
			->addJoinCondition('pei', 'pei.Lang LIKE ?', $this->userLocale)
			->joinWith('ItemSecondaryEffect ise', \Criteria::LEFT_JOIN)
			->joinWith('ise.ItemSecondaryEffectI18n isei', \Criteria::LEFT_JOIN)
			->addJoinCondition('isei', 'isei.Lang LIKE ?', $this->userLocale)
			->add(ItemPeer::SLUG, $slug)
		->find();

		if (null === $item)
		{
			throw new InvalidArgumentException('No item with slug:' . $slug . '!');
		}

		return $item[0];
	}
	
	public function findAllActiveForItemModal()
	{
		return ItemQuery::create()
			->joinWith('ItemGeneologyRelatedByParentId igrbp', \Criteria::LEFT_JOIN)
			->joinWith('igrbp.ItemRelatedByChildId irbc', \Criteria::LEFT_JOIN)
			->addJoinCondition('irbc', 'irbc.IsObsolete = ?', '0')
			->joinWith('ItemGeneologyRelatedByChildId igrbc', \Criteria::LEFT_JOIN)
			->joinWith('igrbc.ItemRelatedByParentId irbp', \Criteria::LEFT_JOIN)
			->addJoinCondition('irbp', 'irbp.IsObsolete = ?', '0')
			->joinWithI18n($this->userLocale, \Criteria::LEFT_JOIN)
			->joinWith('ItemPrimaryEffect ipe', \Criteria::LEFT_JOIN)
			->joinWith('ipe.PrimaryEffect pe', \Criteria::LEFT_JOIN)
			->joinWith('pe.PrimaryEffectI18n pei', \Criteria::LEFT_JOIN)
			->addJoinCondition('pei', 'pei.Lang LIKE ?', $this->userLocale)
			->joinWith('ItemSecondaryEffect ise', \Criteria::LEFT_JOIN)
			->joinWith('ise.ItemSecondaryEffectI18n isei', \Criteria::LEFT_JOIN)
			->addJoinCondition('isei', 'isei.Lang LIKE ?', $this->userLocale)
			->add(ItemPeer::IS_OBSOLETE, false)
		->find();
	}
	
	/**
	 * Récupère un objet Item à partir de son slug $slug
	 * 
	 * @param string $slug le slug de l'item dont on souhaite récupérer l'objet Item associé 
	 * @return MVNerds\CoreBundle\Model\Item l'objet Item qui correspond au slug $slug 
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé au slug $slug
	 */
	public function findBySlugWithI18n($slug)
	{
		$item = ItemQuery::create()
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
	public function findByName($name, $locale = null)
	{
		if(null == $locale)
		{
			$locale = $this->userLocale;
		}
		$item = ItemQuery::create()
			->joinWithI18n($locale)
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
	public function findByCode($code, $locale = null)
	{
		if(null == $locale)
		{
			$locale = $this->userLocale;
		}
		$item = ItemQuery::create($locale)
			->joinWithI18n()
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
				
			->joinWith('ItemTag', \Criteria::LEFT_JOIN)
			->joinWith('ItemTag.Tag', \Criteria::LEFT_JOIN)
			->joinWith('Tag.TagI18n', \Criteria::LEFT_JOIN)
				
			->joinWith('ItemGameMode', \Criteria::LEFT_JOIN)
			->joinWith('ItemGameMode.GameMode', \Criteria::LEFT_JOIN)
			
				
			->orderBy('ItemI18n.NAME')
		->find();
	}
	
	/**
	 * Récupère tous les items de la base de données avec leurs tags
	 * 
	 * @return PropelCollection<MVNerds\CoreBundle\Model\Item> retourne un objet PropelCollection qui contient
	 * tous les items de la base de données avec leurs tags
	 */
	public function findAllActive($locale = null)
	{		
		if( $locale == null )
			$locale = $this->userLocale;
		
		return ItemQuery::create()
			->add(ItemPeer::IS_OBSOLETE, '0')
			->joinWithI18n($locale)
				
			->joinWith('ItemTag', \Criteria::LEFT_JOIN)
			->joinWith('ItemTag.Tag', \Criteria::LEFT_JOIN)
			->joinWith('Tag.TagI18n', \Criteria::LEFT_JOIN)
				
			->joinWith('ItemGameMode', \Criteria::LEFT_JOIN)
			->joinWith('ItemGameMode.GameMode', \Criteria::LEFT_JOIN)
			
				
			->orderBy('ItemI18n.NAME')
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
