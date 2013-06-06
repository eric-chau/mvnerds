<?php

namespace MVNerds\SkeletonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SkeletonController extends Controller
{
    /**
     * @Route("/", name="site_homepage")
     */
    public function indexAction()
    {
		/* @var $feedManager \MVNerds\SkeletonBundle\Feed\FeedManager */
		$feedManager = $this->get('mvnerds.feed_manager');
		
		/* @var $superTagManager \MVNerds\SkeletonBundle\SuperTag\SuperTagManager */
		$superTagManager = $this->get('mvnerds.super_tag_manager');
		
		//Simulation de saisie de l'utilisateur dans la barre de recherche
		$userInput = 'gp, mid';
		
		//Tableau contenant les 'UNIQUE_NAME' des tags associés aux feeds que l'on veut récupérer.
		$superTags = $superTagManager->getUniqueNamesFromString($userInput);
		
		try {
			//Récupération de tous les Feeds avec des tags inclus dans le tableau passé en paramètre
			$feeds = $feedManager->findBySuperTags($superTags);
			foreach($feeds as $feed) {
				var_dump($feed->getTitle());
			}
		} catch(\Exception $e){}
		
		
        return $this->render('MVNerdsSkeletonBundle::layout.html.twig');
    }
}
