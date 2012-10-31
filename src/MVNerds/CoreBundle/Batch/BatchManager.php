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
		$batchReleaseFinder = file_get_contents($this->itemBuildsPath . 'mvnerds.batch.release_finder.txt');
		$batchHeader = file_get_contents($this->itemBuildsPath . 'mvnerds.batch.header.txt');
		
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
		$batFileName = $this->itemBuildsPath . $itemBuild->getSlug() . '.bat';
		$batFile = fopen($batFileName, 'w');
		
		
		//Ecriture des chemins d acces au dossier de league of legends
		foreach ($paths as $path)
		{
			fwrite($batFile, 'cd "'.$path."\"\n");
		}
		
		//Ecriture de l operation permettant de retrouver le dernier repertoire de release de LoL
		fwrite($batFile, $batchReleaseFinder);
		
		//Parcours de chaque champion
		$championItemBuilds = $itemBuild->getChampionItemBuildsJoinChampion();
		if ($championItemBuilds->count() <= 0)
		{
			$championItemBuilds = $itemBuild->getChampionItemBuilds();
		}
		
		$champNames=array();
		$itemNames = array(
			$itemBuild->getItemRelatedByItem1Id()->setLocale('en')->getName(),
			$itemBuild->getItemRelatedByItem2Id()->setLocale('en')->getName(),
			$itemBuild->getItemRelatedByItem3Id()->setLocale('en')->getName(),
			$itemBuild->getItemRelatedByItem4Id()->setLocale('en')->getName(),
			$itemBuild->getItemRelatedByItem5Id()->setLocale('en')->getName(),
			$itemBuild->getItemRelatedByItem6Id()->setLocale('en')->getName()
		);
		
		foreach ($championItemBuilds as $championItemBuild)
		{
			/* @var $championItemBuild \MVNerds\CoreBundle\Model\ChampionItemBuild */
			
			//Récupération du nom de champion
			$champName = $championItemBuild->getChampion()->setLocale('en')->getName();
			$champNames[] = $champName;
			$escapedChampName = preg_replace(array("/[ .']/"), array('', '', ''), $champName);
			if ($escapedChampName == 'Wukong')
			{
				$escapedChampName = 'MonkeyKing';
			}
			
			//Récupération du mode de jeu
			$fileName='RecItems';	
			$gameMode = $championItemBuild->getGameMode()->getLabel();
			if ($gameMode == 'dominion')
			{
				$fileName .= 'ODIN.ini';
			}
			elseif ($gameMode == 'aram')
			{
				$fileName .= 'ARAM.ini';
			}
			elseif ($gameMode == 'twisted-treeline')
			{
				$fileName .= 'CLASSICMap10.ini';
			}
			else
			{
				$fileName .= 'CLASSIC.ini';
			}
			
			fwrite($batFile, 'md "'.$escapedChampName."\"\n");
			fwrite($batFile, 'cd "'.$escapedChampName."\"\n");
			fwrite($batFile, 'echo [ItemSet1] > '.$fileName."\n");		
			fwrite($batFile, 'echo SetName=Set1 >> '.$fileName."\n");		
			fwrite($batFile, 'echo RecItem1='. $itemBuild->getItemRelatedByItem1Id()->getRiotCode() .' >> '.$fileName."\n");	
			fwrite($batFile, 'echo RecItem2='. $itemBuild->getItemRelatedByItem2Id()->getRiotCode() .' >> '.$fileName."\n");	
			fwrite($batFile, 'echo RecItem3='. $itemBuild->getItemRelatedByItem3Id()->getRiotCode() .' >> '.$fileName."\n");	
			fwrite($batFile, 'echo RecItem4='. $itemBuild->getItemRelatedByItem4Id()->getRiotCode() .' >> '.$fileName."\n");	
			fwrite($batFile, 'echo RecItem5='. $itemBuild->getItemRelatedByItem5Id()->getRiotCode() .' >> '.$fileName."\n");	
			fwrite($batFile, 'echo RecItem6='. $itemBuild->getItemRelatedByItem6Id()->getRiotCode() .' >> '.$fileName."\n");	
			fwrite($batFile, "\n\n");	
			fwrite($batFile, 'cd "../"'."\n\n");
		}
		
		fwrite($batFile, "Cls\necho Les fichiers ont bien ete crees, a bientot sur mvnerds.com\n");
		fwrite($batFile, 'pause');
		
		fclose($batFile);
		
		$tmpHeader= "echo Le build suivant : \n";
		for($i = 1; $i <= count($itemNames); $i++)
		{
			$tmpHeader .= "echo \tItem $i : ".$itemNames[$i-1]."\n";
		}
		$tmpHeader.="echo va etre affecte aux champions suivants : \n";
		foreach($champNames as $championName)
		{
			$tmpHeader .= "echo \t".$championName."\n";
		}
		$tmpHeader.="pause\n";
		
		$batchHeader = $batchHeader.$tmpHeader;
		
		file_put_contents($batFileName, $batchHeader.file_get_contents($batFileName));
		
		return $this->itemBuildsPath;
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}

?>
