<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseNews;

/**
 * Skeleton subclass for representing a row from the 'news' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class News extends BaseNews {

	public function getImage()
	{
		return '';
	}
	
	/**
	 * ATTENTION L'EXTENSION PHP_FILEINFO DOIT ETRE
	 * ACTIVEE POUR POUVOIR UTILISER LA METHODE 
	 * GUESS_EXTENTION
	 */
	public function setImage($v)
	{
		if ($v != null) {
			//On récupère l extension de l'image
			$extension = $v->guessExtension();
			if (!$extension) {
				$extension = 'bin';
			}			
			//on génère un nom aléatoire jusqu'a en trouver un qui n'existe pas encore
			do {
				$imageName = rand(1, 99999) . '.' . $extension;
			} while (file_exists(__DIR__ . '/../../../../web/medias/images/news/' . $imageName));

			//On crée une copie de l'image en local
			$destination = __DIR__ . '/../../../../web/medias/images/news/';
			$v->move( $destination ,$imageName);

			//Création de la miniature
			$source = null;
			if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'jpe' || $extension == 'jfif') {
				$source = imagecreatefromjpeg($destination . $imageName);
				$miniature = $this->generateResampled($source, $imageName, $extension, $destination);
				imagejpeg($miniature, $destination . "mini_" . $imageName);
			} elseif ($extension == 'gif') {
				$source = imagecreatefromgif($destination . $imageName);
				$miniature = $this->generateResampled($source, $imageName, $extension, $destination);
				imagegif($miniature, $destination . "mini_" . $imageName);
			} elseif ($extension == 'png') {
				$source = imagecreatefrompng($destination . $imageName);
				$miniature = $this->generateResampled($source, $imageName, $extension, $destination);
				imagepng($miniature, $destination . "mini_" . $imageName);
			}
			
			if (($oldImageName = $this->getImageName())) {
				if (file_exists($destination . $oldImageName)) {
					unlink($destination . $oldImageName);
				}
				if (file_exists($destination . 'mini_' . $oldImageName)) {
					unlink($destination . 'mini_' . $oldImageName);
				}
			}
			
			//On set le champ en BDD pour l'image
			$this->setImageName($imageName);
		}
	}
	
	private function generateResampled($source)
	{
		$largeur_source = imagesx($source);
		$hauteur_source = imagesy($source);

		$maxHeight = 160;
		$maxWidth = 300;
		
		$x = $maxWidth;
		$y = $maxWidth * $hauteur_source / $largeur_source;
		if ($y > $maxHeight) {
			$y = $maxHeight;
			$x = $largeur_source * $maxHeight / $hauteur_source;
		}
		
		// On crée la miniature vide
		$miniature = imagecreatetruecolor($x, $y); 
		$largeur_miniature = imagesx($miniature);
		$hauteur_miniature = imagesy($miniature);

		// On crée la miniature
		imagecopyresampled($miniature, $source, 0, 0, 0, 0, $largeur_miniature, $hauteur_miniature, $largeur_source, $hauteur_source);
		
		return $miniature;
	}
} // News
