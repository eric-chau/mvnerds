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
		$team = TeamSeekerCacheQuery::create()
			->where(TeamSeekerCachePeer::NAME . '= ?', $tagOrName)
			->_or()
			->where(TeamSeekerCachePeer::TAG . '= ?', $tagOrName)
			->add(TeamSeekerCachePeer::REGION, $region)
		->findOne();
		
		if (null == $team || ($team->getUpdateTime()->getTimestamp() + 30 * 60) < time()) {
			$team = $this->retrieveTeamByTagOrNameFromElophantAPI($region, $tagOrName, $team);
		}

		return $team;
	}
	
	private function retrieveTeamByTagOrNameFromElophantAPI($region, $tagOrName, TeamSeekerCache $team = null)
	{
		// Si $team est égal à null, on créé un nouvelle objet TeamSeekerCache
		if (null == $team) {
			$team = new TeamSeekerCache();
		}
		
		// On essaye de récupérer les informations conernant l'équipe à partir du tag ou du nom d'équipe
		try {
			$rawResponse = $this->elophantAPIManager->findTeamByTagOrName($region, $tagOrName);
		}
		catch (InvalidArgumentException $e) {
			// Si l'exception est de type InvalidArugmentException cela signifie que le tag ou le nom fourni n'est pas reconnu pour la région spécifiée
			var_dump('tag ou nom d\'ÃƒÂ©quipe non reconnu !'); die;
		}
		
		// On rassemble toutes les informations conernant l'équipes et ses membres dans un même tableau
		$teamInfos = array(
			'tag'						=> $rawResponse->tag,
			'name'						=> $rawResponse->name,
			'ranked_team_5x5_league'	=> 'UNRANKED',
			'ranked_team_3x3_league'	=> 'UNRANKED'
		);
		
		$roster = array();
		// On commence par parcourir tous les joueurs que comportent l'équipe pour les rassembler dans un seul et même tableau
		foreach ($rawResponse->roster->memberList as $member) {
			$memberInfos = array(
				'summoner_name'				=> $member->playerName,
				'summoner_id'				=> $member->playerId,
				'ranked_solo_5x5_league'	=> 'UNDEFINED'
			);
			
			// On se base sur le propriétaire de l'équipe et ses ligues pour retrouver la ligue et la division de l'ÃƒÂ©quipe en question
			if ($rawResponse->roster->ownerId == $member->playerId) {
				
				try {
				$leagues = $this->elophantAPIManager->getSummonerLeagues($region, $member->playerId);
				}
				catch (InvalidArgumentException $e) {
					$memberInfos['ranked_solo_5x5_league'] = 'UNRANKED';
					if ($rawResponse->roster->ownerId == $member->playerId) {
						$memberInfos['owner'] = true;
					}
					
					$roster[] = $memberInfos;
					continue;
				}
				
				// Si on ne trouve pas de ligue pour le capitaine cela signifie qu'il n'es pas classé en soloQ
				if ($memberInfos['ranked_solo_5x5_league'] == 'UNDEFINED') {
					$memberInfos['ranked_solo_5x5_league'] = 'UNRANKED';
				}
				
				// Seulement pour le capitaine de l'équipe, on en profite pour récupérer son classement en soloQ
				foreach ($leagues->summonerLeagues as $league) {
					if ($league->queue == 'RANKED_SOLO_5x5') {
						$memberInfos['ranked_solo_5x5_league'] = $league->tier . '_' . $league->requestorsRank;
						break;
					}
				}
				
				// On les parcourt une ÃƒÂ  une pour connaÃƒÂ®tre la file et l'ÃƒÂ©quipe concernÃƒÂ©es
				foreach ($leagues->summonerLeagues as $league) {
					// On test si c'est une file qui concerne l'ÃƒÂ©quipe que l'on recherche
					if ($teamInfos['name'] == $league->requestorsName) {
						// Si oui on test si c'est pour la file Equipe ClassÃƒÂ©e 5vs5
						if ('RANKED_TEAM_5x5' == $league->queue) {
							$teamInfos['ranked_team_5x5_league'] = $league->tier . '_' . $league->requestorsRank;
						}
						// Sinon c'est la file 3vs3
						else {
							$teamInfos['ranked_team_3x3_league'] = $league->tier . '_' . $league->requestorsRank;
						}
					}
				}
				
				$memberInfos['owner'] = true;
			}
			
			// On ajoute le joueur muni de ses informations dans le tableau $roster
			$roster[] = $memberInfos;
		}
		
		$matchHistory = array();
		// Parcours de l'historique des 20 (max) derniers matchs de l'équipe
		foreach ($rawResponse->matchHistory as $match) {
			$matchHistory[] = array(
				'game_id'		=> $match->gameId,
				'is_5v5'		=> $match->mapId == 1,
				'win'			=> $match->win,
				'opposing_team' => $match->opposingTeamName,
				'kills'			=> $match->kills,
				'deaths'		=> $match->deaths
			);
		}
		
		// On hydrate l'objet TeamSeekerCache
		$team->setTag($teamInfos['tag']);
		$team->setName($teamInfos['name']);
		$team->setRegion($region);
		$team->setRanked5x5League($teamInfos['ranked_team_5x5_league']);
		$team->setRanked3x3League($teamInfos['ranked_team_3x3_league']);
		$team->setRoster($roster);
		$team->setMatchHistory($matchHistory);
		
		$team->save();
		
		return $team;
	}
	
	public function setElophantAPIManager(ElophantAPIManager $manager)
	{
		$this->elophantAPIManager = $manager;
	}
	
	/**
	 * 
	 * @param type $region
	 * @param type $tagOrName
	 * @return boolean|string
	 */
	public function updatePlayerSoloQLeagueIfNeeded($region, $tagOrName) {
		$team = $this->findTeamByTagOrName($region, $tagOrName);
		
		$roster = $team->getRoster();
		$player = null;
		$playerKey = null;
		foreach ($roster as $key => $member) {
			if ($member['ranked_solo_5x5_league'] == 'UNDEFINED') {
				$player = $member;
				$playerKey = $key;
				break;
			}
		}
		
		if (null == $player) {
			return false;
		}
		
		try {
			$leagues = $this->elophantAPIManager->getSummonerLeagues($region, $player['summoner_id']);
		}
		catch (InvalidArgumentException $e) {
			$player['ranked_solo_5x5_league'] = 'UNRANKED';
		}
		
		if ($player['ranked_solo_5x5_league'] == 'UNDEFINED') {
			foreach ($leagues->summonerLeagues as $league) {
				if ($league->queue == 'RANKED_SOLO_5x5') {
					$player['ranked_solo_5x5_league'] = $league->tier . '_' . $league->requestorsRank;
					break;
				}
			}

			if ($player['ranked_solo_5x5_league'] == 'UNDEFINED') {
				$player['ranked_solo_5x5_league'] = 'UNRANKED';
			}
		}
		
		$roster[$playerKey] = $player;
		
		$team->setRoster($roster);
		$team->save();
		
		return $player;
	}
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
