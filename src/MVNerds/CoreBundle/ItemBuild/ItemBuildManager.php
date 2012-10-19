<?php

namespace MVNerds\CoreBundle\ItemBuild;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\Item;
use MVNerds\CoreBundle\Model\ItemQuery;
use MVNerds\CoreBundle\Model\ItemPeer;
use MVNerds\CoreBundle\Model\ItemI18nPeer;
use MVNerds\CoreBundle\Model\ItemBuild;
use MVNerds\CoreBundle\Model\ItemBuildQuery;
use MVNerds\CoreBundle\Model\ItemBuildPeer;

class ItemBuildManager
{
	/**
	 * Récupère un objet ItemBuild à partir de son identifiant $id 
	 * 
	 * @param integer $id l'id de l'item build dont on souhaite récupérer l'objet ItemBuild associé 
	 * @return MVNerds\CoreBundle\Model\ItemBuild l'objet ItemBuild qui correspond à l'id $id 
	 * @throws InvalidArgumentException exception levé si aucun item build n'est associé à l'id $id
	 */
	public function findOneById($id)
	{
		$itemBuild = ItemBuildQuery::create()
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('Item', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion', \Criteria::LEFT_JOIN)
			->add(ItemBuildPeer::ID, $id)
		->findOne();

		if (null === $itemBuild)
		{
			throw new InvalidArgumentException('No item build with id:' . $id . '!');
		}

		return $itemBuild;
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
}
