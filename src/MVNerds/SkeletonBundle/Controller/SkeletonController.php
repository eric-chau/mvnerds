<?php

namespace MVNerds\SkeletonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
		$feeds = $this->get('mvnerds.feed_manager')->findLatest();
		
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
		
		return $this->render('MVNerdsSkeletonBundle:Feed:create_feed.html.twig', array(
			'form' => $form->createView(),
			'super_tags' => $superTags,
		));
	}
	
	/**
	 * 
	 * @param type $slug
	 * @Route("/{_locale]/feed/{slug}", name="feed_detail")
	 */
	public function feedDetailAction($slug)
	{
		return $this->render('MVNerdsSkeletonBundle:Feed:feed_detail.html.twig', array(
			'feed' => $this->get('mvnerds.feed_manager')->findBySlug($slug),
		));
	}
	
	/**
	 * Permet de récupérer, à partir d'une chaine de caractères de SuperTags fournie par l'utilisateur,
	 * tous les objets Feed associés
	 * 
	 * @Route("/{_locale}/get-feeds", name="get_feeds", options={"expose"=true})
	 */
	public function getFeedsAction()
	{
		$request = $this->getRequest();
		
		if  (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'La requête doit être effectuée en AJAX et en method POST !');
		}
		
		// Une chaine de caractères, saisie par l'utilisateur, composée de SuperTags
		$stringTags = $request->get('stringTags');
		// La page des Feeds que l'on souhaite obtenir
		$page = $request->get('page', 1);
		
		//On convertit la chaine de tags en tableau de "véritables" SuperTags
		$arrayTags = $this->get('mvnerds.super_tag_manager')->getUniqueNamesFromString($stringTags);
		
		/* @var $feedManager \MVNerds\SkeletonBundle\Feed\FeedManager */
		$feedManager = $this->get('mvnerds.feed_manager');
		
		//On récupère tous les feeds associés aux SuperTags présents dans le tableau $arrayTags
		$feeds = $feedManager->findBySuperTags($arrayTags, $page);
		
		return new Response(json_encode($feeds->toArray()));
	}
}
