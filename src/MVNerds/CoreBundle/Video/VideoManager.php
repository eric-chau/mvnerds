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
		if (strpos($link, 'youtube.com') === false && strpos($link,'youtu.be') === false && strpos($link,'dailymotion.com') === false) {
			return false;
		}
		return true;
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
}
