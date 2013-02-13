<?php

namespace MVNerds\CoreBundle\Video;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\VideoQuery;
use MVNerds\CoreBundle\Model\VideoPeer;
use MVNerds\CoreBundle\Model\VideoCategoryQuery;
use MVNerds\CoreBundle\Model\VideoCategoryPeer;

class VideoManager
{	
	/**
	 * 
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
	
	public function findAllActive() 
	{
		return VideoQuery::create()
			->joinWith('VideoCategory')
			->joinWith('User')
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
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
		if (strpos($escapedLink, 'youtube.com/watch?v=') !== false || strpos($escapedLink, 'youtu.be/') !== false || strpos($escapedLink, 'youtube.com/v/') !== false) { 
			$embed = 'http://www.youtube.com/v/';
			
			if (strpos($escapedLink, 'youtube.com/v/') !== false) {
				$exploded = array(str_replace('youtube.com/v/', '', $escapedLink));
			} elseif (strpos($escapedLink, 'youtu.be/') !== false) {
				$exploded = explode('?', str_replace('youtu.be/', '', $escapedLink));
			} elseif (strpos($escapedLink, 'youtube.com') !== false) {
				$exploded = explode('&', str_replace('youtube.com/watch?v=', '', $escapedLink));
			}
			
			if ($exploded != null && count($exploded) > 0 && $exploded[0] != '') {
				$formatedLink = $embed . $exploded[0];
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
	
	public function findAllActiveAjax($limitStart = 0, $limitLength = 2, $orderArr = array('CreateTime' => 'desc'), $whereArr = array())
	{
		$videoQuery = VideoQuery::create()
			->offset($limitStart)
			->limit($limitLength)
			->add(VideoPeer::STATUS, VideoPeer::STATUS_ACTIVE)
			->joinWith('User', \Criteria::LEFT_JOIN)
			->joinWith('VideoCategory', \Criteria::LEFT_JOIN);
		
		foreach($orderArr as $orderCol => $orderDir)
		{
			$videoQuery->orderBy($orderCol, $orderDir);
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
}
