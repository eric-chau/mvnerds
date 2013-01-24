<?php

namespace MVNerds\DataGrabberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\Model\Champion;
use MVNerds\CoreBundle\Model\ChampionQuery;
use MVNerds\CoreBundle\Model\ChampionPeer;
use MVNerds\DataGrabberBundle\Form\Type\ChampionGrabberType;
use MVNerds\DataGrabberBundle\Form\Type\ChampionGrabberByNameType;
use MVNerds\CoreBundle\Model\TagQuery;
use MVNerds\CoreBundle\Model\TagI18nPeer;
use MVNerds\CoreBundle\Model\ChampionI18nPeer;

/**
 * @Route("/champs")
 */
class ChampionController extends Controller
{	
	/**
	 * Permet d'insérer dans la base les champions de manière automatique
	 * 
	 * @Route("/", name="DataGrabber_champions_index")
	 */
	public function indexAction()
	{
		$form = $this->createForm(new ChampionGrabberType());
				
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');
				
				$champInfo = $form->getData();
				$startChamp = $champInfo['start_index'];
				$nbChamp = $champInfo['nb_champions'];
				
				//Récupération de la liste des champions
				$championsList = file_get_html('http://lol-fr.com/champions/')->find('ul#champions-list');

				//Si la liste des champions a bien été récupérée
				if ($championsList)
				{			
					//Création d"un tableau associatif permettant de faire l'équivalence entre le nom 
					//des statistiques du site à extraire en françai et le nom du champ dans la base
					$statConversion = array(
						'Santé'				=> 'setBaseHealth',
						'Mana'				=> 'setBaseMana',
						'Dégâts'				=> 'setBaseDamage',
						'Vitesse d\'attaque'		=> 'setBaseAttackSpeed',
						'Armure'				=> 'setBaseArmor',
						'Résistance magique'		=> 'setBaseMagicResist',
						'Régénération santé'		=> 'setBaseHealthRegen',
						'Régénération mana'		=> 'setBaseManaRegen',
						'Vitesse de déplacement'	=> 'setMoveSpeed',
						'Distance d\'attaque'		=> 'setAttackRange'
					);

					//idem pour les champs de bonus par niveau
					$bonusStatConversion = array(
						'Santé'				=> 'setBonusHealthPerLevel',
						'Mana'				=> 'setBonusManaPerLevel',
						'Dégâts'				=> 'setBonusDamagePerLevel',
						'Vitesse d\'attaque'		=> 'setBonusAttackSpeedPerLevel',
						'Armure'				=> 'setBonusArmorPerLevel',
						'Résistance magique'		=> 'setBonusMagicResistPerLevel',
						'Régénération santé'		=> 'setBonusHealthRegenPerLevel',
						'Régénération mana'		=> 'setBonusManaRegenPerLevel'
					);
					
					//Tableau de conversion des tags
					$tagConversion = array(
						'attaquant'	=> 'pusher',
						'jungler'	=> 'jungler',
						'soutien'	=> 'support',
						'assassin'	=> 'assassin',
						'à distance'	=> 'ranged',
						'furtif'		=> 'stealth',
						'conseillé'	=> 'recommended',
						'mage'	=> 'mage',
						'carry'		=> 'carry',
						'tank'		=> 'tank',
						'combattant'	=> 'fighter',
						'mêlée'	=> 'melee',
					);
					
					//Création du champion tag manager
					/* @var $championTagManager \MVNerds\CoreBundle\ChampionTag\ChampionTagManager */
					$championTagManager = $this->get('mvnerds.champion_tag_manager');

					//Création du flash manager
					/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
					$flashManager = $this->get('mvnerds.flash_manager');
					
					//Méthodes de la classe Champion
					$championMethods = get_class_methods(new Champion);

					//Liens de tous les champions
					$championsLinks = $championsList[0]->find('li a');

					//Si l'utilisateur demande à récupérer tous les champions qui suivent l'index de départ
					if ($nbChamp <= 0)
					{
						$nbChamp = count($championsLinks) - $startChamp;
					}
					
					//On boucle sur chaque champion pour récupérer le lien associé
					for ($i = $startChamp; $i < $startChamp + $nbChamp; $i++)
					{
						$championLink = $championsLinks[$i];

						//Récupération de la page du champion
						$championHtml = file_get_html('http://lol-fr.com' . $championLink->href);

						if ($championHtml)
						{	
							//Récupération du nom du champion
							$name= $championHtml->find('ul#champion-skin-switcher li', 1)->plaintext;
							$champion = ChampionQuery::create()->joinWithI18n()->add(ChampionI18nPeer::NAME, $name)->findOne();
							//On vérifie si le champion éxiste déjà
							if (!$champion)
							{
								//S'il n'éxiste pas on en crée un nouveau
								$champion = new Champion();
								$champion->setName($name);
								$champion->setLocale('en');
								$champion->setName($name);
								$champion->setLocale('fr');
								
							}
							$championTitleTmp = $championHtml->find('#content h2', 0)->plaintext;
							$championTitle = substr(stristr($championTitleTmp, ', '), 2);
							$championLore = $championHtml->find('#champion-lore', 0)->plaintext;
							$championRPCost = $championHtml->find('#champion-price #champion-rpcost', 0)->plaintext;
							$championIPCost = $championHtml->find('#champion-price #champion-ipcost', 0)->plaintext;
							
							$champion->setTitle($championTitle);
							$champion->setLore($championLore);
							$champion->setRpCost($championRPCost);
							$champion->setIpCost($championIPCost);

							
							/**
							 * Recherche des stats du champion
							 */
							$stats = $championHtml->find('div#champion-stats table tr');
							$statsSize = count($stats);

							//Parcours des stats du champion en sautant le premier indice correspondant au header de la table
							for ($j = 1; $j < $statsSize; $j++)
							{	
								//récupération du contenu de la balise <tr>
								$tr = $stats[$j];

								//Récupération de la balise <th> a l'intérieur du <tr> qui contient le nom de la stat
								$statNameHtml = $tr->find('th');
								$statName = $statNameHtml[0]->plaintext;
								$statName = str_replace(' :', '', $statName);

								//Si le nom de la stat existe dans le tableau des stats
								if (array_key_exists($statName, $statConversion))
								{
									//on récupère la méthode correspondante
									$method = $statConversion[$statName];
									//si celle-ci existe parmis les méthodes de la classe Champion
									if (in_array($method, $championMethods))
									{
										//Récupération des deux <td> contenus dans le <tr> qui contiennent la valeur de la stat et son modifieur par niveau
										$statsData = $tr->find('td');
										$statValue = $statsData[0]->plaintext;
										$statValue = str_replace(',', '.', $statValue);

										//On applique la méthode préparée plus tôt
										$champion->$method($statValue);

										//On récupère le bonus par niveau de la stat
										$statModifierTmp = $statsData[1];

										if ($statModifierTmp)
										{
											//Si le modifier existe dans le tableau des bonus par niveau
											if (array_key_exists($statName ,$bonusStatConversion))
											{
												//On récupère la méthode associée
												$bonusMethod = $bonusStatConversion[$statName];

												//si celle-ci existe parmis les méthodes de la classe Champion
												if (in_array($bonusMethod, $championMethods))
												{
													//On applique la méthode
													$statModifier = str_replace(',', '.', $statModifierTmp->plaintext);
													$champion->$bonusMethod($statModifier);
												}
											}
										}
									}
								}
							}
							/**
							 * Fin de la récupération des stats du champion
							 */
														
							//On fait persister le champion
							$champion->save();
							
							/**
							 * Récupération des tags du champion
							 */
							foreach ($championHtml->find('div#champion-stats p a') as $tagHtml)
							{
								$tagLabelRaw = $tagHtml->plaintext;
								
								//Si le tag est trouvé dans le tableau de conversion
								if(key_exists($tagLabelRaw, $tagConversion))
								{
									//On récupère le tag au format de la base de données
									$tagLabel = $tagConversion[$tagLabelRaw];
									
									//Si le tag est trouvé en base
									$tag = TagQuery::create()
										->joinWith('TagI18n')
										->add(TagI18nPeer::LABEL, $tagLabel)
									->findOne();
									if ($tag)
									{
										//On enregistre l'association
										$championTagManager->addTagToChampion($tag, $champion);
									}
								}							
							}
						}
					}
				}
				else
				{
					// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
					$flashManager->setErrorMessage('Flash.error.grab.champions');
					// On redirige l'utilisateur vers la liste des utilisateurs
					return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
				}
				
				// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
				$flashManager->setSuccessMessage('Flash.success.grab.champions');
				// On redirige l'utilisateur vers la liste des utilisateurs
				return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
			}
		}
		return $this->render('MVNerdsDataGrabberBundle:Champion:index.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Permet de mettre à jour les statistiques des champions depuis le site du wiki de LoL
	 * 
	 * @Route("/update-stats", name="DataGrabber_champions_update_stats")
	 */
	public function updateStatsAction()
	{
		$form = $this->createForm(new ChampionGrabberType());
				
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');
				//Augmente la durée maximum d'exécution
				set_time_limit(30);
				
				$champInfo = $form->getData();
				$startChamp = $champInfo['start_index'];
				$nbChamp = $champInfo['nb_champions'];
				
				//Récupération de la liste des champions
				$championsList = file_get_html('http://leagueoflegends.wikia.com/wiki/League_of_Legends_Wiki')->find('ol.champion_roster', 0);
				
				//Si la liste des champions a bien été récupérée
				if ($championsList->find('li span a'))
				{
					/* @var $championManager \MVNerds\CoreBundle\Champion\ChampionManager */
					$championManager = $this->get('mvnerds.champion_manager');
					/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
					$flashManager = $this->get('mvnerds.flash_manager');

					//Liens de tous les champions
					$championsLinks = $championsList->find('li span a');

					//Si l'utilisateur demande à récupérer tous les champions qui suivent l'index de départ
					if ($nbChamp <= 0)
					{
						$nbChamp = count($championsLinks) - $startChamp;
					}
					
					$errors = 'Erreurs : <br />';
					
					//On boucle sur chaque champion pour récupérer le lien associé
					for ($i = $startChamp; $i < $startChamp + $nbChamp; $i++)
					{
						$championLink = $championsLinks[$i];

						//Récupération de la page du champion
						$championHtml = file_get_html('http://leagueoflegends.wikia.com' . $championLink->href);
						
						if ($championHtml->find('h1'))
						{	
							//Récupération du nom du champion
							$name = $championHtml->find('#WikiaPageHeader h1', 0)->plaintext;
							try {
								/* @var $champion \MVNerds\CoreBundle\Model\Champion */
								$champion = $championManager->findByName($name);
								$champion->setLocale('fr');
							} catch (\Exception $e) {
								$errors .= 'Impossible de trouver le champion ayant pour nom : ' . $name . '<br />';
								continue;
							}
							
							//Récupération du prix du champion
							$championCostHtml = $championHtml->find('#champion_info-upper tr td', 2)->find('span a');
							$champion->setIpCost($championCostHtml[1]->plaintext);
							$champion->setRpCost($championCostHtml[3]->plaintext);
							//Initialisation du mana type à none qui sera écrasé par la suite
							$champion->setManaType('NONE');
							
							//Récupération des stats du champion
							$championStatsHtml = $championHtml->find('table#champion_info-lower table', 1)->find('tr td');
							foreach($championStatsHtml as $key => $championStatHtml) {
								if ($key % 2 != 0) {
									//On saute un tour de boucle tous les deux tours
									continue;
								}
								$statName =  trim($championStatHtml->plaintext);
								$statValue = trim($championStatsHtml[$key + 1]->plaintext);
								
								if ( $statName == 'Health') {
									$flatValue = trim(strstr($statValue, '(', true)) * 1;
									$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) * 1;
									$champion->setBaseHealth($flatValue + $perLvlValue);
									$champion->setBonusHealthPerLevel($perLvlValue);
								} elseif ($statName == 'Attack damage') {
									$flatValue = trim(strstr($statValue, '(', true)) * 1;
									$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) * 1;
									$champion->setBaseDamage($flatValue + $perLvlValue);
									$champion->setBonusDamagePerLevel($perLvlValue);
								} elseif ($statName == 'Health regen.') {
									$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) / 5;
									$flatValue = trim(strstr($statValue, '(', true)) / 5;
									$champion->setBaseHealthRegen(round($flatValue + $perLvlValue, 2));
									$champion->setBonusHealthRegenPerLevel(round($perLvlValue, 2));
								} elseif ($statName == 'Attack speed') {
									$flatValue = trim(strstr($statValue, '(', true)) * 1;
									$perLvlValue = preg_replace('/\(\+|%\)/', '', trim(strstr($statValue, '(+'))) * 1;
									$champion->setBaseAttackSpeed(round($flatValue, 4));
									$champion->setBonusAttackSpeedPerLevel(round($flatValue * $perLvlValue / 100, 4));
								} elseif ($statName == 'Mana' && $statValue != 'N/A') {
									$flatValue = trim(strstr($statValue, '(', true)) * 1;
									$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) * 1;
									$champion->setBaseMana($flatValue + $perLvlValue);
									$champion->setBonusManaPerLevel($perLvlValue);
									$champion->setManaType('MANA');
								} elseif ($statName == 'Fury' || $statName == 'Heat' || $statName == 'Energy') {
									$champion->setBaseMana($statValue * 1);
									$champion->setBonusManaRegenPerLevel(0);
									$champion->setManaType(strtoupper($statName));
								} elseif ($statName == 'Mana regen.') {
									$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) / 5;
									$flatValue = trim(strstr($statValue, '(', true)) / 5;
									$champion->setBaseManaRegen(round($flatValue + $perLvlValue, 2));
									$champion->setBonusManaRegenPerLevel(round($perLvlValue, 2));
								} elseif ($statName == 'Energy regen.') {
									$champion->setBaseManaRegen($statValue * 1);
									$champion->setBonusManaRegenPerLevel(0);
								} elseif ($statName == 'Magic res.') {
									$flatValue = trim(strstr($statValue, '(', true)) * 1;
									$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) * 1;
									$champion->setBaseMagicResist($flatValue + $perLvlValue);
									$champion->setBonusMagicResistPerLevel($perLvlValue);
								} elseif ($statName == 'Range') {
									$champion->setAttackRange($statValue);
								} elseif ($statName == 'Mov. speed') {
									$champion->setMoveSpeed($statValue);
								}
							}
							$champion->save();
						} else {
							$errors .= 'Impossible d\'accéder à la page du champion : http://leagueoflegends.wikia.com' . $championLink->href . '<br />';
						}
					}
				} else {
					$flashManager->setErrorMessage('Impossible d\'accéder à la liste des champions');
					return $this->redirect($this->generateUrl('DataGrabber_champions_update_stats'));
				}
				
				$flashManager->setSuccessMessage('Les statistiques ont bien été mises à jour.<br />' . $errors);
				return $this->redirect($this->generateUrl('DataGrabber_champions_update_stats'));
			}
		}
		
		return $this->render('MVNerdsDataGrabberBundle:Champion:update_stats.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Permet de créer un nouveau champion en pasant en paramètre un nom à grab sur le Wiki de LoL
	 * 
	 * @Route("/create", name="DataGrabber_champions_create")
	 */
	public function createAction()
	{
		$form = $this->createForm(new ChampionGrabberByNameType());
				
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');
				//Augmente la durée maximum d'exécution
				set_time_limit(30);
				
				$champInfo = $form->getData();
				$grabName = trim($champInfo['name']);
				$championName = preg_replace('/\'/', '%27',preg_replace('/ /', '_', $grabName));
				
				//Récupération de la liste des champions
				$championHtml = file_get_html('http://leagueoflegends.wikia.com/wiki/' . $championName)->find('ol.champion_roster', 0);
				
				/* @var $championManager \MVNerds\CoreBundle\Champion\ChampionManager */
				$championManager = $this->get('mvnerds.champion_manager');
				/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
				$flashManager = $this->get('mvnerds.flash_manager');

				$errors = 'Erreurs : <br />';
					
				if ($championHtml->find('h1'))
				{	
					//Récupération du nom du champion
					$name = $championHtml->find('#WikiaPageHeader h1', 0)->plaintext;
					try {
						$championManager->findByName($name);
						$errors .= 'Un champion avec le nom ' . $name . 'existe déjà';
					} catch (\Exception $e) {
						/* @var $champion \MVNerds\CoreBundle\Model\Champion */
						$champion = new Champion();
						$champion->setLocale('en');
						$champion->setName($name);
						//TODO Ajouter le title et le Lore et peut être les tags
						
						//Récupération du prix du champion
						$championCostHtml = $championHtml->find('#champion_info-upper tr td', 2)->find('span a');
						$champion->setIpCost($championCostHtml[1]->plaintext);
						$champion->setRpCost($championCostHtml[3]->plaintext);
						//Initialisation du mana type à none qui sera écrasé par la suite
						$champion->setManaType('NONE');

						//Récupération des stats du champion
						$championStatsHtml = $championHtml->find('table#champion_info-lower table', 1)->find('tr td');
						foreach($championStatsHtml as $key => $championStatHtml) {
							if ($key % 2 != 0) {
								//On saute un tour de boucle tous les deux tours
								continue;
							}
							$statName =  trim($championStatHtml->plaintext);
							$statValue = trim($championStatsHtml[$key + 1]->plaintext);

							if ( $statName == 'Health') {
								$flatValue = trim(strstr($statValue, '(', true)) * 1;
								$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) * 1;
								$champion->setBaseHealth($flatValue + $perLvlValue);
								$champion->setBonusHealthPerLevel($perLvlValue);
							} elseif ($statName == 'Attack damage') {
								$flatValue = trim(strstr($statValue, '(', true)) * 1;
								$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) * 1;
								$champion->setBaseDamage($flatValue + $perLvlValue);
								$champion->setBonusDamagePerLevel($perLvlValue);
							} elseif ($statName == 'Health regen.') {
								$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) / 5;
								$flatValue = trim(strstr($statValue, '(', true)) / 5;
								$champion->setBaseHealthRegen(round($flatValue + $perLvlValue, 2));
								$champion->setBonusHealthRegenPerLevel(round($perLvlValue, 2));
							} elseif ($statName == 'Attack speed') {
								$flatValue = trim(strstr($statValue, '(', true)) * 1;
								$perLvlValue = preg_replace('/\(\+|%\)/', '', trim(strstr($statValue, '(+'))) * 1;
								$champion->setBaseAttackSpeed(round($flatValue, 4));
								$champion->setBonusAttackSpeedPerLevel(round($flatValue * $perLvlValue / 100, 4));
							} elseif ($statName == 'Mana' && $statValue != 'N/A') {
								$flatValue = trim(strstr($statValue, '(', true)) * 1;
								$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) * 1;
								$champion->setBaseMana($flatValue + $perLvlValue);
								$champion->setBonusManaPerLevel($perLvlValue);
								$champion->setManaType('MANA');
							} elseif ($statName == 'Fury' || $statName == 'Heat' || $statName == 'Energy') {
								$champion->setBaseMana($statValue * 1);
								$champion->setBonusManaRegenPerLevel(0);
								$champion->setManaType(strtoupper($statName));
							} elseif ($statName == 'Mana regen.') {
								$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) / 5;
								$flatValue = trim(strstr($statValue, '(', true)) / 5;
								$champion->setBaseManaRegen(round($flatValue + $perLvlValue, 2));
								$champion->setBonusManaRegenPerLevel(round($perLvlValue, 2));
							} elseif ($statName == 'Energy regen.') {
								$champion->setBaseManaRegen($statValue * 1);
								$champion->setBonusManaRegenPerLevel(0);
							} elseif ($statName == 'Magic res.') {
								$flatValue = trim(strstr($statValue, '(', true)) * 1;
								$perLvlValue = preg_replace('/\(\+|\)/', '', trim(strstr($statValue, '(+'))) * 1;
								$champion->setBaseMagicResist($flatValue + $perLvlValue);
								$champion->setBonusMagicResistPerLevel($perLvlValue);
							} elseif ($statName == 'Range') {
								$champion->setAttackRange($statValue);
							} elseif ($statName == 'Mov. speed') {
								$champion->setMoveSpeed($statValue);
							}
						}
						$champion->save();
					}
				} else {
					$flashManager->setErrorMessage('Impossible d\'accéder à la page du champion');
					return $this->redirect($this->generateUrl('DataGrabber_champions_create'));
				}
				
				$flashManager->setSuccessMessage('Les statistiques ont bien été mises à jour.<br />' . $errors);
				return $this->redirect($this->generateUrl('DataGrabber_champions_create'));
			}
		}
		
		return $this->render('MVNerdsDataGrabberBundle:Champion:create.html.twig', array(
			'form' => $form->createView()
		));
	}
}
