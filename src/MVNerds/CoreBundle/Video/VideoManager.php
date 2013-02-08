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
	
	public function isVideoLinkValid($link) 
	{
		
		//On supprime la chaine "http://" si elle existe puis on supprime la chaine "www" si elle existe
		$videoLink = preg_replace('/^www\./', '', str_replace('http://', '', $link));
		
		if (strpos($videoLink, 'youtube.com/watch?v=') !== false || strpos($videoLink, 'youtu.be/') !== false) {
			
			if (strpos($videoLink, 'youtube.com') !== false) {
				$exploded = explode('&', str_replace('youtube.com/watch?v=', '', $videoLink));
			} else {
				$exploded = explode('?', str_replace('youtu.be/', '', $videoLink));
			}
			
			return $exploded[0] != '';
			
		} elseif (strpos($videoLink,'dailymotion.com') !== false) {
			
			if (strpos($videoLink, '/video/') !== false) {
				$exploded = explode('_', str_replace('dailymotion.com/video/', '', $videoLink));
			
				return $exploded[0] != '';
				
			} elseif (strpos($videoLink,'#video=') !== false) {
				$exploded .= preg_replace('/dailymotion\.com\/.*#video=/', '', $videoLink);
				
				return $exploded != '';
			}
		}
		
		return false;
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