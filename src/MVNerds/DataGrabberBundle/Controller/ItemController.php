<?php

namespace MVNerds\DataGrabberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\Model\Item;
use MVNerds\DataGrabberBundle\Form\Type\ItemGrabberType;
use MVNerds\CoreBundle\Model\ItemGeneology;
use MVNerds\CoreBundle\Model\ItemPrimaryEffect;
use MVNerds\CoreBundle\Model\ItemSecondaryEffect;
use MVNerds\CoreBundle\Model\ItemSecondaryEffectPeer;
use MVNerds\CoreBundle\Model\ItemTag;

/**
 * @Route("/items")
 */
class ItemController extends Controller
{		
	/**
	 * Permet d'insérer dans la base des items et leur généalogie sans les stats ou les tags
	 * 
	 * @Route("/", name="DataGrabber_items_base")
	 */
	public function getBaseItemsAction()
	{
		include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');
				
		//Récupération de la liste des items
		$itemsList = file_get_html('file:///C:/Users/Haniki/Downloads/items_lol.html')->find('div#list_view table.champion_item');

		$startItem = 0;
		$nbItem = count($itemsList);
		
		//Création du flash manager
		/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');

		//Si la liste des items a bien été récupérée
		if ($itemsList)
		{		
			/* @var $itemManager \MVNerds\CoreBundle\Item\ItemManager */
			$itemManager = $this->get('mvnerds.item_manager');

			//On boucle sur chaque champion pour récupérer le lien associé
			for ($i = $startItem; $i < $startItem + $nbItem; $i++)
			{
				$item = new Item();
				$item->setStacks(1);
				$item->setSellValue(0);

				$description = $itemsList[$i]->find('td.description', 0);

				$itemHref = $description->find('a.lol_item',0)->href;
				$itemCode = substr(strstr($itemHref, '#'), 1);
				try{
					$itemManager->findByCode($itemCode);
					continue;
				}
				catch(\Exception $e){
				}
				$item->setRiotCode($itemCode);
				
				$name = $description->find('a.lol_item span',0)->plaintext;
				$item->setName($name);	

				$cost = $itemsList[$i]->find('td.cost span.big', 0);
				$itemCost = strstr($cost->innertext, '<br>', true);
				$item->setCost($itemCost);

				$itemManager->save($item);
			}
			//Une fois que l on a inséré tous les items avec leur codes on peut insérer leurs liens de parenté
			for ($i = $startItem; $i < $startItem + $nbItem; $i++)
			{
				$description = $itemsList[$i]->find('td.description', 0);
				$itemHref = $description->find('a.lol_item',0)->href;
				$itemCode = substr(strstr($itemHref, '#'), 1);
				
				if(($item = $itemManager->findByCode($itemCode)))
				{
					$itemGeneologies = $description->find('div.item_geneology');
					foreach ($itemGeneologies as $itemGeneology)
					{
						if (null == $itemGeneology->find('div.item_container.geneology_head'))
						{
							foreach ($itemGeneology->find('ul.minilist li a.lol_item') as $childItem)
							{
								$childItemHref = $childItem->href;
								$childItemCode = substr(strstr($childItemHref, '#'), 1);
								if(($childItem = $itemManager->findByCode($childItemCode)))
								{
									$dbItemGeneology = new ItemGeneology();
									$dbItemGeneology->setParentId($item->getId());
									$dbItemGeneology->setChildId($childItem->getId());
									$dbItemGeneology->save();
								}
							}
						}
					}
				}
			}
		}
		else
		{
			// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
			$flashManager->setErrorMessage('Erreur lors du grab des items de base');
			// On redirige l'utilisateur vers la liste des utilisateurs
			return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
		}

		// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
		$flashManager->setSuccessMessage('Les items de base ont bien été grabbés');
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
	}
	
	/**
	 * Permet de récupérer le reste des données de l item (stats tags)
	 * 
	 * A utiliser apres avoir insérer les objets de base avec l action getBaseItemsAction 
	 * sur un fichier provenant de la page des item du site officiel de LoL
	 * 
	 * @Route("/complete/{code}", name="DataGrabber_items_complete", defaults={"code"=null})
	 */
	public function getCompleteItemsAction($code)
	{
		$form = $this->createForm(new ItemGrabberType());
				
		$request = $this->getRequest();
		if ( $code == null && !$request->isMethod('POST'))
		{
			return $this->render('MVNerdsDataGrabberBundle:Item:index.html.twig', array(
				'form' => $form->createView()
			));
		}
		$form->bind($request);
		if ($code != null || $form->isValid())
		{
			include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');

			//Création du flash manager
			/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
			$flashManager = $this->get('mvnerds.flash_manager');

			$itemInfo = $form->getData();
			$startItem = $itemInfo['start_index'];
			$nbItem = $itemInfo['nb_items'];

			/* @var $primaryEffectManager \MVNerds\CoreBundle\PrimaryEffect\PrimaryEffectManager */
			$primaryEffectManager = $this->get('mvnerds.primary_effect_manager');

			$itemEffects = array(
				'armure'						=> $primaryEffectManager->findByLabel('armure'),
				'résistance magique'				=> $primaryEffectManager->findByLabel('résistance magique'),
				'PV'							=> $primaryEffectManager->findByLabel('PV'),
				'puissance'						=> $primaryEffectManager->findByLabel('puissance'),
				'régénération du mana toutes les 5 sec'	=> $primaryEffectManager->findByLabel('régénération du mana toutes les 5 sec'),
				'vitesse d\'attaque'					=> $primaryEffectManager->findByLabel('vitesse d\'attaque'),
				'dégâts d\'attaque'					=> $primaryEffectManager->findByLabel('dégâts d\'attaque'),
				'régénération des PV toutes les 5 sec'	=> $primaryEffectManager->findByLabel('régénération des PV toutes les 5 sec'),
				'mana'						=> $primaryEffectManager->findByLabel('mana'),
				'chances de coup critique'			=> $primaryEffectManager->findByLabel('chances de coup critique'),
				'vol de vie'						=> $primaryEffectManager->findByLabel('vol de vie'),
				'vitesse de déplacement'				=> $primaryEffectManager->findByLabel('vitesse de déplacement'),
				'puissance par niveau'				=> $primaryEffectManager->findByLabel('puissance par niveau'),
				'dégâts d\'attaque par niveau'			=> $primaryEffectManager->findByLabel('dégâts d\'attaque par niveau')
			);

			/* @var $tagManager \MVNerds\CoreBundle\Tag\TagManager */
			$tagManager = $this->get('mvnerds.tag_manager');

			//Tableau de conversion des tags
			$statConversion = array(
				'Santé'			=> $tagManager->findOneByLabel('santé', 'fr'),
				'Résistance magique'	=> $tagManager->findOneByLabel('résistance magique', 'fr'),
				'Régénération santé'	=> $tagManager->findOneByLabel('régénération santé', 'fr'),
				'Armure'			=> $tagManager->findOneByLabel('armure', 'fr'),
				'Dégâts'			=> $tagManager->findOneByLabel('dégâts', 'fr'),
				'Coup critique'		=> $tagManager->findOneByLabel('coup critique', 'fr'),
				'Vitesse d\'attaque'	=> $tagManager->findOneByLabel('vitesse d\'attaque', 'fr'),
				'Vol de vie'			=> $tagManager->findOneByLabel('vol de vie', 'fr'),
				'Puissance'			=> $tagManager->findOneByLabel('puissance', 'fr'),
				'Réduction des délais'	=> $tagManager->findOneByLabel('réduction des délais', 'fr'),
				'Mana'			=> $tagManager->findOneByLabel('mana', 'fr'),
				'Régénération mana'	=> $tagManager->findOneByLabel('régénération mana', 'fr'),
				'Déplacements'		=> $tagManager->findOneByLabel('déplacement', 'fr'),
				'Consommables'		=> $tagManager->findOneByLabel('consommable', 'fr')
			);

			/* @var $itemManager \MVNerds\CoreBundle\Item\ItemManager */
			$itemManager = $this->get('mvnerds.item_manager');

			if ($code == null) {
				$items = $itemManager->findAll();

				//Si l'utilisateur demande à récupérer tous les champions qui suivent l'index de départ
				if ($nbItem <= 0)
				{
					$nbItem = count($items) - $startItem;
				}
			} else {
				try {
					$items = array($itemManager->findByCode($code));
					$startItem = 0;
					$nbItem = 1;
				} catch ( \Exception $e ) {
					throw new \Exception('No item with code '.$code);
				}
			}


			for ($i = $startItem; $i < $startItem + $nbItem; $i++)
			{
				$item=$items[$i];

				//Récupération de la page de l'item
				$itemPage = file_get_html('http://lol-fr.com/objets/' . $item->getRiotCode())->find('div#content', 0);
				$description = $itemPage->find('p#item-desc', 0)->innertext;

				$stats = preg_split( "#<br( /)?> #", $description);

				foreach ($stats as $stat)
				{
					$stat = trim($stat);
					echo $stat;
					//Si le symbole + est trouvé ça veut dire que c est une stat de base
					if (strpos($stat, '+') !== false)
					{
						//On enleve ce qui suit les parentheses s il y en a
						if(($escaped = strstr($stat, '(', true)))
						{
							$exploded = explode(' ', $escaped, 2);
						}
						else
						{
							$exploded = explode(' ', $stat, 2);
						}

						if (count($exploded) >1 )
						{
							$statValue = $exploded[0];
							$statLabel = $exploded[1];
						}
						else
						{
							$exploded = preg_replace(array('/\(/', '/ \+ 0\)/'), array(' ',' '), $stat);
							$statValue = $exploded[0];
							$statLabel = $exploded[1];
						}

						if (array_key_exists($statLabel, $itemEffects))
						{
							$itemPrimaryEffect = new ItemPrimaryEffect();
							$itemPrimaryEffect->setItem($item);
							$itemPrimaryEffect->setPrimaryEffect($itemEffects[$statLabel]);
							$itemPrimaryEffect->setValue($statValue);
							try{
								$itemPrimaryEffect->save();
							}catch(\Exception $e)
							{
								echo 'already saved';
							}
						}
					}
					elseif (strpos($stat, 'Propriété passive') !== false)
						{
							$itemSecondaryEffect = new ItemSecondaryEffect();
							if (strpos($stat, 'UNIQUE') !== false)
							{
								$itemSecondaryEffect->setIsUnique(true);
							}
							else
							{
								$itemSecondaryEffect->setIsUnique(false);
							}
							$itemSecondaryEffect->setCategory('PASSIVE');
							$itemSecondaryEffect->setDescription($stat);
							$itemSecondaryEffect->setItem($item);
							$itemSecondaryEffect->save();
						}
						elseif (strpos($stat, 'Propriété active') !== false)
						{
							$itemSecondaryEffect = new ItemSecondaryEffect();
							if (strpos($stat, 'UNIQUE') !== false)
							{
								$itemSecondaryEffect->setIsUnique(true);
							}
							else
							{
								$itemSecondaryEffect->setIsUnique(false);
							}
							$itemSecondaryEffect->setCategory('ACTIVE');
							$itemSecondaryEffect->setDescription($stat);
							$itemSecondaryEffect->setItem($item);
							$itemSecondaryEffect->save();
						}
						elseif (strpos($stat, 'Halo') !== false)
						{
							$itemSecondaryEffect = new ItemSecondaryEffect();
							if (strpos($stat, 'UNIQUE') !== false)
							{
								$itemSecondaryEffect->setIsUnique(true);
							}
							else
							{
								$itemSecondaryEffect->setIsUnique(false);
							}
							$itemSecondaryEffect->setCategory('AURA');
							$itemSecondaryEffect->setDescription($stat);
							$itemSecondaryEffect->setItem($item);
							$itemSecondaryEffect->save();

						}
						elseif (strpos($stat, 'Cliquer pour consommer :') !== false)
						{
							$itemSecondaryEffect = new ItemSecondaryEffect();
							$itemSecondaryEffect->setIsUnique(false);		
							$itemSecondaryEffect->setCategory('CONSUMABLE');
							$itemSecondaryEffect->setDescription($stat);
							$itemSecondaryEffect->setItem($item);
							$itemSecondaryEffect->save();
						}
						else
						{
							$itemSecondaryEffect = new ItemSecondaryEffect();
							$itemSecondaryEffect->setIsUnique(false);		
							$itemSecondaryEffect->setCategory('OTHER');
							$itemSecondaryEffect->setDescription($stat);
							$itemSecondaryEffect->setItem($item);
							$itemSecondaryEffect->save();
						}
				}
				if (( $categories = $itemPage->find('div#item-categories ul li')))
				{
					foreach ($categories as $category)
					{
						if (array_key_exists($category->plaintext, $statConversion))
						{
							$itemTag = new ItemTag();
							$itemTag->setItem($item);
							$itemTag->setTag($statConversion[$category->plaintext]);
							try{
							$itemTag->save();
							}catch(\Exception $e){}
						}
					}
				}

			}
			// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
			$flashManager->setSuccessMessage('Les items ont bien été grabbés');
			// On redirige l'utilisateur vers la liste des utilisateurs
			return $this->redirect($this->generateUrl('DataGrabber_items_complete'));
		}
		return $this->render('MVNerdsDataGrabberBundle:Item:index.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * V2 depuis lol toolkits en local
	 * 
	 * Permet de récupérer le reste des données de l item (stats tags)
	 * 
	 * A utiliser apres avoir insérer les objets de base avec l action getBaseItemsAction 
	 * sur un fichier provenant de la page des item du site officiel de LoL
	 * 
	 * @Route("/complete-bis", name="DataGrabber_items_complete_bis")
	 */
	public function getCompleteItemsBisAction()
	{
		
		include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');

		//Création du flash manager
		/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');

		$startItem = 0;
		$nbItem =0;

		/* @var $primaryEffectManager \MVNerds\CoreBundle\PrimaryEffect\PrimaryEffectManager */
		$primaryEffectManager = $this->get('mvnerds.primary_effect_manager');

		$itemEffects = array(
			'armure'						=> $primaryEffectManager->findByLabel('armure'),
			'résistance magique'				=> $primaryEffectManager->findByLabel('résistance magique'),
			'PV'							=> $primaryEffectManager->findByLabel('PV'),
			'puissance'						=> $primaryEffectManager->findByLabel('puissance'),
			'régénération du mana toutes les 5 sec'	=> $primaryEffectManager->findByLabel('régénération du mana toutes les 5 sec'),
			'vitesse d\'attaque'					=> $primaryEffectManager->findByLabel('vitesse d\'attaque'),
			'dégâts d\'attaque'					=> $primaryEffectManager->findByLabel('dégâts d\'attaque'),
			'régénération des PV toutes les 5 sec'	=> $primaryEffectManager->findByLabel('régénération des PV toutes les 5 sec'),
			'mana'						=> $primaryEffectManager->findByLabel('mana'),
			'chances de coup critique'			=> $primaryEffectManager->findByLabel('chances de coup critique'),
			'vol de vie'						=> $primaryEffectManager->findByLabel('vol de vie'),
			'vitesse de déplacement'				=> $primaryEffectManager->findByLabel('vitesse de déplacement'),
			'puissance par niveau'				=> $primaryEffectManager->findByLabel('puissance par niveau'),
			'dégâts d\'attaque par niveau'			=> $primaryEffectManager->findByLabel('dégâts d\'attaque par niveau')
		);

		/* @var $tagManager \MVNerds\CoreBundle\Tag\TagManager */
		$tagManager = $this->get('mvnerds.tag_manager');

		//Tableau de conversion des tags
		$statConversion = array(
			'Santé'			=> $tagManager->findOneByLabel('santé', 'fr'),
			'Résistance magique'	=> $tagManager->findOneByLabel('résistance magique', 'fr'),
			'Régénération santé'	=> $tagManager->findOneByLabel('régénération santé', 'fr'),
			'Armure'			=> $tagManager->findOneByLabel('armure', 'fr'),
			'Dégâts'			=> $tagManager->findOneByLabel('dégâts', 'fr'),
			'Coup critique'		=> $tagManager->findOneByLabel('coup critique', 'fr'),
			'Vitesse d\'attaque'	=> $tagManager->findOneByLabel('vitesse d\'attaque', 'fr'),
			'Vol de vie'			=> $tagManager->findOneByLabel('vol de vie', 'fr'),
			'Puissance'			=> $tagManager->findOneByLabel('puissance', 'fr'),
			'Réduction des délais'	=> $tagManager->findOneByLabel('réduction des délais', 'fr'),
			'Mana'			=> $tagManager->findOneByLabel('mana', 'fr'),
			'Régénération mana'	=> $tagManager->findOneByLabel('régénération mana', 'fr'),
			'Déplacements'		=> $tagManager->findOneByLabel('déplacement', 'fr'),
			'Consommables'		=> $tagManager->findOneByLabel('consommable', 'fr')
		);

		/* @var $itemManager \MVNerds\CoreBundle\Item\ItemManager */
		$itemManager = $this->get('mvnerds.item_manager');

		$items = file_get_html('file:///C:/Users/Haniki/Downloads/lol_tk_build.html')->find('select#selectItemFrench option');
		//Si l'utilisateur demande à récupérer tous les champions qui suivent l'index de départ
		if ($nbItem <= 0)
		{
			$nbItem = count($items) - $startItem;
		}

		for ($i = $startItem; $i < $startItem + $nbItem; $i++)
		{
			$itemPage = $items[$i];
			$onclick = $itemPage->onclick;

			$onclickEscaped = str_replace	('addItem(', '', $onclick);

			$pos = strrpos($onclickEscaped, ')');

			$onclickEscaped = substr_replace($onclickEscaped, '', $pos, strlen($onclickEscaped));
			
			$onclickExploded = explode(',', $onclickEscaped, 5);
			$itemCode = trim($onclickExploded[1]);
			
			$itemEffects = $onclickExploded[4];
			$itemEffects = substr($itemEffects, 2, - 1);
			
			$stats = explode('&lt;br/&gt;', $itemEffects);
			$stats = str_replace('\\', '', $stats);
			
			try{
				$item = $itemManager->findByCode($itemCode);
			}catch(\Exception $e){
				continue;
			}
			
			foreach ($stats as $stat)
			{
				$stat = trim($stat);
				
				//Si le symbole + est trouvé ça veut dire que c est une stat de base
				if (strpos($stat, '+') !== false && strpos($stat, '+') == 0)
				{
					continue;
				}
				else
				{
					$itemSecondaryEffect = new ItemSecondaryEffect();
					
					if (strpos($stat, 'UNIQUE') !== false)
					{
						$itemSecondaryEffect->setIsUnique(true);
					}
					else
					{
						$itemSecondaryEffect->setIsUnique(false);
					}
					
					if (strpos($stat, 'Propriété passive') !== false)
					{
						$itemSecondaryEffect->setCategory('PASSIVE');
					}
					elseif (strpos($stat, 'Propriété active') !== false)
					{
						$itemSecondaryEffect->setCategory('ACTIVE');
					}
					elseif (strpos($stat, 'Halo') !== false)
					{
						$itemSecondaryEffect->setCategory('AURA');

					}
					elseif (strpos($stat, 'Cliquer pour consommer :') !== false)
					{
						$itemSecondaryEffect->setCategory('CONSUMABLE');
					}
					else
					{	
						$itemSecondaryEffect->setCategory('OTHER');
					}
					$itemSecondaryEffect->setDescription(utf8_encode($stat));
					$itemSecondaryEffect->setItem($item);
					echo $itemSecondaryEffect->getDescription() . ' | '.utf8_decode($itemSecondaryEffect->getDescription()).' | '.utf8_encode($itemSecondaryEffect->getDescription()).'<br/><br/>';
					$itemSecondaryEffect->save();
				}
			}
		}die();
	}
	
	/**
	 * Permet de récupérer les noms des items en anglais
	 * 
	 * A utiliser apres avoir insérer les objets de base avec l action getBaseItemsAction 
	 * sur un fichier provenant de la page des item du site officiel de LoL
	 * 
	 * @Route("/en-names", name="DataGrabber_items_en_names")
	 */
	public function getEnNamesAction()
	{
		include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');
				
		//Récupération de la liste des items
		$itemsList = file_get_html('file:///C:/Users/Haniki/Downloads/Items_lol_en.html')->find('div#list_view table.champion_item');

		$startItem = 0;
		$nbItem = count($itemsList);
		
		//Création du flash manager
		/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');

		//Si la liste des items a bien été récupérée
		if ($itemsList)
		{		
			/* @var $itemManager \MVNerds\CoreBundle\Item\ItemManager */
			$itemManager = $this->get('mvnerds.item_manager');

			//On boucle sur chaque champion pour récupérer le lien associé
			for ($i = $startItem; $i < $startItem + $nbItem; $i++)
			{
				$description = $itemsList[$i]->find('td.description', 0);

				$itemHref = $description->find('a.lol_item',0)->href;
				$itemCode = substr(strstr($itemHref, '#'), 1);
				try{
					//Si l item a été trouvé
					$item = $itemManager->findByCode($itemCode, 'en');
					$item->setLocale('en');
					$name = $description->find('a.lol_item span',0)->plaintext;
					$item->setName($name);
					$itemManager->save($item);
				}
				catch(\Exception $e){
					continue;
				}
			}
		}
		else
		{
			// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
			$flashManager->setErrorMessage('Erreur lors du grab des noms en des items');
			// On redirige l'utilisateur vers la liste des utilisateurs
			return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
		}

		// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
		$flashManager->setSuccessMessage('Les noms en des items ont bien été grabbés');
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
	}
	
	/**
	 * Permet de récupérer les modes de jeu des items
	 * 
	 * A utiliser apres avoir insérer les objets de base avec l action getBaseItemsAction 
	 * sur un fichier provenant de la page des item du site officiel de LoL
	 * 
	 * Cette action s utilise sur un fichier en local  extrait de la page des items de mobafire
	 * 
	 * @Route("/game-modes", name="DataGrabber_items_game_modes")
	 */
	public function getItemGameModesAction()
	{
		include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');
				
		//Récupération de la liste des items
		$itemsList = file_get_html('file:///C:/Users/Haniki/Downloads/MOBAFire_items.htm')->find('div#browse-items a.champ-box');

		$startItem = 0;
		$nbItem = count($itemsList);
		
		//Création du flash manager
		/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');

		//Si la liste des items a bien été récupérée
		if ($itemsList)
		{		
			/* @var $itemManager \MVNerds\CoreBundle\Item\ItemManager */
			$itemManager = $this->get('mvnerds.item_manager');

			//On boucle sur chaque champion pour récupérer le lien associé
			for ($i = $startItem; $i < $startItem + $nbItem; $i++)
			{
				$link = $itemsList[$i];
				
				$itemName = $link->find('div.info div.champ-name', 0)->plaintext;
				
				try{
					//Si l item a été trouvé
					$item = $itemManager->findByName($itemName, 'en');
					$item->setLocale('en');
					
					$classes = $link->class;
					
					$itemGameMode = new \MVNerds\CoreBundle\Model\ItemGameMode();
					$itemGameMode->setItem($item);
					
					if (strpos($classes, 'classic-only') !== false)
					{
						$itemGameMode->setGameModeId(1);
						
					}
					elseif (strpos($classes, 'dominion-only') !== false)
					{
						$itemGameMode->setGameModeId(2);
					}
					else
					{
						$itemGameMode->setGameModeId(4);
					}
					var_dump($itemGameMode);
					$itemGameMode->save();
				}
				catch(\Exception $e){
					continue;
				}
			}
		}
		else
		{
			// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
			$flashManager->setErrorMessage('Erreur lors du grab des game modes');
			// On redirige l'utilisateur vers la liste des utilisateurs
			return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
		}

		// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
		$flashManager->setSuccessMessage('Les game modes ont bien été grabbés');
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
	}
	
	/**
	 * Permet de récupérer effets des items en anglais
	 * 
	 * A utiliser apres avoir utilisé getEnNames
	 * 
	 * @Route("/en-effects", name="DataGrabber_items_en_effects")
	 */
	public function getEnEffectsAction()
	{
		include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');

		/* @var $itemManager \MVNerds\CoreBundle\Item\ItemManager */
		$itemManager = $this->get('mvnerds.item_manager');

		$itemsList = $itemManager->findAll();
		
		$startItem = 0;
		$nbItem = 1;//count($itemsList);
		
		//Création du flash manager
		/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');

		//Si la liste des items a bien été récupérée
		if ($itemsList)
		{
			//On boucle sur chaque champion pour récupérer le lien associé
			for ($i = $startItem; $i < $startItem + $nbItem; $i++)
			{
				$item=$itemsList[$i];
				
				$enName = $item->setLocale('en')->getName();
				$escapedName = str_replace('\'', '%27', str_replace(' ', '_', $enName));
				//Récupération de la page de l'item
				$itemPage = file_get_html('http://leagueoflegends.wikia.com/wiki/' . $escapedName)->find('table.infobox', 0);
				
				$infoRow = $itemPage->find('tr');
				foreach ($infoRow as $info)
				{
					if ($info->find('th', 0)->plaintext == 'Sell value')
					{
						$sellValueG = $info->find('td span.gold', 0)->plaintext;
						$sellValue = strstr($sellValueG, 'g', true);
						$item->setSellValue($sellValue);
					}
					elseif ($info->find('th', 0)->plaintext == 'Stacks')
					{
						$stacksPerSlot = $info->find('td', 0)->plaintext;
						$stacks = strstr($stacksPerSlot, ' per item slot.', true);
						$item->setStacks($stacks);
					}
					elseif ($info->find('th', 0)->plaintext == 'Effects')
					{
						$stacksPerSlot = $info->find('td', 0)->plaintext;
						$stacks = strstr($stacksPerSlot, ' per item slot.', true);
						$item->setStacks($stacks);
					}
				}
			}
		}
		else
		{
			// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
			$flashManager->setErrorMessage('Erreur lors du grab des game modes');
			// On redirige l'utilisateur vers la liste des utilisateurs
			return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
		}

		// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
		$flashManager->setSuccessMessage('Les game modes ont bien été grabbés');
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
	}
}
