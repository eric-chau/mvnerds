<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/mailing")
 */
class MailingController extends Controller
{
	/**
	 * Envoi de mail à tous les membres actifs
	 *
	 * @Route("/", name="admin_mailing_index")
	 */
	public function indexAction()
	{
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$subject = $request->get('mailing-subject');
			$message = $request->get('mailing-message');
			
			/* $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
			$flashManager = $this->get('mvnerds.flash_manager');
			
			if ($subject && strlen($subject) > 5) {
				if ($message && strlen($message) > 10) {
					
					//$mails = $this->get('mvnerds.user_manager')->getActiveUsersMail()->toArray();
					
					$message = \Swift_Message::newInstance()
						->setContentType("text/html")
						->setSubject($subject)
						->setFrom('noreply@mvnerds.com', 'MVNerds')
						->setBcc(array('hani.yagoub@gmail.com', 'eriic.chau@gmail.com'))
						->setBody($this->renderView('MVNerdsAdminBundle:Mailing:mailing.html.twig', array(
							'message'	=> $message,
					)));
					$this->get('mailer')->send($message);
					$flashManager->setSuccessMessage('Flash.success.send_mail_contact');
				} else {
					$flashManager->setErrorMessage('Flash.error.send_mail_contact.message_invalid');
				}
			} else {
				$flashManager->setErrorMessage('Flash.error.send_mail_contact.subject_invalid');
			}
		}

		return $this->render('MVNerdsAdminBundle:Mailing:index.html.twig');
	}
}
