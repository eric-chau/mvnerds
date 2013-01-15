<?php

namespace MVNerds\DataGrabberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\DataGrabberBundle\Form\Type\ChampionGrabberType;
use MVNerds\CoreBundle\Model\TagQuery;
use MVNerds\CoreBundle\Model\TagI18nPeer;
use MVNerds\CoreBundle\Model\Skill;
/**
 * @Route("/skills")
 */
class SkillController extends Controller
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
				$championsList = file_get_html('http://lol-fr.com/champions/')->find('ul#champions-list', 0);

				//Si la liste des champions a bien été récupérée
				if ($championsList)
				{
					/* @var $championTagManager \MVNerds\CoreBundle\ChampionTag\ChampionTagManager */
					$championTagManager = $this->get('mvnerds.champion_tag_manager');
					
					/* @var $championManager \MVNerds\CoreBundle\Champion\ChampionManager */
					$championManager = $this->get('mvnerds.champion_manager');

					/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
					$flashManager = $this->get('mvnerds.flash_manager');
					
					/* @var $skillManager \MVNerds\CoreBundle\Skill\SkillManager */
					$skillManager = $this->get('mvnerds.skill_manager');

					//Liens de tous les champions
					$championsLinks = $championsList->find('li a');

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
							try {
								$champion = $championManager->findByName($name);
							} catch ( \Exception $e) {
								continue;
							}
							
							$championSpellsHtml = $championHtml->find('ul#champion-spells li');
							for ($j = 0; $j < 5; $j++) 
							{
								$championSpellHtml = $championSpellsHtml[$j];
								
								$spellNameHtml = $championSpellHtml->find('p', 0);
								$spellName = $spellNameHtml->find('strong', 0)->plaintext;
								try {
									$skill = $skillManager->findByName($spellName);
									continue;
								} catch ( \Exception $e ) {
									$skill = new Skill();
									$skill->setName($spellName);
									$skill->setChampion($champion);
									$skill->setLocale('fr');
								}
								
								$spellImageUrl = $championSpellHtml->find('img', 0)->src;
								
								if ($j < 4) {
									$spellTooltip = $championSpellHtml->find('p', 1)->innertext;
									$spellTooltipExploded = explode('<br />', $spellTooltip);

									$skill->setCooldown(array_shift($spellTooltipExploded));
									$skill->setCost(array_shift($spellTooltipExploded));
									$skill->setRange(0);
									
									$spellDescriptionImploded = implode(' ', $spellTooltipExploded);
									$spellDescriptionEscaped = trim(str_get_html($spellDescriptionImploded)->plaintext);
									$spellDescription = preg_replace('/ +/', ' ', $spellDescriptionEscaped);

									$skill->setDescription($spellDescription);
									$skill->setPosition($j+1);
								} else {
									$passiveDescriptionRaw = preg_replace('/((<strong>).*(<\/strong>))(<br \/>)/', ' ', $spellNameHtml->innertext);
									$passiveDescription = preg_replace('/ +/', ' ', trim($passiveDescriptionRaw));
									$skill->setDescription($passiveDescription);
									$skill->setPosition(0);
								}
								$skill->save();
								file_put_contents(__DIR__ . '/../../../../web/images/spells/'. $skill->getSlug() .'.png', file_get_contents($spellImageUrl));
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
				$flashManager->setSuccessMessage('Les skills ont bien été grabbés');
				// On redirige l'utilisateur vers la liste des utilisateurs
				return $this->redirect($this->generateUrl('DataGrabber_champions_index'));
			}
		}
		return $this->render('MVNerdsDataGrabberBundle:Champion:index.html.twig', array(
			'form' => $form->createView()
		));
	}
}
