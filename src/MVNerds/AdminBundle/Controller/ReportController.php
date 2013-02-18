<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/reports")
 */
class ReportController extends Controller
{
	/**
	 * Liste tous les objets qui ont le report_status soft
	 * 
	 * @Route("/", name="admin_reports_index")
	 */
	public function indexAction()
	{
		$reports = $this->get('mvnerds.report_manager')->findAllReported();
		$softReports = array();
		
		foreach ($reports as $report) {
			$objectNameSpace = $report->getObjectNamespace();
			$objectType = lcfirst(substr($objectNameSpace, strrpos($objectNameSpace, '\\') + 1));
			$object = $this->get('mvnerds.' . $objectType . '_manager')->findById($report->getObjectId());
			
			if (method_exists($object, 'getReportStatus') && $object->getReportStatus() == 'SOFT') {
				$softReports[] = $report;
			}
		}		
		
		return $this->render('MVNerdsAdminBundle:Report:index.html.twig', array(
			'reports' => $softReports
		));
	}
	
	/**
	 * Permet de changer le report status d un objet à HARD
	 * @Route("/status-to-hard", name="admin_reports_change_status_to_hard", options={"expose"=true})
	 */
	public function changeStatusToHard()
	{
		//Uniquement AJAX
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		//Récupération et vérification des paramètres
		$objectId= $request->get('object_id', null);
		$objectNameSpace = $request->get('object_namespace', null);
		if ($objectId == null || $objectNameSpace == null) {
			throw new HttpException(500, 'Missing parameters !');
		}
				
		$objectType = lcfirst(substr($objectNameSpace, strrpos($objectNameSpace, '\\') + 1));
		
		//On essaie de récupérer l objet depuis la BDD
		try {
			$object = $this->get('mvnerds.' . $objectType . '_manager')->findById($objectId);
		} catch (Exception $e) {
			throw new InvalidArgumentException('Object not found for id:`'. $objectId .'`');
		}
		
		$object->setReportStatus('HARD');
		if (method_exists($object, 'keepUpdateDateUnchanged')) {
			$object->keepUpdateDateUnchanged();
		}
		$object->save();
		
		return new Response(json_encode('Changement de statut pris en compte'));
	}
}
