<?php

namespace MVNerds\CoreBundle\News;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\News;
use MVNerds\CoreBundle\Model\NewsQuery;
use MVNerds\CoreBundle\Model\NewsPeer;

class NewsManager
{
	private $userLocale;
	
	/**
	 * Supprime un item en fonction de son slug $slug
	 * 
	 * @param strin $slug le slug de l'item  à supprimer
	 * @throws InvalidArgumentException exception levé si aucun item n'est associé au slug $slug
	 */
	public function deleteBySlug($slug)
	{
		$news = NewsQuery::create()
			->joinWithI18n($this->userLocale)
			->add(NewsPeer::SLUG, $slug)
		->findOne();

		if (null === $news)
		{
			throw new InvalidArgumentException('News with slug:' . $slug . ' does not exist!');
		}

		// Finally
		$news->delete();
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
		$news = NewsQuery::create()
			->joinWithI18n($this->userLocale)
			->joinWith('NewsCategory')
			->joinWith('User', \Criteria::LEFT_JOIN)
			->add(NewsPeer::SLUG, $slug)
		->findOne();

		if (null === $news)
		{
			throw new InvalidArgumentException('No news with slug:' . $news . '!');
		}

		return $news;
	}
	
	public function findPublicBySlug($slug)
	{
		$news = NewsQuery::create()
			->filterBySlug($slug)
			->filterByStatus(NewsPeer::STATUS_PUBLIC)
			->joinWithI18n($this->userLocale)
			->joinWith('NewsCategory')
			->joinWith('User', \Criteria::LEFT_JOIN)
		->findOne();

		if (null === $news)
		{
			throw new InvalidArgumentException('No news with slug:' . $news . '!');
		}

		return $news;
	}
	
	public function findAllPublic()
	{
		return NewsQuery::create()
			->orderByCreateTime('desc')
			->joinWithI18n($this->userLocale)
			->joinWith('NewsCategory')
			->joinWith('User', \Criteria::LEFT_JOIN)
			->where(NewsPeer::STATUS . ' LIKE ?', NewsPeer::STATUS_PUBLIC)
		->find();
	}
	
	public function findAllNotPrivate()
	{
		return NewsQuery::create()
			->orderByCreateTime('desc')
			->joinWithI18n($this->userLocale)
			->joinWith('NewsCategory')
			->joinWith('User', \Criteria::LEFT_JOIN)
			->where(NewsPeer::STATUS . ' NOT LIKE ?', NewsPeer::STATUS_PRIVATE)
		->find();
	}
	
	public function findAll()
	{
		return NewsQuery::create()
			->joinWithI18n($this->userLocale)
			->joinWith('NewsCategory')
			->joinWith('User', \Criteria::LEFT_JOIN)
		->find();
	}
	
	/**
	 * Récupère les derniers highlights publiques
	 */
	public function findPublicHighlights()
	{
		$news = NewsQuery::create()
			->joinWith('User')
			->joinWith('NewsCategory')
			->where(NewsPeer::STATUS . ' LIKE ?', NewsPeer::STATUS_PUBLIC)
			->add(NewsPeer::IS_HIGHLIGHT, '1')
			->orderByCreateTime(\Criteria::DESC)
			->limit(5)
		->find();

		$news->populateRelation('NewsI18n');
		
		if (null === $news)
		{
			throw new InvalidArgumentException('No news found !');
		}

		return $news;
	}	
	
	/**
	 * Récupère les derniers highlights non privés
	 */
	public function findNotPrivateHighlights()
	{
		$news = NewsQuery::create()
			->joinWith('User')
			->joinWith('NewsCategory')
			->where(NewsPeer::STATUS . ' NOT LIKE ?', NewsPeer::STATUS_PRIVATE)
			->add(NewsPeer::IS_HIGHLIGHT, '1')
			->orderByCreateTime(\Criteria::DESC)
			->limit(5)
		->find();
		
		$news->populateRelation('NewsI18n');
		
		if (null === $news)
		{
			throw new InvalidArgumentException('No news found !');
		}

		return $news;
	}	

	/**
	 * Permet de faire persister en base de données la news $news
	 * 
	 * @param \MVNerds\CoreBundle\Model\News $news l'objet News à faire persister en base de données
	 */
	public function save(News $news)
	{
		$news->save();
	}
	
	public function delete(News $news)
	{
		$news->delete();
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
