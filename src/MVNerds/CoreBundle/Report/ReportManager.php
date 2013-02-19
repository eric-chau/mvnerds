<?php

namespace MVNerds\CoreBundle\Report;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\UserReportPeer;
use MVNerds\CoreBundle\Model\UserReportQuery;
use MVNerds\CoreBundle\Report\IReport;
use MVNerds\CoreBundle\Model\UserReport;

class ReportManager
{
	/**
	 * Le nombre maximum de reports autorisés avant que l'objet ne passe en report_status : SOFT
	 */
	const MAX_REPORTS = 2;
	
	/**
	 * Récupère tous les objets ayant le report_status : SOFT ou HARD
	 */
	public function findAllReported()
	{
		return UserReportQuery::create()
			->groupByObjectId()
			->groupByObjectNamespace()
			->having('COUNT(*) > '. (self::MAX_REPORTS - 1))
		->find();
	}
	
	public function report(IReport $object ,$user, $desciption = null)
	{
		try {
			$report = $this->findByObjectAndUser($object, $user);
			throw new InvalidArgumentException('This user has already reported this object');
		} catch (\Exception $e) {
			$report = new UserReport();
			$report->setObjectId($object->getId());
			$report->setObjectNamespace(get_class($object));
			$report->setUser($user);
			$report->setDescription($desciption);
			$report->save();
		}
		
		$this->updateObjectReportStatus($object);
		
		return $report;
	}
	
	public function findByObjectAndUser(IReport $object, $user)
	{
		$reports = UserReportQuery::create()
			->joinWith('User')
			->add(UserReportPeer::OBJECT_NAMESPACE, get_class($object))
			->add(UserReportPeer::OBJECT_ID, $object->getId())
			->add(UserReportPeer::USER_ID, $user->getId())
		->find();
		
		if (null === $reports || count($reports) <= 0 )
		{
			throw new InvalidArgumentException('No reports foud for this object and user !');
		}

		return $reports[0];
	}
	
	private function updateObjectReportStatus(IReport $object)
	{
		if ($object->getReportStatus() != 'HARD' && $this->countReportsForObject($object) >= self::MAX_REPORTS) {
			$object->setReportStatus('SOFT');
			if (method_exists($object, 'keepUpdateDateUnchanged')) {
				$object->keepUpdateDateUnchanged();
			}
			
			$object->save();
		}
	}
	
	private function countReportsForObject(IReport $object)
	{
		return  UserReportQuery::create()
			->add(UserReportPeer::OBJECT_ID, $object->getId())
			->add(UserReportPeer::OBJECT_NAMESPACE, get_class($object))
		->count();
	}
}
