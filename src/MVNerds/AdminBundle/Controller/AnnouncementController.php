<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Route("/announcement")
 */
class AnnouncementController extends Controller
{

	const MVN_ANNOUNCEMENT_KEY = 'mvn_announcement';
	
	/**
	 * Liste tous les tags de la plateforme
	 *
	 * @Route("/", name="admin_announcement_index")
	 */
	public function indexAction()
	{
		$announcement = apc_fetch(self::MVN_ANNOUNCEMENT_KEY);
		
		return $this->render('MVNerdsAdminBundle:Announcement:index.html.twig', array(
			'announcement' => $announcement != false ? $announcement : ''
		));
	}
	
	/**
	 * Permet de créer un nouveau tag
	 * 
	 * @Route("/store", name="admin_announcement_store")
	 */
	public function storeAction()
	{
		$request = $this->getRequest();	
		if (!$request->isMethod('POST')) {
			throw new HttpException(500, 'Request must be POST method!');
		}
		
		$announcement = $request->get('announcement', null);
		
		if ($announcement && $announcement != '') {
			apc_store(self::MVN_ANNOUNCEMENT_KEY, $announcement);
		} else {
			apc_delete(self::MVN_ANNOUNCEMENT_KEY);
		}
		
		return $this->redirect($this->generateUrl('admin_announcement_index'));
	}
}
