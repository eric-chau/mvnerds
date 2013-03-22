<?php

namespace MVNerds\CoreBundle\ItemBuild;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;
use \PropelException;
use \Propel;
use \PropelObjectFormatter;

use MVNerds\CoreBundle\Model\Item;
use MVNerds\CoreBundle\Model\ItemBuild;
use MVNerds\CoreBundle\Model\ItemBuildQuery;
use MVNerds\CoreBundle\Model\ItemBuildPeer;
use MVNerds\CoreBundle\Model\ChampionItemBuildPeer;

class ItemBuildManager
{
	private $userLocale;
	
	/**
	 * Récupère un objet ItemBuild à partir de son slug $slug
	 * 
	 * @param string $slug le slug de l'item build dont on souhaite récupérer l'objet ItemBuild associé 
	 * @return ItemBuild l'objet ItemBuild qui correspond au slug $slug
	 * @throws InvalidArgumentException exception levé si aucun item build n'est associé au slug $slug
	 */
	public function findBySlug($slug)
	{
		
		$itemBuild = ItemBuildQuery::create()
			->joinWith('User')
			->joinWith('User.Profile')
			->joinWith('Profile.GameAccount', \Criteria::LEFT_JOIN)
			->joinWith('Profile.Avatar')
			->joinWith('GameMode')
			->joinWith('ItemBuildBlock')
			->joinWith('ItemBuildBlock.ItemBuildBlockItem')
			->useItemBuildBlockQuery()
				->orderByPosition()
				->useItemBuildBlockItemQuery()
					->orderByPosition()
				->endUse()
			->endUse()
			->joinWith('ItemBuildBlockItem.Item')
			->joinWith('Item.ItemI18n')
			->where('ItemI18n.Lang LIKE ?', $this->userLocale)
			->add(ItemBuildPeer::SLUG, $slug)
		->find();
		
		$championItemBuildsCriteria = \MVNerds\CoreBundle\Model\ChampionItemBuildQuery::create()
				->joinWith('Champion')
				->joinWith('Champion.ChampionI18n');
		$itemBuild->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);

		if (null === $itemBuild || null === $itemBuild[0])
		{
			throw new InvalidArgumentException('No item build with slug:' . $slug . '!');
		}

		return $itemBuild[0];
	}
	
	public function findAll($offset, $limit)
	{
		$itemBuilds = ItemBuildQuery::create()
			->orderById()
			->offset($offset)
			->limit($limit)
		->find();
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}
	
	public function findAllPublic()
	{
		$itemBuilds = ItemBuildQuery::create()
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion chp', \Criteria::LEFT_JOIN)
			->joinWith('chp.ChampionI18n', \Criteria::LEFT_JOIN)
			->joinWith('User', \Criteria::LEFT_JOIN)
			->orderByCreateTime('Desc')
		->find();
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}
	
	public function findAllPublicAjax($limitStart = 0, $limitLength = 2, $orderArr = array('Create_Time' => 'desc'), $whereArr = array(), $championName = null)
	{
		$itemBuildsQuery = ItemBuildQuery::create()
			->offset($limitStart)
			->limit($limitLength)
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->joinWith('User', \Criteria::LEFT_JOIN);
		
		foreach($orderArr as $orderCol => $orderDir)
		{
			switch ($orderDir) {
				case 'asc':
					$itemBuildsQuery->addAscendingOrderByColumn($orderCol);
					break;
				case 'desc':
					$itemBuildsQuery->addDescendingOrderByColumn($orderCol);
					break;
				default:
					throw new PropelException('ModelCriteria::orderBy() only accepts Criteria::ASC or Criteria::DESC as argument');
			}
		}
		foreach($whereArr as $whereCol => $whereVal)
		{
			$itemBuildsQuery->add($whereCol, '%' . $whereVal . '%', \Criteria::LIKE);
		}
		
		if ($championName && $championName != '') 
		{
			$championsIds = \MVNerds\CoreBundle\Model\ChampionQuery::create()
				->select(array('ChampionI18n.Id'))
				->joinWithI18n()
				->add(\MVNerds\CoreBundle\Model\ChampionI18nPeer::NAME, '%'.$championName.'%', \Criteria::LIKE)
			->find()->toArray();
			
			$itemBuildsQuery->join('ChampionItemBuild')->addJoinCondition('ChampionItemBuild', 'ChampionItemBuild.ChampionId IN ?', $championsIds)->distinct();
		}
		
		$itemBuilds = $itemBuildsQuery->find();
		
		$championItemBuildsCriteria = \MVNerds\CoreBundle\Model\ChampionItemBuildQuery::create()
				->joinWith('GameMode')
				->joinWith('Champion')
				->joinWith('Champion.ChampionI18n');
		
		
		
		$itemBuilds->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}
	
	public function countAllPublic()
	{
		$itemBuildsCount = ItemBuildQuery::create()
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
		->count();
		
		return $itemBuildsCount;
	}
	
	public function countAllPublicAjax($whereArr = array(), $championName = null)
	{
		$itemBuildsQuery = ItemBuildQuery::create()
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->joinWith('User', \Criteria::LEFT_JOIN);
	
		foreach($whereArr as $whereCol => $whereVal)
		{
			$itemBuildsQuery->add($whereCol, '%' . $whereVal . '%', \Criteria::LIKE);
		}
		
		if ($championName && $championName != '') 
		{
			$championsIds = \MVNerds\CoreBundle\Model\ChampionQuery::create()
				->select(array('ChampionI18n.Id'))
				->joinWithI18n()
				->add(\MVNerds\CoreBundle\Model\ChampionI18nPeer::NAME, '%'.$championName.'%', \Criteria::LIKE)
			->find()->toArray();
			
			$itemBuildsQuery->join('ChampionItemBuild')->addJoinCondition('ChampionItemBuild', 'ChampionItemBuild.ChampionId IN ?', $championsIds)->distinct();
		}
		
		return $itemBuildsQuery->count();
	}
	
	public function findByUserId($userId)
	{
		$itemBuilds = ItemBuildQuery::create()
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion chp', \Criteria::LEFT_JOIN)
			->joinWith('chp.ChampionI18n', \Criteria::LEFT_JOIN)
			->joinWith('User', \Criteria::LEFT_JOIN)
			->add(ItemBuildPeer::USER_ID, $userId)
		->find();
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}	
	public function findPublicByUserId($userId)
	{
		$itemBuilds = ItemBuildQuery::create()
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion chp', \Criteria::LEFT_JOIN)
			->joinWith('chp.ChampionI18n', \Criteria::LEFT_JOIN)
			->joinWith('User', \Criteria::LEFT_JOIN)
			->add(ItemBuildPeer::USER_ID, $userId)
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
		->find();
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}	
	
	/**
	 * Récupère les builds les plus récents
	 */
	public function findLatestBuilds()
	{
		$itemBuilds = ItemBuildQuery::create()
			->joinWith('User')
			->joinWith('GameMode')
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->orderById(\Criteria::DESC)
			->limit(5)
		->find();
		
		$championItemBuildsCriteria = \MVNerds\CoreBundle\Model\ChampionItemBuildQuery::create()
				->joinWith('Champion')
				->joinWith('Champion.ChampionI18n');
		
		$itemBuilds->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);
				
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}
		return $itemBuilds;
	}	
	
	/**
	 * Récupère les builds les plus téléchargés
	 */
	public function findMostDownloadedBuilds()
	{
		$itemBuilds = ItemBuildQuery::create()
			->joinWith('User')
			->joinWith('GameMode')
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->orderByDownload(\Criteria::DESC)
			->limit(5)
		->find();
		
		$championItemBuildsCriteria = \MVNerds\CoreBundle\Model\ChampionItemBuildQuery::create()
				->joinWith('Champion')
				->joinWith('Champion.ChampionI18n');
		
		$itemBuilds->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}	
	
	/**
	 * Récupère les 10 meilleurs builds pour un champion donné
	 */
	public function findBestBuildsForChampion($champion)
	{
		$con = Propel::getConnection(ItemBuildPeer::DATABASE_NAME);
		$sql = "SELECT item_build.* FROM item_build 
				WHERE status = :status
				AND item_build.id IN (SELECT cib1.item_build_id FROM champion_item_build AS cib1 WHERE cib1.champion_id = :championId 
				AND (SELECT COUNT(*) FROM champion_item_build AS cib2 WHERE cib2.item_build_id = cib1.item_build_id) = 1) 
				ORDER BY like_count / (like_count + dislike_count) * 100 DESC, item_build.download DESC
				LIMIT 10";
		
		$stmt = $con->prepare($sql);
		$stmt->execute(array(':championId' => $champion->getId(), ':status' => ItemBuildPeer::STATUS_PUBLIC));
		
		$formatter = new PropelObjectFormatter();
		$formatter->setClass('MVNerds\CoreBundle\Model\ItemBuild');
		$itemBuilds = $formatter->format($stmt);
	
		$championItemBuildsCriteria = \MVNerds\CoreBundle\Model\ChampionItemBuildQuery::create()
				->joinWith('Champion')
				->joinWith('Champion.ChampionI18n');
		$itemBuilds->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);
		$itemBuilds->populateRelation('User');
		$itemBuilds->populateRelation('GameMode');
		
		if (null === $itemBuilds) {
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
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
	
	/**
	 * Rends tous les item builds qui contiennent l item ayant pour id $id 
	 */
	public function findByItemId($id)
	{
		$itemBuilds = ItemBuildQuery::create()
				->joinWithItemBuildItems()
				->add(\MVNerds\CoreBundle\Model\ItemBuildItemsPeer::ITEM_ID, $id)
		->find();

		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build where item id = ' . $id . '!');
		}

		return $itemBuilds;
	}
	
	public function findItemBuildItemsByItemId($id)
	{
		$itemBuildItems = \MVNerds\CoreBundle\Model\ItemBuildItemsQuery::create()
				->add(\MVNerds\CoreBundle\Model\ItemBuildItemsPeer::ITEM_ID, $id)
		->find();

		if (null === $itemBuildItems)
		{
			throw new InvalidArgumentException('No item build items where item id = ' . $id . '!');
		}

		return $itemBuildItems;
	}
	
	public function countNbBuildsByUserId($id)
	{
		$nbItemBuilds = ItemBuildQuery::create()
				->add(ItemBuildPeer::USER_ID, $id)
		->count();

		return $nbItemBuilds;
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
