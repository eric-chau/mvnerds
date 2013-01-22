<?php

namespace MVNerds\DataGrabberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\DataGrabberBundle\Form\Type\ChampionGrabberType;
use MVNerds\CoreBundle\Model\Skin;
/**
 * @Route("/skin")
 */
class SkinController extends Controller
{	
	/**
	 * Permet de récupérer les kills des champions depuis le site lol-fr en français
	 * 
	 * @Route("/", name="DataGrabber_skin_index")
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
				$championsList = file_get_html('http://www.mobafire.com/league-of-legends/champions')->find('#browse-build', 0);
				
				$errors = '';
				
				//Si la liste des champions a bien été récupérée
				if ($championsList->find('a.champ-box'))
				{
					/* @var $championManager \MVNerds\CoreBundle\Champion\ChampionManager */
					$championManager = $this->get('mvnerds.champion_manager');
					/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
					$flashManager = $this->get('mvnerds.flash_manager');
					/* @var $skinManager \MVNerds\CoreBundle\Skin\SkinManager */
					$skinManager = $this->get('mvnerds.skin_manager');
					
					//Liens de tous les champions
					$championsLinks = $championsList->find('a.champ-box');
					
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
						$championHtml = file_get_html('http://www.mobafire.com' . $championLink->href . '/skins');
						
						if ($championHtml->find('#champ-head'))
						{	
							//Récupération du nom du champion
							$h1 = $championHtml->find('#champ-head div.champ-wrap div.champ-info h1.champ-name', 0)->innertext;
							$h1Exploded = explode('<span>', $h1);
							$name = trim($h1Exploded[0]);
							
							try {
								$champion = $championManager->findByName($name);
							} catch ( \Exception $e) {
								$errors .= 'impossible de trouver le champion avec le nom : ' . $name . '<br />';
								continue;
							}
							
							$championSkinsHtml = $championHtml->find('#champ-skins div.skin-wrap');
							foreach ($championSkinsHtml as $championSkinHtml) {
								$skinName = $championSkinHtml->find('div.skin-hdr table tbody tr td', 0)->plaintext;
								try {
									$skinManager->findByName($skinName, 'en');
									$errors .= 'le skin avec le nom ' . $skinName . ' existe déjà<br />';
									continue;
								} catch (\Exception $e) {}
								
								$skinPicHtml = $championSkinHtml->find('div.skin-pic', 0);
								$skinPicStyle = $skinPicHtml->style;
								$skinPicUrlArr = array();
								preg_match('/url\(.*\)/', $skinPicStyle, $skinPicUrlArr);
								$skinPicUrl = preg_replace('/url\(|\)/', '', $skinPicUrlArr[0]);
								
								$skinCost = trim($championSkinHtml->find('div.skin-info div.cost', 0)->plaintext);
								
								$skin = new Skin();
								$skin->setChampion($champion);
								$skin->setLocale('en');
								$skin->setName($skinName);
								$skin->setCost($skinCost);
								$skin->save();
								
								try {
									file_put_contents(__DIR__ . '/../../../../web/images/skins/'. $skin->getSlug() .'.png', file_get_contents('http://www.mobafire.com' . $skinPicUrl));
								} catch (\Exception $e) {
									$errors .= 'impossible de récupérer l\'image du champion : http://www.mobafire.com' . $skinPicUrl . '<br />';
								}
							}
						} else {
							$errors .= 'impossible d\'accéder à la page du champion : ' . $championLink->href . '<br />';
						}
					}
				}
				else
				{
					// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
					$flashManager->setErrorMessage('La liste des champions n\'a pas pu être récupérée');
					// On redirige l'utilisateur vers la liste des utilisateurs
					return $this->redirect($this->generateUrl('DataGrabber_skin_index'));
				}
				
				// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
				$flashManager->setSuccessMessage('Les skins ont bien été grabbés. <br />Errors : <br />'.$errors);
				// On redirige l'utilisateur vers la liste des utilisateurs
				return $this->redirect($this->generateUrl('DataGrabber_skin_index'));
			}
		}
		return $this->render('MVNerdsDataGrabberBundle:Skin:index.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Permet de récupérer les kills des champions depuis le site leaguecraft.com en anglais
	 * 
	 * @Route("/en", name="DataGrabber_skin_en")
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
					return $this->redirect($this->generateUrl('DataGrabber_skin'));
				}
				
				// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
				$flashManager->setSuccessMessage('Les skins ont bien été grabbés <br />'.$errors);
				// On redirige l'utilisateur vers la liste des utilisateurs
				return $this->redirect($this->generateUrl('DataGrabber_skin'));
			}
		}
		return $this->render('MVNerdsDataGrabberBundle:Skin:index.html.twig', array(
			'form' => $form->createView()
		));
	}
}
