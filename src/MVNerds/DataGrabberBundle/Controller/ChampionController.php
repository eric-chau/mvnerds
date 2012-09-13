<?php

namespace MVNerds\DataGrabberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\Model\Champion;
use MVNerds\CoreBundle\Model\ChampionQuery;
use MVNerds\CoreBundle\Model\ChampionPeer;
/**
 * @Route("/champions")
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
		include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');
		
		//Récupération de la liste des champions
		$championsList = file_get_html('http://lol-fr.com/champions/')->find('ul#champions-list');
		
		//Si la liste des champions a bien été récupérée
		if ($championsList)
		{
			//Nombre de champions à récupérer simultanément
			$nbChamp = 1;
			//Indice du champion par lequel on commence le parcours
			$startChamp = 102;
			
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
			
			//Méthodes de la classe Champion
			$championMethods = get_class_methods(new Champion);
			
			//Liens de tous les champions
			$championsLinks = $championsList[0]->find('li a');
			
			//On boucle sur chaque champion pour récupérer le lien associé
			for ($i = $startChamp; $i < $startChamp + $nbChamp; $i++)
			{
				$championLink = $championsLinks[$i];
				
				//Récupération de la page du champion
				$championHtml = file_get_html('http://lol-fr.com' . $championLink->href);
				
				if ($championHtml)
				{
					//Récupération du nom du champion
					$nameHtml = $championHtml->find('ul#champion-skin-switcher li');
					$name = $nameHtml[1]->plaintext;

					//On vérifie si le champion éxiste déjà
					if (!($champion = ChampionQuery::create()->add(ChampionPeer::NAME, $name)->findOne()))
					{
						//S'il n'éxiste pas on en crée un nouveau
						$champion = new Champion();
						$champion->setName($name);
					}

					//Recherche des stats du champion
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
					
					//On fait persister le champion
					$champion->save();
				}
				else
				{
					$message = 'Le champion n\'a pas été trouvé';
				}
			}
			$message = 'Les champions ont bien été enregistrés';
		}
		else
		{
			$message = 'La liste des champions n\'a pas été trouvée';
		}
		
		return $this->render('MVNerdsDataGrabberBundle:Champion:index.html.twig', array(
			'message' => $message
		));
	}
}
