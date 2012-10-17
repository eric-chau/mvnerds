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
	 * @Route("/complete", name="DataGrabber_items_complete")
	 */
	public function getCompleteItemsAction()
	{
		$form = $this->createForm(new ItemGrabberType());
				
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
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
				
				$items = $itemManager->findAll();
				
				//Si l'utilisateur demande à récupérer tous les champions qui suivent l'index de départ
				if ($nbItem <= 0)
				{
					$nbItem = count($items) - $startItem;
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
		}
		return $this->render('MVNerdsDataGrabberBundle:Item:index.html.twig', array(
			'form' => $form->createView()
		));
	}
}
