<?php

namespace MVNerds\DataGrabberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/champions")
 */
class ChampionController extends Controller
{
	/**
	 * @Route("/", name="DataGrabber_champions_index")
	 */
	public function indexAction()
	{
		include(__DIR__ . '/../SimpleHtmlDom/simple_html_dom.php');
		
		$championsList = file_get_html('http://euw.leagueoflegends.com/champions');
		$maxChamp = 2;
		$i = 0;
		
		$championsTab = array();
		
		//On boucle sur les champions de la page de listing de tous les champions
		foreach ($championsList->find('div[id=list_view] table.champion_item tbody tr td.champion a') as $element)
		{
			$element->href;
			$champion = file_get_html('http://euw.leagueoflegends.com' . $element->href);
			
			$nameResult = $champion->find('div[id=page_header] div.page_header_text');
			$name = $nameResult[0]->plaintext;
			$championsTab[$name] = array();
			
			$stats = $champion->find('table.stats_table tbody');
			foreach ($stats[0]->find('tr') as $tr)
			{
				$statsTab = $tr->find('td');

				$statName = $statsTab[0]->plaintext;
				
				$statValue = $statsTab[1]->plaintext;
				$championsTab[$name][$statName]['statValue'] = $statValue;
				
				$statModifierTmp = $statsTab[2]->find('span.ability_per_level_stat');
				if ($statModifierTmp)
				{
					$statModifier = $statModifierTmp[0]->plaintext;
					$championsTab[$name][$statName]['statModifier'] = $statModifier;
				}
			}
			
			
			
			$i++;
			
			if ($i >= $maxChamp)
			{
				break;
			}
		}
		//die(var_dump($championsTab));

		return $this->render('MVNerdsDataGrabberBundle:Champion:index.html.twig', array(
			'champions' => $championsTab
		));
	}
}
