<?php

namespace MVNerds\CoreBundle\Video;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use \Criteria;
use \PropelException;

use MVNerds\CoreBundle\Model\VideoQuery;
use MVNerds\CoreBundle\Model\VideoPeer;
use MVNerds\CoreBundle\Model\VideoCategoryQuery;
use MVNerds\CoreBundle\Model\VideoCategoryPeer;

class VideoManager
{	
	/**
	 * @param type $slug
	 * @return \MVNerds\CoreBundle\Model\Video
	 * @throws InvalidArgumentException
	 */
	public function findBySlug($slug)
	{
		$video = VideoQuery::create()
			->add(VideoPeer::SLUG, $slug)
		->findOne();

		if (null === $video)
		{
			throw new InvalidArgumentException('No video with slug:' . $slug . '!');
		}

		return $video;
	}
	
	/**
	 * @param int $id
	 * @return \MVNerds\CoreBundle\Model\Video
	 * @throws InvalidArgumentException
	 */
	public function findById($id)
	{
		$video = VideoQuery::create()
			->add(VideoPeer::ID, $id)
		->findOne();

		if (null === $video)
		{
			throw new InvalidArgumentException('No video with id:' . $id . '!');
		}

		return $video;
	}
	
	public function findAllActive() 
	{
		return VideoQuery::create()
			->joinWith('VideoCategory')
			->joinWith('User')
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
		->find();
	}
	
	public function findByUser($user) 
	{
		return VideoQuery::create()
			->joinWith('VideoCategory')
			->joinWith('User')
			->add(VideoPeer::USER_ID, $user->getId())
		->find();
	}
	
	public function findRelatedVideos($video)
	{
		return VideoQuery::create()
			->joinWith('VideoCategory')
			->joinWith('User')
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
			->add(VideoPeer::VIDEO_CATEGORY_ID, $video->getVideoCategoryId())
			->add(VideoPeer::ID, $video->getId(), \Criteria::NOT_EQUAL)
			->limit(5)
		->find();
	}
	
	/**
	 * Permet de formater une url brut (http://www.youtube.com/watch?v=....) en url embed (http://www.youtube.com/v/...)
	 * @param string $link l url a formater
	 * @return l url formatée si tout c est bien passé ou false sinon
	 */
	public function formatVideoLink($link)
	{
		//On supprime les chaine "http://" et "https://" si elles existent puis on supprime la chaine "www" si elle existe
		$link = str_replace('http://', '', $link);
		$link = str_replace('https://', '', $link);
		$escapedLink = preg_replace('/^www\./', '', $link);
		
		$formatedLink = false;
		
		// On vérifie si la vidéo provient de youtube
		if (preg_match('/(youtube\.com\/watch\?.*v=)/', $escapedLink) !== false || strpos($escapedLink, 'youtu.be/') !== false || strpos($escapedLink, 'youtube.com/v/') !== false) { 
			$embed = 'http://www.youtube.com/v/';
			
			if (strpos($escapedLink, 'youtube.com/v/') !== false) {
				$replaced = str_replace('youtube.com/v/', '', $escapedLink);
				preg_match('/(start=[0-9]+)/', $replaced, $time);
				$exploded = array($replaced);
			} elseif (strpos($escapedLink, 'youtu.be/') !== false) {
				$replaced = str_replace('youtu.be/', '', $escapedLink);
				preg_match('/(t=([0-9]+[hms])+)/', $replaced, $time);
				$exploded = explode('?', $replaced);
			} elseif (preg_match('/(youtube\.com\/watch\?.*v=)/', $escapedLink) !== false) {
				$replaced = preg_replace('/(youtube\.com\/watch\?.*v=)/', '', $escapedLink);
				preg_match('/(t=([0-9]+[hms])+)/', $replaced, $time);
				$exploded = preg_split('/[&#]/', $replaced);
			}
			
			$finalTime = '';
			$finalSeconds = 0;
			if ($time != null && count($time) > 1 ) {
				if (strpos($time[0], 'start=') !== false) {
					preg_match('/([0-9]+)/', $time[0], $seconds);
				} elseif (strpos($time[0], 't=') !== false) {
					preg_match('/([0-9]+h)/', $time[0], $hours);
					preg_match('/([0-9]+m)/', $time[0], $minutes);
					preg_match('/([0-9]+s)/', $time[0], $seconds);
				}
				
				if(isset($hours) && $hours != null && count($hours) > 0 && $hours[0] != '') {
					$hours = str_replace('h', '', $hours[0]);
					if (is_numeric($hours)) {
						$finalSeconds += $hours*60*60;
					}
				}
				if(isset($minutes) && $minutes != null && count($minutes) > 0 && $minutes[0] != '') {
					$minutes = str_replace('m', '', $minutes[0]);
					if (is_numeric($minutes)) {
						$finalSeconds += $minutes*60;
					}
				}
				if(isset($seconds) && $seconds != null && count($seconds) > 0 && $seconds[0] != '') {
					$seconds = str_replace('s', '', $seconds[0]);
					if (is_numeric($seconds)) {
						$finalSeconds += $seconds;
					}
				}
				
				if ($finalSeconds > 0) {
					$finalTime = '&start=' . $finalSeconds;
				}
			}
			
			if ($exploded != null && count($exploded) > 0 && $exploded[0] != '') {
				$formatedLink = $embed . $exploded[0] . $finalTime;
			}
		} 
		// Sinon on vérifie si elle provient de dailymotion
		elseif (strpos($escapedLink,'dailymotion.com') !== false || strpos($escapedLink,'dailymotion.com/embed/video/') !== false) {
			$embed = 'http://www.dailymotion.com/embed/video/';
			
			if (strpos($escapedLink, 'dailymotion.com/embed/video/') !== false) {
				$exploded = array(str_replace('dailymotion.com/embed/video/', '', $escapedLink));
			} elseif (strpos($escapedLink, '/video/') !== false) {
				$exploded = explode('_', str_replace('dailymotion.com/video/', '', $escapedLink));
			} elseif (strpos($escapedLink,'#video=') !== false) {
				$exploded = array(preg_replace('/dailymotion\.com\/.*#video=/', '', $escapedLink));
			}
			
			if ($exploded != null && count($exploded) > 0 && $exploded[0] != '') {
				$formatedLink = $embed . $exploded[0];
			}
		}
		
		return $formatedLink;
	}
	
	public function findAllVideoCatgories() 
	{
		return VideoCategoryQuery::create()
		->find();
	}
	
	public function findVideoCategoryById($id)
	{
		$videoCategory = VideoCategoryQuery::create()
			->add(VideoCategoryPeer::ID, $id)
		->findOne();

		if (null === $videoCategory)
		{
			throw new InvalidArgumentException('No video catgory with id:' . $id . '!');
		}

		return $videoCategory;
	}
	
	public function findAllActiveAjax($limitStart = 0, $limitLength = 2, $orderArr = array('Create_Time' => 'desc'), $whereArr = array())
	{
		$videoQuery = VideoQuery::create()
			->offset($limitStart)
			->limit($limitLength)
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
			->joinWith('User', \Criteria::LEFT_JOIN)
			->joinWith('VideoCategory', \Criteria::LEFT_JOIN);
		
		foreach($orderArr as $orderCol => $orderDir)
		{
			switch ($orderDir) {
				case 'asc':
					$videoQuery->addAscendingOrderByColumn($orderCol);
					break;
				case 'desc':
					$videoQuery->addDescendingOrderByColumn($orderCol);
					break;
				default:
					throw new PropelException('ModelCriteria::orderBy() only accepts Criteria::ASC or Criteria::DESC as argument');
			}
		}
		foreach($whereArr as $whereCol => $whereVal)
		{
			$videoQuery->add($whereCol, '%' . $whereVal . '%', \Criteria::LIKE);
		}
		
		$videos = $videoQuery->find();
		
		if (null === $videos)
		{
			throw new InvalidArgumentException('No video found !');
		}
		
		return $videos;
	}
	
	public function countAllActive()
	{
		$videosCount = VideoQuery::create()
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
		->count();
		
		return $videosCount;
	}
	
	public function countAllActiveAjax($whereArr = array())
	{
		$videoQuery = VideoQuery::create()
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
			->joinWith('User', \Criteria::LEFT_JOIN)
			->joinWith('VideoCategory', \Criteria::LEFT_JOIN);
	
		foreach($whereArr as $whereCol => $whereVal)
		{
			$videoQuery->add($whereCol, '%' . $whereVal . '%', \Criteria::LIKE);
		}
		
		return $videoQuery->count();
	}
	
	/**
	 * Récupère les vidéos les plus récentes
	 */
	public function findNewestVideos()
	{
		$videos = VideoQuery::create()
			->joinWith('User')
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
			->orderById(\Criteria::DESC)
			->limit(5)
		->find();
				
		if (null === $videos) {
			throw new InvalidArgumentException('No item build found !');
		}
		return $videos;
	}	
	
	/**
	 * Récupère les vid"éos les plus consultées
	 */
	public function findMostViewedVideos()
	{
		$videos = VideoQuery::create()
			->joinWith('User')
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
			->orderByView(\Criteria::DESC)
			->limit(5)
		->find();
		
		if (null === $videos) {
			throw new InvalidArgumentException('No item build found !');
		}

		return $videos;
	}
}
