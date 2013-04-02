<?php

namespace MVNerds\TeamSeekerBundle\TeamSeeker;

use JMS\SecurityExtraBundle\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\ElophantAPI\ElophantAPIManager;

class TeamSeekerManager 
{
	private $elophantAPIManager;
	
	public function findTeamFromTagOrName($region, $tagOrName) 
	{
		try {
			$rawResponse = $this->elophantAPIManager->findTeamByTagOrName($region, $tagOrName);
		}
		catch (InvalidArgumentException $e) {
			var_dump('tag ou nom d\'équipe non reconnu !'); die;
		}
		
		//var_dump($rawResponse); die;
		
		$teamInfos = array(
			'tag'						=> $rawResponse->tag,
			'name'						=> $rawResponse->name,
			'ranked_team_5x5_league'	=> 'UNRANKED',
			'ranked_team_3x3_league'	=> 'UNRANKED'
		);
		
		//var_dump($teamInfos); die;
		$roster = array();
		foreach ($rawResponse->roster->memberList as $member) {
			$memberInfos = array(
				'summoner_name'				=> $member->playerName,
				'ranked_solo_5x5_league'	=> 'UNRANKED'
			);
			
			usleep(1700000);
			$leagues = $this->elophantAPIManager->getSummonerLeagues($region, $member->playerId);
			
			foreach ($leagues->summonerLeagues as $league) {
				if ($league->queue == 'RANKED_SOLO_5x5') {
					$memberInfos['ranked_solo_5x5_league'] = $league->tier . '_' . $league->requestorsRank;
					break;
				}
			}
			
			if ($rawResponse->roster->ownerId == $member->playerId) {
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
		}
		
		$teamInfos['roster'] = $roster;
		
		var_dump($teamInfos); die;
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
