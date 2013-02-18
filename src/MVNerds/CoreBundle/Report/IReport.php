<?php

namespace MVNerds\CoreBundle\Report;

interface IReport
{	
	public function getReportStatus();
	
	public function setReportStatus($v);
}
