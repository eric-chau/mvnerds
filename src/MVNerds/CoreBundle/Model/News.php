<?php

namespace MVNerds\CoreBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use MVNerds\CoreBundle\Comment\IComment;
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
	public function setImage(UploadedFile $v)
	{
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
		$v->move( __DIR__ . '/../../../../web/medias/images/news/' ,$imageName);
		
		//On set le champ en BDD pour l'image
		$this->setImageName('/medias/images/news/' . $imageName);
	}
} // News
