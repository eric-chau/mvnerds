<?php

namespace MVNerds\CoreBundle\News;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\News;
use MVNerds\CoreBundle\Model\NewsQuery;
use MVNerds\CoreBundle\Model\NewsPeer;
use MVNerds\CoreBundle\Model\NewsCategoryQuery;

class NewsManager
{
	const NB_RELATED_NEWS  = 3;
	
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
			->joinWith('NewsCategory')
			->joinWith('User', \Criteria::LEFT_JOIN)
			->where(NewsPeer::STATUS . ' LIKE ?', NewsPeer::STATUS_PUBLIC)
		->find();
	}
	
	public function findAllNotPrivate()
	{
		return NewsQuery::create()
			->orderByCreateTime('desc')
			->joinWith('NewsCategory')
			->joinWith('User', \Criteria::LEFT_JOIN)
			->where(NewsPeer::STATUS . ' NOT LIKE ?', NewsPeer::STATUS_PRIVATE)
		->find();
	}
	
	public function findAll()
	{
		return NewsQuery::create()
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
			->limit(10)
		->find();
		
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
			->limit(10)
		->find();
		
		if (null === $news)
		{
			throw new InvalidArgumentException('No news found !');
		}

		return $news;
	}	
	
	public function findAllAjax($onlyPublic = true, $limitStart = 0, $limitLength = 2, $orderArr = array('Create_Time' => 'desc'), $whereArr = array())
	{
		$newsQuery = NewsQuery::create()
			->offset($limitStart)
			->limit($limitLength)
			->joinWith('User', \Criteria::LEFT_JOIN)
			->joinWith('NewsCategory', \Criteria::LEFT_JOIN);
		
		if ($onlyPublic) {
			$newsQuery->add(NewsPeer::STATUS, NewsPeer::STATUS_PUBLIC);
		} else {
			$newsQuery->add(NewsPeer::STATUS, NewsPeer::STATUS_PRIVATE, \Criteria::NOT_LIKE);
		}
		
		foreach($orderArr as $orderCol => $orderDir)
		{
			switch ($orderDir) {
				case 'asc':
					$newsQuery->addAscendingOrderByColumn($orderCol);
					break;
				case 'desc':
					$newsQuery->addDescendingOrderByColumn($orderCol);
					break;
				default:
					throw new PropelException('ModelCriteria::orderBy() only accepts Criteria::ASC or Criteria::DESC as argument');
			}
		}
		foreach($whereArr as $whereCol => $whereVal)
		{
			$newsQuery->add($whereCol, '%' . $whereVal . '%', \Criteria::LIKE);
		}
		
		$news = $newsQuery->find();
		
		if (null === $news)
		{
			throw new InvalidArgumentException('No news found !');
		}
		
		return $news;
	}
	
	public function countAll($onlyPublic = true)
	{
		if ($onlyPublic) {
			$newsCount = NewsQuery::create()->add(NewsPeer::STATUS, NewsPeer::STATUS_PUBLIC)->count();
		} else {
			$newsCount = NewsQuery::create()->add(NewsPeer::STATUS, NewsPeer::STATUS_PRIVATE, \Criteria::NOT_LIKE)->count();	
		}
		
		return $newsCount;
	}
	
	public function countAllAjax($onlyPublic = true, $whereArr = array())
	{
		$newsQuery = NewsQuery::create()
			->joinWith('User', \Criteria::LEFT_JOIN)
			->joinWith('NewsCategory', \Criteria::LEFT_JOIN);
	
		if ($onlyPublic) {
			$newsQuery->add(NewsPeer::STATUS, NewsPeer::STATUS_PUBLIC);
		} else {
			$newsQuery->add(NewsPeer::STATUS, NewsPeer::STATUS_PRIVATE, \Criteria::NOT_LIKE);
		}
		
		foreach($whereArr as $whereCol => $whereVal)
		{
			$newsQuery->add($whereCol, '%' . $whereVal . '%', \Criteria::LIKE);
		}
		
		return $newsQuery->count();
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
	
	public function findAllNewsCategories()
	{
		return NewsCategoryQuery::create()
		->find();
	}
	
	/**
	 * Récupère les derniers highlights non privés
	 */
	public function findRelatedNews(News $news)
	{
		$newsCollection = NewsQuery::create()
			->joinWith('User')
			->joinWith('NewsCategory')
			->add(NewsPeer::STATUS, 2) // Correspond à PUBLIC
			->add(NewsPeer::NEWS_CATEGORY_ID, $news->getNewsCategoryId())
			->add(NewsPeer::ID, $news->getId(), \Criteria::NOT_EQUAL)
			->orderByCreateTime(\Criteria::DESC)
			->limit(self::NB_RELATED_NEWS)
		->find();

		return $newsCollection;
	}	
}
