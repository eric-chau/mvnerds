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
		//On supprime la chaine "http://" si elle existe puis on supprime la chaine "www" si elle existe
		$videoLink = preg_replace('/^www\./', '', str_replace('http://', '', $this->getLink()));
		
		if (strpos($videoLink, 'youtube.com/watch?v=') !== false || strpos($videoLink, 'youtu.be/') !== false) { 
			$embed = 'http://img.youtube.com/vi/';
			
			if (strpos($videoLink, 'youtube.com') !== false) {
				$exploded = explode('&', str_replace('youtube.com/watch?v=', '', $videoLink));
			} else {
				$exploded = explode('?', str_replace('youtu.be/', '', $videoLink));
			}
			
			return $embed . $exploded[0] . '/0.jpg';
			
		} elseif (strpos($videoLink,'dailymotion.com') !== false) {
			$embed = 'http://www.dailymotion.com/thumbnail/video/';
			
			if (strpos($videoLink, '/video/') !== false) {
				$exploded = explode('_', str_replace('dailymotion.com/video/', '', $videoLink));
			
				return $embed . $exploded[0];
				
			} elseif (strpos($videoLink,'#video=') !== false) {
				$embed .= preg_replace('/dailymotion\.com\/.*#video=/', '', $videoLink);
				
				return $embed;
			}
		}
		
		return '';
	}
} // Video
