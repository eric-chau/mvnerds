<?php

namespace MVNerds\CoreBundle\ElophantAPI;

use Buzz\Browser;

class ElophantAPIManager
{
	private $buzz;
	private $developerAPIKey = '5nbcaguivyp0JvvttrmA';
	private $apiBaseUrl = 'http://api.elophant.com/v2/';
	
	public function getSummonerAccoundId($summonerName, $region)
	{
		// Récupération l'ID du compte
		$url = $this->apiBaseUrl . $region . '/summoner/' . rawurlencode($summonerName) . '?key=' . $this->developerAPIKey;
		//var_dump($url);
		
		$response = $this->buzz->get($url);
		$responseArray = json_decode($response->getContent());
		$summonerAccountID = $responseArray->data->acctId;
		var_dump($summonerName . '\'s account id: ' . $summonerAccountID);
		
		$this->getSummonerMaxAndCurrentEloAtSoloQ($summonerAccountID, $region);
		
		$this->getSummonerLastTenGames($summonerAccountID, $region);
		die;
	}
	
	public function getSummonerMaxAndCurrentEloAtSoloQ($summonerAccountID, $region)
	{
		// Récupération de l'elo max et courant en soloQ
		$url = $this->apiBaseUrl . $region . '/player_stats/' . $summonerAccountID . '?key=' . $this->developerAPIKey;
		//var_dump($url);
		
		$response = $this->buzz->get($url);
		$responseArray = json_decode($response->getContent());
		//var_dump($responseArray->data->playerStatSummaries->playerStatSummarySet); die;
		foreach ($responseArray->data->playerStatSummaries->playerStatSummarySet as $queue) {
			if ('RankedSolo5x5' == $queue->playerStatSummaryType) {
				var_dump('max soloQ\'s elo: ' . $queue->maxRating);
				var_dump('current soloQ\'s elo: ' . $queue->rating);
				
				break;
			}
		}
	}
	
	public function getSummonerLastTenGames($summonerAccountID, $region)
	{
		// Récupération des champions et leurs elophant ids
		$url = $this->apiBaseUrl . '/champions?key=' . $this->developerAPIKey;
		$response = $this->buzz->get($url);
		$responseArray = json_decode($response->getContent());
		$championsArray = array();
		foreach ($responseArray->data as $championInfos) {
			$championsArray[$championInfos->id] = $championInfos->name;
		}
		
		// Récupération des 10 dernières parties du joueur
		$url = $this->apiBaseUrl . $region . '/recent_games/' . $summonerAccountID . '?key=' . $this->developerAPIKey;
		$response = $this->buzz->get($url);
		$responseArray = json_decode($response->getContent());
		
		foreach(array_reverse($responseArray->data->gameStatistics) as $gameInfos) {
			var_dump('=====================================================================');
			var_dump('=====================================================================');
			var_dump('game\'s id: ' . $gameInfos->gameId);
			var_dump('ro0ny played: ' . $championsArray[$gameInfos->championId]);
			$teamId = $gameInfos->teamId;
			var_dump('team\'s id: ' . $teamId);
			var_dump('Opponents player :');
			foreach($gameInfos->fellowPlayers as $player) {
				if ($teamId != $player->teamId) {
					var_dump($player->summonerName .'(' . $championsArray[$player->championId] . ')');
				}
			}
		}
		
	}
	
	public function setBuzz(Browser $buzz) 
	{
		$this->buzz = $buzz;
	}
}
