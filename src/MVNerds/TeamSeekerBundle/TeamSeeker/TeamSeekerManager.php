<?php

namespace MVNerds\TeamSeekerBundle\TeamSeeker;

use Exception;
use JMS\SecurityExtraBundle\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\ElophantAPI\ElophantAPIManager;
use MVNerds\TeamSeekerBundle\Exception\InvalidTeamNameOrTagException;
use MVNerds\CoreBundle\Exception\ServiceUnavailableException;
use MVNerds\CoreBundle\Model\TeamSeekerCache;
use MVNerds\CoreBundle\Model\TeamSeekerCachePeer;
use MVNerds\CoreBundle\Model\TeamSeekerCacheQuery;

class TeamSeekerManager 
{
	private $elophantAPIManager;
	
	public function findTeamByTagOrName($region, $tagOrName) 
	{		
		$team = $this->findInCacheByTagOrName($region, $tagOrName);
		
		if (null == $team || ($team->getUpdateTime()->getTimestamp() + 30 * 60) < time()) {
			try {
				$team = $this->retrieveTeamByTagOrNameFromElophantAPI($region, $tagOrName, $team);
			}
			catch (ServiceUnavailableException $e) {
				// On a rien ÃƒÂ  faire, on intercepte juste l'exception
			}
		}

		return $team;
	}
	
	private function retrieveTeamByTagOrNameFromElophantAPI($region, $tagOrName, TeamSeekerCache $team = null)
	{
		// Si $team est ÃƒÆ’Ã‚Â©gal ÃƒÆ’Ã‚Â  null, on crÃƒÆ’Ã‚Â©ÃƒÆ’Ã‚Â© un nouvelle objet TeamSeekerCache
		$lockTeamCreationKey = 'team_seeker_' . $tagOrName . '_' . $region . '_locker';
		if (null == $team) {
			apc_store($lockTeamCreationKey, true);
			$team = new TeamSeekerCache();
		}
		
		// On essaye de rÃƒÆ’Ã‚Â©cupÃƒÆ’Ã‚Â©rer les informations conernant l'ÃƒÆ’Ã‚Â©quipe ÃƒÆ’Ã‚Â  partir du tag ou du nom d'ÃƒÆ’Ã‚Â©quipe
		try {
			$rawResponse = $this->elophantAPIManager->findTeamByTagOrName($region, $tagOrName);
		}
		catch (InvalidArgumentException $e) {
			// Si l'exception est de type InvalidArugmentException cela signifie que le tag ou le nom fourni n'est pas reconnu pour la rÃƒÆ’Ã‚Â©gion spÃƒÆ’Ã‚Â©cifiÃƒÆ’Ã‚Â©e
			throw new InvalidTeamNameOrTagException();
		}
		
		// On rassemble toutes les informations conernant l'ÃƒÆ’Ã‚Â©quipes et ses membres dans un mÃƒÆ’Ã‚Âªme tableau
		$teamInfos = array(
			'tag'						=> $rawResponse->tag,
			'name'						=> $rawResponse->name,
			'ranked_team_5x5_league'	=> 'UNRANKED',
			'ranked_team_3x3_league'	=> 'UNRANKED'
		);
		
		$roster = array();
		// On commence par parcourir tous les joueurs que comportent l'ÃƒÆ’Ã‚Â©quipe pour les rassembler dans un seul et mÃƒÆ’Ã‚Âªme tableau
		foreach ($rawResponse->roster->memberList as $member) {
			$memberInfos = array(
				'summoner_name'				=> $member->playerName,
				'summoner_id'				=> $member->playerId,
				'ranked_solo_5x5_league'	=> 'UNDEFINED'
			);
			
			// On se base sur le propriÃƒÆ’Ã‚Â©taire de l'ÃƒÆ’Ã‚Â©quipe et ses ligues pour retrouver la ligue et la division de l'ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©quipe en question
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
				
				// Si on ne trouve pas de ligue pour le capitaine cela signifie qu'il n'es pas classÃƒÆ’Ã‚Â© en soloQ
				if ($memberInfos['ranked_solo_5x5_league'] == 'UNDEFINED') {
					$memberInfos['ranked_solo_5x5_league'] = 'UNRANKED';
				}
				
				// Seulement pour le capitaine de l'ÃƒÆ’Ã‚Â©quipe, on en profite pour rÃƒÆ’Ã‚Â©cupÃƒÆ’Ã‚Â©rer son classement en soloQ
				foreach ($leagues->summonerLeagues as $league) {
					if ($league->queue == 'RANKED_SOLO_5x5') {
						$memberInfos['ranked_solo_5x5_league'] = $league->tier . '_' . $league->requestorsRank;
						break;
					}
				}
				
				// On les parcourt une à  une pour connaître la file et l'équipe concernées
				foreach ($leagues->summonerLeagues as $league) {
					// On test si c'est une file qui concerne l'ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©quipe que l'on recherche
					if ($teamInfos['name'] == $league->requestorsName) {
						// Si oui on test si c'est pour la file Equipe ClassÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©e 5vs5
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
		// Parcours de l'historique des 20 (max) derniers matchs de l'ÃƒÆ’Ã‚Â©quipe
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
		
		try {
			$team->save();
		}
		catch (Exception $e) {
			$team = $this->findInCacheByTagOrName($region, $tagOrName);
		}
		
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
	public function updatePlayerSoloQLeagueIfNeeded($region, $tag, $playerID) 
	{		
		$player = null;
		$playerKey = null;
		foreach ($this->findTeamByTagOrName($region, $tag)->getRoster() as $key => $member) {
			if ($member['summoner_id'] == $playerID) {
				$player = $member;
				$playerKey = $key;
				break;
			}
		}
		
		if ($player['ranked_solo_5x5_league'] != 'UNDEFINED') {
			return $player;
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
		
		/*$team = $this->findTeamByTagOrName($region, $tag);
		$roster = $team->getRoster();
		$roster[$playerKey] = $player;
		$team->setRoster($roster);
		$team->save();*/
				
		return $player;
	}
	
	public function findInCacheByTagOrName($region, $tagOrName)
	{
		return TeamSeekerCacheQuery::create()
			->where(TeamSeekerCachePeer::NAME . '= ?', $tagOrName)
			->_or()
			->where(TeamSeekerCachePeer::TAG . '= ?', $tagOrName)
			->add(TeamSeekerCachePeer::REGION, $region)
		->findOne();
	}
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
