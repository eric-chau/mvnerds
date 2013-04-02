<?php

namespace MVNerds\TeamSeekerBundle\TeamSeeker;

use JMS\SecurityExtraBundle\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\ElophantAPI\ElophantAPIManager;
use MVNerds\CoreBundle\Model\TeamSeekerCache;
use MVNerds\CoreBundle\Model\TeamSeekerCachePeer;
use MVNerds\CoreBundle\Model\TeamSeekerCacheQuery;

class TeamSeekerManager 
{
	private $elophantAPIManager;
	
	public function findTeamByTagOrName($region, $tagOrName) 
	{
		/*$team = TeamSeekerCacheQuery::create()
			->where(TeamSeekerCachePeer::NAME . '= ?', $tagOrName)
			->_or()
			->where(TeamSeekerCachePeer::TAG . '= ?', $tagOrName)
			->add(TeamSeekerCachePeer::REGION, $region)
		->findOne();
		
		if (null == $team) {*/
			$team = $this->retrieveTeamByTagOrNameFromElophantAPI($region, $tagOrName);
		//}
				
		var_dump($team); die;
		return $team;
	}
	
	private function retrieveTeamByTagOrNameFromElophantAPI($region, $tagOrName, TeamSeekerCache $team = null)
	{
		if (null == $team) {
			$team = new TeamSeekerCache();
		}
		
		try {
			$rawResponse = $this->elophantAPIManager->findTeamByTagOrName($region, $tagOrName);
		}
		catch (InvalidArgumentException $e) {
			var_dump('tag ou nom d\'équipe non reconnu !'); die;
		}
		
		$teamInfos = array(
			'tag'						=> $rawResponse->tag,
			'name'						=> $rawResponse->name,
			'ranked_team_5x5_league'	=> 'UNRANKED',
			'ranked_team_3x3_league'	=> 'UNRANKED'
		);
		
		$roster = array();
		foreach ($rawResponse->roster->memberList as $member) {
			$memberInfos = array(
				'summoner_name'				=> $member->playerName,
				'ranked_solo_5x5_league'	=> 'UNRANKED'
			);
			
			$leagues = $this->elophantAPIManager->getSummonerLeagues($region, $member->playerId);
			
			foreach ($leagues->summonerLeagues as $league) {
				if ($league->queue == 'RANKED_SOLO_5x5') {
					$memberInfos['ranked_solo_5x5_league'] = $league->tier . '_' . $league->requestorsRank;
					break;
				}
			}
			
			if ($rawResponse->roster->ownerId == $member->playerId) {
				$leagues = $this->elophantAPIManager->getSummonerLeagues($region, $member->playerId);
				$roster['owner'] = $memberInfos;
				
				foreach ($leagues->summonerLeagues as $league) {
					if ($teamInfos['name'] == $league->requestorsName) {
						if ('RANKED_TEAM_5x5' == $league->queue) {
							$teamInfos['ranked_team_5x5_league'] = $league->tier . '_' . $league->requestorsRank;
						}
						else {
							$teamInfos['ranked_team_3x3_league'] = $league->tier . '_' . $league->requestorsRank;
						}
					}
				}
			}
			else {
				$roster[] = $memberInfos;
			}
				//$roster[] = $memberInfos;
		}
		
		$teamInfos['roster'] = $roster;
		
		$team->setTag($teamInfos['tag']);
		$team->setName($teamInfos['name']);
		$team->setRegion($region);
		$team->setRanked5x5League($teamInfos['ranked_team_5x5_league']);
		$team->setRanked3x3League($teamInfos['ranked_team_3x3_league']);
		$team->setRoster($teamInfos['roster']);
		
		$team->save();
		
		return $team;
	}
	
	public function setElophantAPIManager(ElophantAPIManager $manager)
	{
		$this->elophantAPIManager = $manager;
	}
	
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
