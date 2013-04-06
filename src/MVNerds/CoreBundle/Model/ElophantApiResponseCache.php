<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseElophantApiResponseCache;

class ElophantApiResponseCache extends BaseElophantApiResponseCache
{
	public function setResponse($v) 
	{		
		parent::setResponse(json_encode($v));
	}
	
	public function getResponse()
	{
		return json_decode(parent::getResponse());
	}
}
