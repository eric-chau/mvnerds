<?php

namespace MVNerds\CoreBundle\Champion;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MVNerds\CoreBundle\Model\Champion;
use MVNerds\CoreBundle\Model\ChampionQuery;
use MVNerds\CoreBundle\Model\ChampionPeer;

class ChampionManager
{

	/**
	 * Vérifies les informations contenu dans l'objet $champion passé en paramètre; si tout se passe bien,
	 * le champion est créé en base de données
	 * 
	 * @param \MVNerds\CoreBundle\Model\Champion $champion le champion à créer s'il contient des données valides
	 * @throws InvalidArgumentException exception levée lorsque :
	 * - Ce n'est pas un nouveau champion
	 * - Le nom est déjà utilisé
	 */
	public function createChampionIfValid(Champion $champion)
	{
		// On vérifie que c'est bien un nouveau champion en vérifiant qu'il n'a pas d'id
		if (null != $champion->getId())
		{
			throw new InvalidArgumentException('Seems that\'s given champion already exists!');
		}

		// On vérifie qu'il n'y a pas de champion dans la base de données avec le même nom
		if (null != ChampionQuery::create()->add(ChampionPeer::NAME, $champion->getName())->findOne())
		{
			throw new InvalidArgumentException('Champion with name:\'' . $champion->getName() . '\' already exists!');
		}

		// Finally
		$this->save($champion);
	}
	
	/**
	 * Permet de récupérer le noms des champions de la base dans un tableau
	 */
	public function getChampionsName()
	{
		return ChampionQuery::create()
			->select(array(ChampionPeer::NAME))
			->OrderBy(ChampionPeer::NAME)
		->find();
	}

	/**
	 * Supprime un champion en fonction de son id $id
	 * 
	 * @param integer $id l'id du champion à supprimer
	 * @throws InvalidArgumentException exception levé si aucun champion n'est associé à l'id $id
	 */
	public function deleteById($id)
	{
		$champion = ChampionQuery::create()
			->add(ChampionPeer::ID, $id)
		->findOne();

		if (null === $champion)
		{
			throw new InvalidArgumentException('Champion with id:' . $id . ' does not exist!');
		}

		// Finally
		$champion->delete();
	}
	
	/**
	 * Supprime un champion en fonction de son slug $id
	 * 
	 * @param strin $slug le slug du champion à supprimer
	 * @throws InvalidArgumentException exception levé si aucun champion n'est associé au slug $slug
	 */
	public function deleteBySlug($slug)
	{
		$champion = ChampionQuery::create()
			->add(ChampionPeer::SLUG, $slug)
		->findOne();

		if (null === $champion)
		{
			throw new InvalidArgumentException('Champion with slug:' . $slug . ' does not exist!');
		}

		// Finally
		$champion->delete();
	}

	/**
	 * Récupère un objet Champion à partir de son identifiant $id 
	 * 
	 * @param integer $id l'id du champion dont on souhaite récupérer l'objet Champion associé 
	 * @return MVNerds\CoreBundle\Model\Champion l'objet Champion qui correspond à l'id $id 
	 * @throws InvalidArgumentException exception levé si aucun champion n'est associé à l'id $id
	 */
	public function findOneById($id)
	{
		$champion = ChampionQuery::create()
			->add(ChampionPeer::ID, $id)
		->findOne();

		if (null === $champion)
		{
			throw new InvalidArgumentException('No champion with id:' . $id . '!');
		}

		return $champion;
	}
	
	/**
	 * Récupère un objet Champion à partir de son slug $slug
	 * 
	 * @param string $slug le nom du champion dont on souhaite récupérer l'objet Champion associé 
	 * @return MVNerds\CoreBundle\Model\Champion l'objet Champion qui correspond au slug $slug 
	 * @throws InvalidArgumentException exception levé si aucun champion n'est associé à l'id $id
	 */
	public function findBySlug($slug)
	{
		$champion = ChampionQuery::create()
			->add(ChampionPeer::SLUG, $slug)
		->findOne();

		if (null === $champion)
		{
			throw new InvalidArgumentException('No champion with slug:' . $slug . '!');
		}

		return $champion;
	}

	/**
	 * Récupère tous les champions de la base de données
	 * 
	 * @return PropelCollection<MVNerds\CoreBundle\Model\Champion> retourne un objet PropelCollection qui contient
	 * tous les champions de la base de données
	 */
	public function findAll()
	{
		return ChampionQuery::create()
			->joinWithI18n()
			->OrderBy(ChampionPeer::ID)
		->find();
	}
	
	/**
	 * Récupère tous les champions de la base de données avec leurs tags
	 * 
	 * @return PropelCollection<MVNerds\CoreBundle\Model\Champion> retourne un objet PropelCollection qui contient
	 * tous les champions de la base de données
	 */
	public function findAllWithTags()
	{
		return  ChampionQuery::create()
			->joinWith('ChampionTag')
			->joinWith('ChampionTag.Tag')
			->joinWith('Tag.TagI18n')
		   ->find();
	}

	/**
	 * Permet de persister en base de données le champion $champion
	 * 
	 * @param \MVNerds\CoreBundle\Model\Champion $champion l'objet Champion à faire persister en base de données
	 */
	public function save(Champion $champion)
	{
		$champion->save();
	}
}
