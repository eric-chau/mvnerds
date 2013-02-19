<?php

namespace MVNerds\ReportBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

use MVNerds\CoreBundle\Model\UserReport;

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
		$objectType = $request->get('object_type', null);
		$descriptionIndex = $request->get('description_index', null);
		if ($objectSlug == null || $objectType == null) {
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
			$object = $this->get('mvnerds.' . $objectType . '_manager')->findBySlug($objectSlug);
		} catch (Exception $e) {
			throw new InvalidArgumentException('Object not found for slug:`'. $objectSlug .'`');
		}
		
		//On essaie d effectuer le report de $object par $user
		try {
			$this->get('mvnerds.report_manager')->report($object, $user, $description);
			return new Response(json_encode('Report pris en compte'));
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 400);
		}
	}
}
