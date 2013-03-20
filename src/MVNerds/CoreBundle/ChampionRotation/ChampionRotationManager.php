<?php

namespace MVNerds\CoreBundle\ChampionRotation;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\RotationQuery;
use MVNerds\CoreBundle\Model\RotationPeer;

class ChampionRotationManager
{
	private $userLocale;

	
	/**
	 * Récupère la dernière rotation de champion a avoir été créée
	 */
	public function findLast()
	{
		$rotations = RotationQuery::create()
			->joinWithI18n($this->userLocale)
			->joinWithChampionRotation()
			->joinWith('ChampionRotation.Champion')
			->joinWith('Champion.ChampionI18n')
			->orderByCreateTime(\Criteria::DESC)
			->orderBy('ChampionI18n.Name', \Criteria::ASC)
		->find();

		if (0 == count($rotations)) {
			throw new InvalidArgumentException('No rotation found');
		}

		return $rotations[0];
	}

	/**
	 * Récupère toutes les rotations de champion
	 */
	public function findAll()
	{
		return RotationQuery::create()
			->joinWithI18n($this->userLocale)
			->joinWithChampionRotation()
			->joinWith('ChampionRotation.Champion')
			->joinWith('Champion.ChampionI18n')
			->OrderBy(RotationPeer::CREATE_TIME, \Criteria::DESC)
		->find();
	}

	public function findById($id)
	{
		$rotation = RotationQuery::create()
			->add(RotationPeer::ID, $id)
		->findOne();
		
		if (null === $rotation)
		{
			throw new InvalidArgumentException('Rotation with id:' . $id . ' does not exist!');
		}

		return $rotation;
	}
	
	public function deleteById($id)
	{
		$rotation = RotationQuery::create()
			->add(RotationPeer::ID, $id)
		->findOne();
		
		if (null === $rotation)
		{
			throw new InvalidArgumentException('Rotation with id:' . $id . ' does not exist!');
		}

		$rotation->delete();
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
