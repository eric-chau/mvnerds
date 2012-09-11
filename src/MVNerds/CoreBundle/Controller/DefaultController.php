<?php

namespace MVNerds\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use MVNerds\CoreBundle\Model\Champion;
use MVNerds\CoreBundle\Model\ChampionQuery;
use MVNerds\CoreBundle\Model\ChampionPeer;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
		$champion = ChampionQuery::create()
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
		
        return array(
			'name' => $name
		);
    }
	
	public function isCurrentActionValid(Champion $champion)
	{
		$champion;
	}
}
