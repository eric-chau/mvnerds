<?php

namespace MVNerds\SkeletonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\Model\Feed;
use MVNerds\SkeletonBundle\Form\Type\FeedType;

class SkeletonController extends Controller
{
    /**
	 * Page d'accueil du site
	 * 
     * @Route("/{_locale}", name="site_homepage", defaults={"_locale" = "fr"})
     */
    public function indexAction()
    {
		return $this->render('MVNerdsSkeletonBundle:Front:index.html.twig');
    }
	
	/**
	 * Page contenant un formulaire qui permet aux utilisateurs de poster un nouveau contenu
	 * 
	 * @Route("/{_locale}/create-new-content", name="new_feed")
	 */
	public function createFeedAction() 
	{
		$form = $this->createForm(new FeedType(), new Feed());
		
		$form->handleRequest($this->getRequest());
		
		if ($form->isValid()) {
			var_dump($form->getData()); die;
		}
		
		return $this->render('MVNerdsSkeletonBundle:Feed:add_new_feed_index.html.twig', array(
			'form' => $form->createView(),
		));
	}
}
