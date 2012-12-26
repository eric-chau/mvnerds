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
	private $itemBuildsModelPath;
	
	public function __construct($itemBuildsPath, $itemBuildsModelPath)
	{
		$this->itemBuildsPath = $itemBuildsPath;
		$this->itemBuildsModelPath = $itemBuildsModelPath;
	}
	
	/**
	 * Permet de créer un fichier bat qui va créer les fichier d items recommandés pour chaque champion associés
	 * à l'ItemBuild fourni en parametre
	 * 
	 * @param ItemBuild $itemBuild
	 */
	public function createRecItemBuilder(ItemBuild $itemBuild, $path = null)
	{
		$batchReleaseFinder = file_get_contents($this->itemBuildsModelPath . 'mvnerds.batch.release_finder.txt');
		//Préparation des headers en fonction de la locale
		if($this->userLocale == 'en')
		{
			$batchHeader = file_get_contents($this->itemBuildsModelPath . 'mvnerds.batch.header_en.txt');
			$tmpHeader= "echo This item build : \n";
		}
		else
		{
			$batchHeader = file_get_contents($this->itemBuildsModelPath . 'mvnerds.batch.header.txt');
			$tmpHeader= "echo Le build suivant : \n";
		}
		
		//Les répertoires les plus courants ou se trouve league of legends
		$paths = array(
			"C:/Program Files/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Program Files/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Program Files/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Program Files (x86)/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/",
			"C:/Program Files (x86)/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/"
		);
		
		//Si le chemin du repertoire de riot est défini par l'utilisateur on l ajoute en fin de la liste des repertoires
		if (null != $path)
		{
			if (strrpos($path, '/') != strlen($path) || strrpos($path, '\\') != strlen($path))
			{
				$path .= '/';
			}
			$paths[] = $path . 'League of Legends/RADS/solutions/lol_game_client_sln/releases/';
		}
		
		//Nomage du fichier bat
		$batFileName = $this->itemBuildsPath . $itemBuild->getSlug() . '.bat';
		//Ouverture du fichier en écriture
		$batFile = fopen($batFileName, 'w');
		
		
		//Ecriture des chemins d acces au dossier de league of legends
		foreach ($paths as $path)
		{
			fwrite($batFile, 'cd /d "'.$path."\"\n");
		}
		
		//Ecriture de l operation permettant de retrouver le dernier repertoire de release de LoL
		fwrite($batFile, $batchReleaseFinder);
		
		//Récupération des championItemBuilds
		$championItemBuilds = $itemBuild->getChampionItemBuildsJoinChampion();
		if ($championItemBuilds->count() <= 0)
		{
			$championItemBuilds = $itemBuild->getChampionItemBuilds();
		}
		
		//Récupération du mode de jeu du build
		$gameMode = $itemBuild->getGameMode()->getLabel();
		
		//Récupération du nom de fichier json en fonction du mode de jeu
		$fileName='0_RecItems_MVNerds';
		if ($gameMode == 'dominion') {
			$jsonGameMode = 'ODIN';
			$jsonMap = '8';
			$fileName .= '_Dominion.json';
		} elseif ($gameMode == 'aram') {
			$jsonGameMode = 'ARAM';
			$jsonMap = '3';
			$fileName .= '_ARAM.json';
		} elseif ($gameMode == 'twisted-treeline') {
			$jsonGameMode = 'CLASSIC';
			$jsonMap = '10';
			$fileName .= '_TwistedTreeline.json';
		} else {
			$jsonGameMode = 'CLASSIC';
			$jsonMap = '1';
			$fileName .= '_SummonerRift.json';
		}
		
		//Parcours de tous les blocks
		$batBlocksContent = '';
		$itemBuildBlocks = $itemBuild->getItemBuildBlocks();
		foreach ($itemBuildBlocks as $itemBuildBlock)
		{
			/* @var $itemBuildBlock \MVNerds\CoreBundle\Model\ItemBuildBlock */

			$type = $itemBuildBlock->getType();

			$tmpHeader .= "echo     " . $type . "\n";

			$batBlocksContent .= "echo \t\t { >> ". $fileName."\n";
			$batBlocksContent .= "echo \t\t\t \"type\":\"". $type ."\", >> ".$fileName."\n";
			$batBlocksContent .= "echo \t\t\t \"items\":[ >> ". $fileName."\n";

			$itemBuildBlockItems = $itemBuildBlock->getItemBuildBlockItems();
			foreach ($itemBuildBlockItems as $itemBuildBlockItem)
			{
				/* @var $itemBuildBlockItem \MVNerds\CoreBundle\Model\ItemBuildBlockItem */

				$item = $itemBuildBlockItem->getItem();
				$count = $itemBuildBlockItem->getCount();

				$tmpHeader .= "echo         " . $item->setLocale('en')->getName() . "\n";

				$batBlocksContent .= "echo \t\t\t\t{ >> ".$fileName."\n";
				$batBlocksContent .= "echo \t\t\t\t\t \"id\":\"".$item->getRiotCode()."\", >> ".$fileName."\n";
				$batBlocksContent .= "echo \t\t\t\t\t \"count\":".$count." >> ".$fileName."\n";

				if ($itemBuildBlockItems->isLast()) {
					$batBlocksContent .= "echo \t\t\t\t} >> ".$fileName."\n";
				} else {
					$batBlocksContent .= "echo \t\t\t\t}, >> ".$fileName."\n";
				}
			}

			$batBlocksContent .= "echo \t\t\t] >> ".$fileName."\n";

			if ($itemBuildBlocks->isLast()) {
				$batBlocksContent .= "echo \t\t } >> ".$fileName."\n";
			} else {
				$batBlocksContent .= "echo \t\t }, >> ".$fileName."\n";
			}
		}
		
		//Parcours de tous les championItemBuild
		$championHeader = '';
		foreach ($championItemBuilds as $championItemBuild)
		{
			/* @var $championItemBuild \MVNerds\CoreBundle\Model\ChampionItemBuild */
			
			//Récupération du nom de champion
			$champName = $championItemBuild->getChampion()->setLocale('en')->getName();
			
			$championHeader .= "echo \t".$champName."\n";
			
			$escapedChampName = preg_replace(array("/[ .']/"), array('', '', ''), $champName);
			if ($escapedChampName == 'Wukong')
			{
				$escapedChampName = 'MonkeyKing';
			}
			
			fwrite($batFile, 'md "'.$escapedChampName."\"\n");
			fwrite($batFile, 'cd "'.$escapedChampName."\"\n");
			fwrite($batFile, "md \"Recommended\"\n");
			fwrite($batFile, "cd \"Recommended\"\n");
			fwrite($batFile, 'echo { > '.$fileName."\n");		
			fwrite($batFile, "echo \t\"champion\":\"". $escapedChampName ."\", >> ".$fileName."\n");		
			fwrite($batFile, "echo \t\"title\":\"default\", >> ".$fileName."\n");	
			fwrite($batFile, "echo \t\"type\":\"mvnerds\", >> ".$fileName."\n");
			fwrite($batFile, "echo \t\"map\":\"". $jsonMap ."\", >> ".$fileName."\n");
			fwrite($batFile, "echo \t\"mode\":\"". $jsonGameMode ."\", >> ".$fileName."\n");
			fwrite($batFile, "echo \t\"priority\":\"true\", >> ".$fileName."\n");
			fwrite($batFile, "echo \t\"blocks\":[ >> ".$fileName."\n");
						
			//BLOCKS
			fwrite($batFile, $batBlocksContent);
			
			fwrite($batFile, "echo \t] >> ".$fileName."\n");
			fwrite($batFile, "echo } >> ".$fileName."\n");
			fwrite($batFile, "\n\n");	
			fwrite($batFile, 'cd "../../"'."\n\n");
		}
		
		if($this->userLocale == 'en')
		{
			fwrite($batFile, "Cls\necho Files have been created, see you soon on mvnerds.com\n");
		}
		else
		{
			fwrite($batFile, "Cls\necho Les fichiers ont bien ete crees, a bientot sur mvnerds.com\n");
		}
		fwrite($batFile, 'pause');
		
		fclose($batFile);
		
		if($this->userLocale == 'en')
		{
			$tmpHeader.="echo will be affected to those champions : \n";
		}
		else
		{
			$tmpHeader.="echo va etre affecte aux champions suivants : \n";
		}
		
		$tmpHeader.=$championHeader;
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
