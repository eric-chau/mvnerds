<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseVideo;


/**
 * Skeleton subclass for representing a row from the 'video' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Video extends BaseVideo {

	public function getThumbnailUrl()
	{
		if (strpos($this->getLink(), 'youtube.com') !== false) { 
			$link = 'http://img.youtube.com/vi/';
			$exploded = explode('&', str_replace('http://www.youtube.com/watch?v=', '', $this->getLink()));
			
			return $link . $exploded[0] . '/0.jpg';
		} elseif (strpos($this->getLink(), 'youtu.be') !== false) {
			$link = 'http://img.youtube.com/vi/';
			$exploded = explode('&', str_replace('http://youtu.be/', '', $this->getLink()));
			
			return $link . $exploded[0] . '/0.jpg';
		} elseif (strpos($this->getLink(),'dailymotion.com') !== false) {
			$link = 'http://www.dailymotion.com/thumbnail/video/';
			if (strpos($this->getLink(),'/video/') !== false) {
				$exploded = explode('_', str_replace('http://www.dailymotion.com/video/', '', $this->getLink()));
			
				return $link . $exploded[0];
			} elseif (strpos($this->getLink(),'#video=') !== false) {
				$exploded = preg_replace('/http:\/\/www\.dailymotion\.com\/.*#video=/', '', $this->getLink());
				
				return $link . $exploded;
			} else {
				return '';
			}
		} else {
			return '';
		}
	}
} // Video
