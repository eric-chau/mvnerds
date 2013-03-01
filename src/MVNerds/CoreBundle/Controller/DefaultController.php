<?php

namespace MVNerds\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     */
    public function indexAction($name)
    {
		/*$champion = ChampionQuery::create()
			->joinWith('Spell')
			->add(ChampionPeer::NAME, 'Akali')
			->add(ChampionPeer::NAME, 'Akali')
			->add(ChampionPeer::NAME, 'Akali')
		->findOne();
		
		$championManager = $this->get('mvnerds.champion_manager');
		
		$champion = $championManager->findChampionById(1);
		
		if (null != $champion) 
		{
			
		}

		$champion->setName('Akali');
		
		$champion->save();
		*/
        return new Response('coucou', 200);
	}
}
