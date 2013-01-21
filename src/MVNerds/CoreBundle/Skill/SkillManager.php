<?php

namespace MVNerds\CoreBundle\Skill;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\Skill;
use MVNerds\CoreBundle\Model\SkillQuery;
use MVNerds\CoreBundle\Model\SkillPeer;
use MVNerds\CoreBundle\Model\SkillI18nPeer;

class SkillManager
{
	private $userLocale;
	
	public function findBySlug($slug)
	{
		$skill = SkillQuery::create()
			->joinWithI18n($this->userLocale)
			->add(SkillPeer::SLUG, $slug)
		->findOne();

		if (null === $skill)
		{
			throw new InvalidArgumentException('No skill with slug:' . $slug . '!');
		}

		return $skill;
	}
	
	/**
	 * Récupère un objet Champion à partir de son nom $name
	 * 
	 * @param string $name le nom du champion dont on souhaite récupérer l'objet Champion associé 
	 * @return MVNerds\CoreBundle\Model\Champion l'objet Champion qui correspond au nom $name
	 * @throws InvalidArgumentException exception levé si aucun champion n'est associé au nom  $name
	 */
	public function findByName($name)
	{
		$skill = SkillQuery::create()
			->joinWithI18n($this->userLocale)
			->add(SkillI18nPeer::NAME, $name)
		->findOne();

		if (null === $skill)
		{
			throw new InvalidArgumentException('No skill with name:' . $name . '!');
		}

		return $skill;
	}

	public function findByChampionAndPosition($champion, $position)
	{
		$skill = SkillQuery::create()
			->add(SkillPeer::CHAMPION_ID, $champion->getId())
			->add(SkillPeer::POSITION, $position)
		->findOne();

		if (null === $skill)
		{
			throw new InvalidArgumentException('No skill with champion_id :' . $champion->getId() . ' and position '. $position .'!');
		}

		return $skill;
	}
	
	/**
	 * Permet de persister en base de données le champion $champion
	 * 
	 * @param \MVNerds\CoreBundle\Model\Champion $champion l'objet Champion à faire persister en base de données
	 */
	public function save(Skill $skill)
	{
		$skill->save();
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
