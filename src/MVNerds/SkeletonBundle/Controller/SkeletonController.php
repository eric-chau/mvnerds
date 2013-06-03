<?php

namespace MVNerds\SkeletonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SkeletonController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
		/* @var $feedManager \MVNerds\SkeletonBundle\Feed\FeedManager */
//		$feedManager = $this->get('mvnerds.feed_manager');
//		
//		$superTags = array('volibear', 'jungle');
//		
//		try {
//			var_dump($feedManager->findBySuperTags($superTags));
//		} catch (\Exception $e) {
//			die('ko');
//		}
		
        return $this->render('MVNerdsSkeletonBundle::layout.html.twig');
    }
}
