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

	//Permet de récupérer l'image d'aperçu de la vidéo à partir du lien de celle-ci
	public function getThumbnailUrl()
	{
		//On récupère le lien de la vidéo
		$videoLink = $this->getLink();
		
		//Si c'est un lien embed youtube
		if (strpos($videoLink, 'http://www.youtube.com/v/') !== false) {
			$embed = 'http://img.youtube.com/vi/';
			$videoId = str_replace('http://www.youtube.com/v/', '', $videoLink);
			
			return $embed . $videoId . '/0.jpg';
		} 
		// Sinon si c est un lien embed dailymotion
		elseif (strpos($videoLink, 'http://www.dailymotion.com/embed/video/') !== false) {
			$embed = 'http://www.dailymotion.com/thumbnail/video/';
			$videoId = str_replace('http://www.dailymotion.com/embed/video/', '', $videoLink);
			
			return $embed . $videoId;
		}
		
		return '';
	}
} // Video
