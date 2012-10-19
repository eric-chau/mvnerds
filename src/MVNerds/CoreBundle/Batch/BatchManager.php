<?php

namespace MVNerds\CoreBundle\Batch;

use Symfony\Component\HttpFoundation\Session\Session;
use MVNerds\CoreBundle\Model\ItemBuild;
/**
 * Permet de gérer la création des fichiers .bat
 */
class BatchManager
{
	private $userLocale;
	private $itemBuildsPath;
	
	public function __construct($itemBuildsPath)
	{
		$this->itemBuildsPath = $itemBuildsPath;
	}
	
	/**
	 * Permet de créer un fichier bat qui va créer les fichier d items recommandés pour chaque champion associés
	 * à l'ItemBuild fourni en parametre
	 * 
	 * @param ItemBuild $itemBuild
	 * @param string $locale
	 */
	public function createRecItemBuilder(ItemBuild $itemBuild, $path = null, $locale = null)
	{
		//Les répertoires les plus courants ou se trouve league of legends
		$paths = array(
			"C:/Program Files/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Program Files/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Program Files (x86)/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Program Files (x86)/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/"
		);
		
		//Si la locale n est pas définie lors de l appel on prend la locale de la session
		if (null === $locale)
		{
			$locale = $this->userLocale;
		}
		
		//Si le chemin du repertoire de riot est défini par l'utilisateur on l ajoute en fin de la liste des repertoires
		if (null != $path)
		{
			$paths[] = $path;
		}
		
		$batFile = fopen($this->itemBuildsPath, 'w');
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}

?>
