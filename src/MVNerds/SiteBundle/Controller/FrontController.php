<?php

namespace MVNerds\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use MVNerds\AdminBundle\Controller\AnnouncementController;

class FrontController extends Controller
{	
	/**
	 * @Route("/maintenance", name="site_maintenance")
	 */
	public function maintenanceAction()
	{
		if (!$this->container->getParameter('maintenance_in_progress')) {
			return $this->redirect($this->generateUrl('site_homepage'));
		}
		
		return $this->render('MVNerdsSiteBundle:Front:maintenance_index.html.twig');
	}
	
    /**
     * @Route("/{_locale}", name="site_homepage", defaults={"_locale" = "fr"})
     */
    public function indexAction()
    {
		$news = null;
		if ($this->get('security.context')->isGranted('ROLE_NEWSER'))
		{
			$news = $this->get('mvnerds.news_manager')->findNotPrivateHighlights();
		} else {
			$news = $this->get('mvnerds.news_manager')->findPublicHighlights();
		}
		
        return $this->render('MVNerdsSiteBundle:Front:index.html.twig', array(
			'lastest_items_builds'	=> $this->get('mvnerds.item_build_manager')->findLatestBuilds(),
			'popular_items_builds'	=> $this->get('mvnerds.item_build_manager')->findMostDownloadedBuilds(),
			'newest_videos'			=> $this->get('mvnerds.video_manager')->findNewestVideos(),
			'most_viewed_videos'	=> $this->get('mvnerds.video_manager')->findMostViewedVideos(),
			'news'					=> $news
		));
    }

	/**
	 * Renvoi la liste des champions formater pour l'affichage dans le footer du site
	 */
	public function renderFooterChampionAction()
	{
		return $this->render('MVNerdsSiteBundle:Common:footer_champion_list.html.twig', array(
					'champions' => $this->get('mvnerds.champion_manager')->findAll()
				));
	}

	/**
	 * Affiche la rotation des champions la plus récente
	 */
	public function renderChampionRotationAction()
	{
		return $this->render('MVNerdsSiteBundle:Common:champion_rotation_list.html.twig', array(
					'rotation' => $this->get('mvnerds.champion_rotation_manager')->findLast()
				));
	}

	/**
	 * @Route("/test/propel")
	 */
	public function testAPIAction()
	{
		$rankedStats = new \MVNerds\CoreBundle\GameAccount\RankedStats();
		$rankedStats->setSolo5x5League('PLATINIUM', 'V');
		
		return $this->render('::layout.html.twig');
	}

	/**
	 * Permet d'accéder à la page de contact
	 * 
	 * @Route("/{_locale}/contact-us", name="site_contact_us", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function contactUsAction()
	{
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$mail = $request->get('contact-mail');
			$subject = $request->get('contact-subject');
			$message = $request->get('contact-message');
			$category = $request->get('contact-category');
			
			/* $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
			$flashManager = $this->get('mvnerds.flash_manager');
			
			if ($mail &&  strlen($mail) > 4) {
				if ($subject && strlen($subject) > 5) {
					if ($message && strlen($message) > 10) {
						$message = \Swift_Message::newInstance()
							->setSubject('Contact from : '.$mail)
							->setFrom($mail, $mail)
							->setTo('hani.yagoub@gmail.com')
							->setBody($this->renderView('MVNerdsSiteBundle:Footer:contact_mail.txt.twig', array(
								'mail'		=> $mail,
								'subject'	=> $subject,
								'message'	=> $message,
								'category'	=> $category
						)));
						$this->get('mailer')->send($message);
						$flashManager->setSuccessMessage('Flash.success.send_mail_contact');
					} else {
						$flashManager->setErrorMessage('Flash.error.send_mail_contact.message_invalid');
					}
				} else {
					$flashManager->setErrorMessage('Flash.error.send_mail_contact.subject_invalid');
				}
			} else {
				$flashManager->setErrorMessage('Flash.error.send_mail_contact.mail_invalid');
			}
		}
		
		return $this->render('MVNerdsSiteBundle:Footer:contact_us.html.twig');
	}
	
	/**
	 * Permet d'accéder à la page des mentions légales
	 * 
	 * @Route("/{_locale}/legal", name="site_legal", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function legalAction()
	{
		return $this->render('MVNerdsSiteBundle:Footer:legal.html.twig');
	}
	
	/**
	 * 
	 * @Route("/{_locale}/statistics", name="site_statistics")
	 */
	public function statisticsAction()
	{
		try{
			$itemBuildsTotalDownloaded = $this->get('mvnerds.statistics_manager')->findByUniqueName('ITEM_BUILDS_TOTAL_DOWNLOADED')->getValue();
		} catch(\Exception $e) {
			$itemBuildsTotalDownloaded = 0;
		}
		
		return $this->render('MVNerdsSiteBundle:Footer:statistics.html.twig', array(
			'item_builds_total_downloaded'	=> $itemBuildsTotalDownloaded
		));
	}
	
	/**
	 * 
	 * @Route("/{_locale}/about-us", name="site_about_us")
	 */
	public function aboutUsAction()
	{		
		return $this->render('MVNerdsLaunchSiteBundle:Footer:about_us.html.twig');
	}
	
	/**
	 * Permet d'accéder à la page de la politique de confidentialité
	 * 
	 * @Route("/{_locale}/privacy-policy", name="site_privacy_policy", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function privacyPolicyAction()
	{
		return $this->render('MVNerdsSiteBundle:Footer:privacy_policy.html.twig');
	}
	
	/**
	 * Permet d'accéder à la page de conditions générales d'utilisation
	 * 
	 * @Route("/{_locale}/terms-of-use", name="site_terms_of_use", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function termsOfUseAction()
	{
		return $this->render('MVNerdsSiteBundle:Footer:terms_of_use.html.twig');
	}
	
	/**
	 * Permet d'afficher un message d'annonce mvnerds lorsqu'il a été saisi depuis l admin
	 */
	public function renderAnnouncementAction()
	{
		if (($announcement = apc_fetch(AnnouncementController::MVN_ANNOUNCEMENT_KEY))) {
			return $this->render('MVNerdsSiteBundle:Common:announcement.html.twig', array(
				'announcement' => $announcement
			));
		}
		
		return new Response();
	}
}
