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
	 * @Route("/", name="front_news_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsNewsBundle:Front:index.html.twig', array(
			'news' => $this->get('mvnerds.news_manager')->findLatestNews()
		));
	}
	
	/**
	 * @Route("/{slug}/view", name="front_news_view")
	 */
	public function viewAction($slug)
	{
		try {
			/* @var $news \MVNerds\CoreBundle\Model\News */
			$news = $this->get('mvnerds.news_manager')->findBySlug($slug);
			$news->setContent($this->get('mvnerds.bbcode_manager')->BBCode2Html($news->getContent()));
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('front_news_index'));
		}
		return $this->render('MVNerdsNewsBundle:Front:view_index.html.twig', array(
			'news' => $news
		));
	}
}
