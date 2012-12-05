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
	 * @param string $locale
	 */
	public function createRecItemBuilder(ItemBuild $itemBuild, $path = null, $locale = null)
	{
		$batchReleaseFinder = file_get_contents($this->itemBuildsModelPath . 'mvnerds.batch.release_finder.txt');
		if($this->userLocale == 'en')
		{
			$batchHeader = file_get_contents($this->itemBuildsModelPath . 'mvnerds.batch.header_en.txt');
		}
		else
		{
			$batchHeader = file_get_contents($this->itemBuildsModelPath . 'mvnerds.batch.header.txt');
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
		
		//Si la locale n est pas définie lors de l appel on prend la locale de la session
		if (null === $locale)
		{
			$locale = $this->userLocale;
		}
		
		//Si le chemin du repertoire de riot est défini par l'utilisateur on l ajoute en fin de la liste des repertoires
		if (null != $path)
		{
			if (strrpos($path, '/') != strlen($path) || strrpos($path, '\\') != strlen($path))
			{
				$path .= '/';
			}
			$paths[] = $path . 'League of Legends/RADS/solutions/lol_game_client_sln/releases/';
		}
		$batFileName = $this->itemBuildsPath . $itemBuild->getSlug() . '.bat';
		$batFile = fopen($batFileName, 'w');
		
		
		//Ecriture des chemins d acces au dossier de league of legends
		foreach ($paths as $path)
		{
			fwrite($batFile, 'cd /d "'.$path."\"\n");
		}
		
		//Ecriture de l operation permettant de retrouver le dernier repertoire de release de LoL
		fwrite($batFile, $batchReleaseFinder);
		
		
		$championItemBuilds = $itemBuild->getChampionItemBuildsJoinChampion();
		if ($championItemBuilds->count() <= 0)
		{
			$championItemBuilds = $itemBuild->getChampionItemBuilds();
		}
		
		$champNames=array();
		
		$itemBuildItemsCollection = $itemBuild->getItemBuildItemss();
		
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
			$fileName='0_RecItems_MVNerds';	
			$gameMode = $championItemBuild->getGameMode()->getLabel();
			
			$jsonGameMode = '';
			$jsonMap = '';
			if ($gameMode == 'dominion')
			{
				$jsonGameMode = 'ODIN';
				$jsonMap = '8';
				$fileName .= '_Dominion.json';
			}
			elseif ($gameMode == 'aram')
			{
				$jsonGameMode = 'ARAM';
				$jsonMap = '3';
				$fileName .= '_ARAM.json';
			}
			elseif ($gameMode == 'twisted-treeline')
			{
				$jsonGameMode = 'CLASSIC';
				$jsonMap = '10';
				$fileName .= '_TwistedTreeline.json';
			}
			else
			{
				$jsonGameMode = 'CLASSIC';
				$jsonMap = '1';
				$fileName .= '_SummonerRift.json';
			}
			
			$recommendedDir = 'Recommended';
			
			fwrite($batFile, 'md "'.$escapedChampName."\"\n");
			fwrite($batFile, 'cd "'.$escapedChampName."\"\n");
			fwrite($batFile, 'md "'.$recommendedDir."\"\n");
			fwrite($batFile, 'cd "'.$recommendedDir."\"\n");
			fwrite($batFile, 'echo { > '.$fileName."\n");		
			fwrite($batFile, "echo \t\"champion\":\"". $escapedChampName ."\", >> ".$fileName."\n");		
			fwrite($batFile, "echo \t\"title\":\"default\", >> ".$fileName."\n");	
			fwrite($batFile, "echo \t\"type\":\"mvnerds\", >> ".$fileName."\n");
			fwrite($batFile, "echo \t\"map\":\"". $jsonMap ."\", >> ".$fileName."\n");
			fwrite($batFile, "echo \t\"mode\":\"". $jsonGameMode ."\", >> ".$fileName."\n");
			fwrite($batFile, "echo \t\"priority\":\"true\", >> ".$fileName."\n");
			fwrite($batFile, "echo \t\"blocks\":[ >> ".$fileName."\n");
			
			$itemTab = array();
			$maxPosition = 0;
			foreach ($itemBuildItemsCollection as $itemBuildItems)
			{
				$item = $itemBuildItems->getItem();
				$type = $itemBuildItems->getType();
				$position = $itemBuildItems->getPosition();
				$count = $itemBuildItems->getCount();
				
				if (! isset($itemTab[$position]))
				{
					$itemTab[$position] = array('type' =>$type, 'items' => array(), 'position' => $position);
				}
				
				$stdItem = new \stdClass();
				$stdItem->code = $item->getRiotCode();
				$stdItem->count = $count;
				$stdItem->name = $item->getName();
				
				$itemTab[$position * 1]['items'][] = $stdItem;
				
				if ($position > $maxPosition)
				{
					$maxPosition = $position;
				}
			}
			ksort($itemTab);
			
			if($this->userLocale == 'en')
			{
				$tmpHeader= "echo This item build : \n";
			}
			else
			{
				$tmpHeader= "echo Le build suivant : \n";
			}
			
			foreach($itemTab as $itemBlock)
			{
				$blockName = $itemBlock['type'];
				
				$tmpHeader .= "echo     " .$blockName . "\n";
				
				fwrite($batFile, "echo \t\t { >> ".$fileName."\n");
				fwrite($batFile, "echo \t\t\t \"type\":\"". $blockName ."\", >> ".$fileName."\n");
				fwrite($batFile, "echo \t\t\t \"items\":[ >> ".$fileName."\n");
				
				$nbItems = count($itemBlock['items']);
				$k = 1;
				foreach($itemBlock['items'] as $item)
				{
					$tmpHeader .= "echo         " . $item->name . "\n";
					
					fwrite($batFile, "echo \t\t\t\t{ >> ".$fileName."\n");
					fwrite($batFile, "echo \t\t\t\t\t \"id\":\"".$item->code."\", >> ".$fileName."\n");
					fwrite($batFile, "echo \t\t\t\t\t \"count\":".$item->count." >> ".$fileName."\n");
					
					if ($k >= $nbItems)
					{
						fwrite($batFile, "echo \t\t\t\t} >> ".$fileName."\n");
					}
					else
					{
						fwrite($batFile, "echo \t\t\t\t}, >> ".$fileName."\n");
					}
					$k++;
				}
				
				fwrite($batFile, "echo \t\t\t] >> ".$fileName."\n");
				
				if ($itemTab[$maxPosition]['type'] == $blockName)
				{
					fwrite($batFile, "echo \t\t } >> ".$fileName."\n");					
				}
				else 
				{
					fwrite($batFile, "echo \t\t }, >> ".$fileName."\n");
				}
			}
			
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
