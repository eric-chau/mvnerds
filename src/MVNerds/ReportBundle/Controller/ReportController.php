<?php

namespace MVNerds\ReportBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

use MVNerds\CoreBundle\Model\UserReport;
use MVNerds\CoreBundle\Report\IReport;

/**
 * @Route("/Report")
 */
class ReportController extends Controller
{
	/**
	 * @Route("/report", name="report_report", options={"expose"=true})
	 */
	public function reportObjectAction()
	{
		//Uniquement AJAX
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		//Récupération et vérification des paramètres
		$objectSlug = $request->get('object_slug', null);
		$objectId = $request->get('object_id', null);
		$objectType = $request->get('object_type', null);
		$descriptionIndex = $request->get('description_index', null);
		if (($objectSlug == null && $objectId == null) || $objectType == null) {
			throw new HttpException(500, 'Missing parameters !');
		}
		$description = null;
		foreach(UserReport::$REPORT_MOTIVES as $key => $value) {
			if (isset($value[$descriptionIndex])) {
				$description = $value[$descriptionIndex];
				break;
			}
		}
		
		//Récupération de l utilisateur courant
		$user = $this->getUser();
		
		//On essaie de récupérer l objet depuis la BDD
		try {
			if ($objectSlug != null) {
				$object = $this->get('mvnerds.' . $objectType . '_manager')->findBySlug($objectSlug);
			} elseif ($objectId != null) {
				$object = $this->get('mvnerds.' . $objectType . '_manager')->findById($objectId / 47);
			}
		} catch (Exception $e) {
			throw new InvalidArgumentException('Object not found for slug:`'. $objectSlug .'` or for ID: `'. $objectId . '`');
		}
		
		//On essaie d effectuer le report de $object par $user
		try {
			$this->get('mvnerds.report_manager')->report($object, $user, $description);
			return new Response(json_encode('Report pris en compte'));
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 400);
		}
	}
	
	public function renderReportBlockAction(IReport $object, $objectType, $isDetailed = false, $hasSlug = true)
	{
		/* @var $reportManager \MVNerds\CoreBundle\Report\ReportManager */
		$reportManager  = $this->get('mvnerds.report_manager');
		
		if (($user = $this->getUser())) {
			try {
				$reportManager->findByObjectAndUser($object, $user);
				$canReport = false;
			} catch (\Exception $e) {
				$canReport = true;
			}
		} else {
			$canReport = false;
		}
		
		$params = array(
			'can_report'	=> $canReport,
			'object_type'	=> $objectType
		);
		
		if ($hasSlug) {
			$params['object_slug'] = $object->getSlug();
		}
		else {
			$params['object_id'] = $object->getId() * 47;
		}
		
		if ($isDetailed) {
			$params['report_motives'] = UserReport::$REPORT_MOTIVES[$objectType];
			
			return $this->render('MVNerdsReportBundle:Report:detailed_report_block.html.twig', $params);
		} else {
			return $this->render('MVNerdsReportBundle:Report:simple_report_block.html.twig', $params);
		}
	}
}
