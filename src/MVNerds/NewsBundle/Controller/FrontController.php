<?php

namespace MVNerds\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/news")
 */
class FrontController extends Controller
{

	/**
	 * @Route("/", name="news_index")
	 */
	public function indexAction()
	{
		if ($this->get('security.context')->isGranted('ROLE_NEWSER'))
		{
			$news = $this->get('mvnerds.news_manager')->findAllNotPrivate();
		} else {
			$news = $this->get('mvnerds.news_manager')->findAllPublic();
		}
		
		return $this->render('MVNerdsNewsBundle:Front:list_index.html.twig', array(
			'news'	=> $news
		));
	}
	
	/**
	 * @Route("/{slug}", name="news_detail")
	 */
	public function viewAction($slug)
	{
		try {
			/* @var $news \MVNerds\CoreBundle\Model\News */
			if ($this->get('security.context')->isGranted('ROLE_NEWSER'))
			{
				$news = $this->get('mvnerds.news_manager')->findBySlug($slug);
			} else {
				$news = $this->get('mvnerds.news_manager')->findPublicBySlug($slug);
			}
			
			$news->setView($news->getView() + 1);
			$news->save();
			$news->setContent($this->get('mvnerds.bbcode_manager')->BBCode2Html($news->getContent()));
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('launch_site_front'));
		}
		return $this->render('MVNerdsNewsBundle:Front:view_index.html.twig', array(
			'news' => $news
		));
	}
}
