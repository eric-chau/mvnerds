<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\Form\Type\NewsType;

/**
 * @Route("/news")
 */
class NewsController extends Controller
{
	/**
	 * Liste toutes les news de la base
	 *
	 * @Route("/", name="admin_news_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsAdminBundle:News:index.html.twig', array(
			'news' => $this->get('mvnerds.news_manager')->findAll()
		));
	}
	
	/**
	 * Permet de créer une nouvelle news
	 *
	 * @Route("/create", name="admin_news_create")
	 */
	public function createAction()
	{
		$form = $this->createForm(new NewsType());

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				/* @var $news \MVNerds\CoreBundle\Model\News */
				$news = $form->getData();
				
				$user = $this->get('security.context')->getToken()->getUser();
				
				$news->setUser($user);
				// Persistance de l'objet en base de données
				$this->get('mvnerds.news_manager')->save($news);

				// Ajout d'un message de flash pour notifier que le champion a bien été ajouté
				$this->get('mvnerds.flash_manager')->setSuccessMessage('La news ' . $news->getTitle() . ' a bien été ajouté.');

				// On redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('admin_news_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:News:create_news_form.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Permet de supprimer une news
	 *
	 * @Route("/{slug}/supprimer", name="admin_news_supprimer")
	 */
	public function deleteAction($slug)
	{
		$this->get('mvnerds.news_manager')->deleteBySlug($slug);

		return new Response(json_encode(true));
	}
	
	/**
	 * Permet d editer une news
	 *
	 * @Route("/{slug}/edit", name="admin_news_edit")
	 */
	public function editAction($slug)
	{
		try {
			$news = $this->get('mvnerds.news_manager')->findBySlug($slug);
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('admin_news_index'));
		}
		$form = $this->createForm(new NewsType(), $news);
		
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$news = $form->getData();
				
				$this->get('mvnerds.news_manager')->save($news);

				// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
				$this->get('mvnerds.flash_manager')->setSuccessMessage('Les informations de la news ' . $news->getTitle() . ' ont bien été mises à jour.');

				// On redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('admin_news_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:News:edit_news_form.html.twig', array(
			'form'		=> $form->createView(),
			'news'		=> $news
		));
	}
	
	/**
	 * @Route("/parse-bbcode", name="bbcode_parse", options={"expose"=true})
	 */
	public function parseBBCodeAction()
	{
		$request = $this->getRequest();
		$data = $request->get('data');
		
		$style='<link href="/css/news/view.css" rel="stylesheet" type="text/css">
			<link href="/css/bootstrap.css" rel="stylesheet" type="text/css">';
		
		return new \Symfony\Component\HttpFoundation\Response($style . '<div id="news-container">' .$this->get('mvnerds.bbcode_manager')->BBCode2Html($data) . '</div>');
	}
}