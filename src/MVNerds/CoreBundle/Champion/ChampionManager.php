<?php

namespace MVNerds\CoreBundle\Champion;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\Champion;
use MVNerds\CoreBundle\Model\ChampionQuery;
use MVNerds\CoreBundle\Model\ChampionPeer;
use MVNerds\CoreBundle\Model\ChampionI18nPeer;

class ChampionManager
{
	private $userLocale;
	
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
		$oldChampion = ChampionQuery::create()
					->joinWithI18n()
					->add(ChampionI18nPeer::NAME, $champion->getName())
				->findOne();
		if (null != $oldChampion)
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
			->joinI18n($this->userLocale, 'i18n')
			->withColumn('i18n.name', 'name')
			->select(array('name'))
			->OrderBy('name')
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
	 * Supprime un champion en fonction de son slug $slug
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
	 * @throws InvalidArgumentException exception levé si aucun champion n'est associé au slug $slug
	 */
	public function findBySlug($slug)
	{
		$champion = ChampionQuery::create()
			->joinWithI18n($this->userLocale)
			->add(ChampionPeer::SLUG, $slug)
		->findOne();

		if (null === $champion)
		{
			throw new InvalidArgumentException('No champion with slug:' . $slug . '!');
		}

		return $champion;
	}
	
	public function findBySlugWithSkillsAndSkins($slug)
	{
		$champion = ChampionQuery::create()
			->joinWithI18n($this->userLocale)
			->joinWithSkill()
			->joinWith('Skin', \Criteria::LEFT_JOIN)
			->joinWith('Skill.SkillI18n', \Criteria::LEFT_JOIN)
			->joinWith('Skin.SkinI18n', \Criteria::LEFT_JOIN)
			->addJoinCondition('SkillI18n', 'SkillI18n.Lang = ?', $this->userLocale)
			->addJoinCondition('SkinI18n', 'SkinI18n.Lang = ?', $this->userLocale)
			->useSkillQuery()
				->orderByPosition()
			->endUse()
			->add(ChampionPeer::SLUG, $slug)
		->find();

		if (null === $champion || null === $champion[0])
		{
			throw new InvalidArgumentException('No champion with slug:' . $slug . '!');
		}

		return $champion[0];
	}
	
	
	/**
	 * Récupère un objet Champion à partir de son slug $slug
	 * 
	 * @param string $slug le nom du champion dont on souhaite récupérer l'objet Champion associé 
	 * @return MVNerds\CoreBundle\Model\Champion l'objet Champion qui correspond au slug $slug 
	 * @throws InvalidArgumentException exception levé si aucun champion n'est associé au slug $slug
	 */
	public function findBySlugWithI18ns($slug)
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
	 * Récupère une collection de Champion à partir d'un tableau de slugs
	 * 
	 * @param array $championsSlugs les slugs des champions pour lesquels on veut récupérer les objets associés
	 * @return PropelCollection<MVNerds\CoreBundle\Model\Champion> la collection de Champion qui correspond aux slugs $slugs
	 * @throws InvalidArgumentException exception levé si aucune liste de slugs est fournie
	 */
	public function findManyBySlugs($championsSlugs)
	{
		$champions = ChampionQuery::create()
			->joinWithI18n($this->userLocale)
			->add(ChampionPeer::SLUG, $championsSlugs,\Criteria::IN)
		->find();

		if (null === $championsSlugs)
		{
			throw new InvalidArgumentException('No champion slugs given!');
		}

		return $champions;
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
		$locale = $locale == null ? $this->userLocale : $locale;
		$champion = ChampionQuery::create()
			->joinWithI18n($locale, \Criteria::LEFT_JOIN)
			->add(ChampionI18nPeer::NAME, $name)
		->findOne();

		if (null === $champion)
		{
			throw new InvalidArgumentException('No champion with name:' . $name . '!');
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
			->joinWithI18n($this->userLocale)
			->OrderBy(ChampionPeer::SLUG)
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
		return ChampionQuery::create()
			->joinWithI18n($this->userLocale)
			->joinWith('ChampionTag', \Criteria::LEFT_JOIN)
			->joinWith('ChampionTag.Tag', \Criteria::LEFT_JOIN)
			->joinWith('Tag.TagI18n', \Criteria::LEFT_JOIN)
			->OrderBy(ChampionPeer::SLUG)
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
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
	
	public function setLevelToChampions($level, $champions)
	{
		foreach ($champions as $champion) {
			$champion->setLevel($level);
		}
		
		return $champions;
	}
}
