<?php

namespace MVNerds\SkeletonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

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
		$feeds = $this->get('mvnerds.feed_manager')->findAll();
		
		return $this->render('MVNerdsSkeletonBundle:Front:index.html.twig', array(
			'feeds' => $feeds,
		));
    }
	
	/**
	 * Page contenant un formulaire qui permet aux utilisateurs de poster un nouveau contenu
	 * 
	 * @Route("/{_locale}/create-new-content", name="new_feed")
	 */
	public function createFeedAction() 
	{
		$form = $this->createForm(new FeedType($this->get('translator')), new Feed());
		
		$form->handleRequest($this->getRequest());
		
		if ($form->isValid()) {
			$this->get('mvnerds.feed_manager')->createFeed($form->getData(), $this->getUser(), $form->get('feed_tags')->getData());
			
			return $this->redirect($this->generateUrl('site_homepage'));
		}
		
		$superTags = array();
		foreach ($this->get('mvnerds.super_tag_manager')->findAll(true) as $superTag) {
			$superTags[] = $superTag->getLabel();
		}
		
		return $this->render('MVNerdsSkeletonBundle:Feed:add_new_feed_index.html.twig', array(
			'form' => $form->createView(),
			'super_tags' => $superTags,
		));
	}
	
	/**
	 * Permet de récupérer, à partir d'une chaine de caractères de SuperTags fournie par l'utilisateur,
	 * tous les objets Feed associés
	 * 
	 * @param string $stringTags une chaine de caractères, saisie par l'utilisateur, composée de SuperTags
	 * 
	 * @Route("/{_locale}/get-feeds/{stringTags}", name="get_feeds", options={"expose"=true})
	 */
	public function getFeedsAction($stringTags)
	{
		//On convertit la chaine de tags en tableau de "véritables" SuperTags
		$arrayTags = $this->get('mvnerds.super_tag_manager')->getUniqueNamesFromString($stringTags);
		
		//On récupère tous les feeds associés aux SuperTags présents dans le tableau $arrayTags
		$feeds = $this->get('mvnerds.feed_manager')->findBySuperTags($arrayTags);
		
		return new Response(json_encode($feeds->toArray()));
	}
}
