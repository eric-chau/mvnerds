<?php

namespace MVNerds\CoreBundle\Model;

use Exception;

use MVNerds\CoreBundle\Model\om\BaseTeamSeekerCache;

class TeamSeekerCache extends BaseTeamSeekerCache
{
	
	
	public function setRoster($v)
	{
		if (!is_array($v)) {
			throw new Exception('Given parameter must be type of array!');
		}
		
		parent::setRoster(json_encode($v));
	}
	
	public function getRoster()
	{
		return json_decode(parent::getRoster(), true);
	}
	
	public function setData($v) 
	{
		if (!is_array($v)) {
			throw new Exception('Given parameter must be type of array!');
		}
		
		parent::setData(json_encode($v));
	}
	
	public function getData()
	{
		return json_decode(parent::getData(), true);
	}
	
	public function setMatchHistory(array $v)
	{
		$data = $this->getData();
		$data['match_history'] = $v;
		
		$this->setData($data);
	}
	
	public function getMatchHistory()
	{
		$data = $this->getData();
		
		return $data['match_history'];
	}
}
