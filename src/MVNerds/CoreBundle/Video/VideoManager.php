<?php

namespace MVNerds\CoreBundle\Video;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\VideoQuery;
use MVNerds\CoreBundle\Model\VideoPeer;

class VideoManager
{	
	public function findBySlug($slug)
	{
		$video = VideoQuery::create()
			->add(VideoPeer::SLUG, $slug)
		->findOne();

		if (null === $video)
		{
			throw new InvalidArgumentException('No skin with slug:' . $slug . '!');
		}

		return $video;
	}
	
	public function isVideoLinkValid($link) 
	{
		if (strpos($link, 'youtube.com') >= 0 || strpos($link,'youtu.be') >= 0 || strpos($link,'dailymotion.com') >= 0) {
			return true;
		}
		return false;
	}
}
