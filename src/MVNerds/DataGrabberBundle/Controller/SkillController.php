<?php

namespace MVNerds\DataGrabberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\DataGrabberBundle\Form\Type\ChampionGrabberType;
use MVNerds\CoreBundle\Model\Skill;
/**
 * @Route("/skills")
 */
class SkillController extends Controller
{	
	/**
	 * Permet de récupérer les kills des champions depuis le site lol-fr en français
	 * 
	 * @Route("/", name="DataGrabber_skills_index")
	 */
	public function indexAction()
	{
		$form = $this->createForm(new ChampionGrabberType());
				
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{die('ko');
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
								$spellImageUrl = preg_replace('/ /', '%20', $spellImageUrl);
								try {
								file_put_contents(__DIR__ . '/../../../../web/images/skills/'. $skill->getSlug() .'.png', file_get_contents($spellImageUrl));
								} catch (\Exception $e) {}
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
		return $this->render('MVNerdsDataGrabberBundle:Skill:index.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Permet de récupérer les kills des champions depuis le site leaguecraft.com en anglais
	 * 
	 * @Route("/en", name="DataGrabber_skills_en")
	 */
	public function enAction()
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
				
				/* @var $championManager \MVNerds\CoreBundle\Champion\ChampionManager */
				$championManager = $this->get('mvnerds.champion_manager');
				
				//Récupération de la liste des champions
				$championsList = $championManager->findAll();
				
				/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
				$flashManager = $this->get('mvnerds.flash_manager');
				
				$errors = '';
				
				//Si la liste des champions a bien été récupérée
				if ($championsList)
				{
					/* @var $skillManager \MVNerds\CoreBundle\Skill\SkillManager */
					$skillManager = $this->get('mvnerds.skill_manager');
					
					
					//Si l'utilisateur demande à récupérer tous les champions qui suivent l'index de départ
					if ($nbChamp <= 0)
					{
						$nbChamp = count($championsList) - $startChamp;
					}
					
					
					//On boucle sur chaque champion pour récupérer le lien associé
					for ($i = $startChamp; $i < $startChamp + $nbChamp; $i++)
					{
						$champName = $championsList->get($i)->setLocale('en')->getName();
						$champNameEscaped = preg_replace("/([' ._]+)/", '-', $champName);
						
						//Récupération de la page du champion
						$championHtml = file_get_html('http://leaguecraft.com/guide/' . $champNameEscaped . '-build-guide.xhtml');

						if ($championHtml->find('h1', 0))
						{
							//Récupération du nom du champion
							$tmpName= $championHtml->find('h1', 0)->plaintext;
							$name = str_replace(' Build Guide', '', $tmpName);
							try {
								$champion = $championManager->findByName($name);
							} catch ( \Exception $e) {
								continue;
							}
							
							$championSpellsHtml = $championHtml->find('.spell_box');
							for ($j = 0; $j < 5; $j++) 
							{
								$championSpellHtml = $championSpellsHtml[$j];
								$tmpSpellName = $championSpellHtml->find('h3', 0)->plaintext;
								$spellName = preg_replace('/\(Q\) |\(W\) |\(E\) |\(R\) /', '', $tmpSpellName);
								echo $spellName . '<br />';
								try {
									/* @var $skill \MVNerds\CoreBundle\Model\Skill */
									$skill = $skillManager->findByChampionAndPosition($champion, $j);
									$slug = $skill->getSlug();
								} catch ( \Exception $e ) {
									continue;
								}
								
								$skill->setLocale('en');
								$skill->setName($spellName);
								
								if ($j > 0) {
									$spellText = preg_replace('/ +/', ' ',trim($championSpellHtml->find('div.spell_text', 0)->innertext));
									$spellTextExploded = preg_split('/<br clear="all">/', $spellText);
									$description = str_get_html($spellTextExploded[2])->plaintext;
									$skill->setDescription(trim($description));
									
									$spellTextHtml = $championSpellHtml->find('div.spell_text ul');
									$skill->setCooldown(preg_replace('/ +/', ' ',trim($spellTextHtml[0]->find('li', 1)->plaintext)));
									$skill->setCost(preg_replace('/ +/', ' ', trim($spellTextHtml[1]->find('li', 1)->plaintext)));
								} else {
									$spellTextHtml = $championSpellHtml->find('div.spell_text', 0);
									$spellText = trim($spellTextHtml->innertext);
									$description = substr($spellText, 0, strpos($spellText, '<li class="column'));
									$skill->setDescription(trim($description));
									$skill->setCooldown(0);
									$skill->setCost(0);
								}
								$skill->setSlug($slug);
								$skill->save();
							}
						} else {
							$errors .= 'champion '. $champNameEscaped . ' not found <br />';
						}
					}
				}
				else
				{
					// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
					$flashManager->setErrorMessage('Erreur <br />' . $errors);
					// On redirige l'utilisateur vers la liste des utilisateurs
					return $this->redirect($this->generateUrl('DataGrabber_skills_en'));
				}
				
				// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
				$flashManager->setSuccessMessage('Les skills ont bien été grabbés <br />'.$errors);
				// On redirige l'utilisateur vers la liste des utilisateurs
				return $this->redirect($this->generateUrl('DataGrabber_skills_en'));
			}
		}
		return $this->render('MVNerdsDataGrabberBundle:Skill:en.html.twig', array(
			'form' => $form->createView()
		));
	}
}
