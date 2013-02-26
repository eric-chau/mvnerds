<?php

namespace MVNerds\CoreBundle\ElophantAPI;

use Buzz\Browser;

class ElophantAPIManager
{
	private $buzz;
	private $developerAPIKey = '5nbcaguivyp0JvvttrmA';
	private $apiBaseUrl = 'http://api.elophant.com/v2/';
	public static $TIER_HIERARCHY = array(
		'CHALLENGER' => 1,
		'DIAMOND' => 2,
		'GOLD' => 3,
		'SILVER' => 4,
		'BRONZE' => 5
	);
	public static $DIVISION_HIERARCHY = array(
		'I' => 1,
		'II' => 2,
		'III' => 3,
		'IV' => 4,
		'V' => 5
	);
	
	public function getSummonerAccoundId($summonerName, $region)
	{
		// Récupération l'ID du compte
		$url = $this->apiBaseUrl . $region . '/summoner/' . rawurlencode($summonerName) . '?key=' . $this->developerAPIKey;
		//var_dump($url);
		
		$response = $this->buzz->get($url);
		$responseArray = json_decode($response->getContent());
		$summonerAccountID = $responseArray->data->summonerId;
		var_dump($this->getSummonerLeagues($summonerAccountID));
		die;
				
		//$this->getSummonerLastTenGames($summonerAccountID, $region);
		die;
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
	
	public function getSummonerLeagues($summonerID)
	{
		// préparation de la route de la requête à envoyer
		$url = $this->apiBaseUrl . 'euw/leagues/'.$summonerID.'?key=' . $this->developerAPIKey;
		$response = $this->buzz->get($url);
		$responseObject = json_decode($response->getContent());
		if (false == $responseObject->success) {
			return array();
		}
		
		$summonerRankedInfos = array();
		foreach ($responseObject->data->summonerLeagues as $league) {
			$queueInfos = array();
			$queueInfos['tier'] = $league->tier;
			$queueInfos['division'] = $league->requestorsRank;
			$queueInfos['playerOrTeamName'] = $league->requestorsName;
			foreach ($league->entries as $entry) {
				if ($entry->playerOrTeamName == $queueInfos['playerOrTeamName']) {
					$queueInfos['leaguePoints'] = $entry->leaguePoints;
					$queueInfos['wins'] = $entry->wins;
					break;
				}
			}
			
			// Test si la file que l'on veut ajouter a déjà été ajoutée ou non dans le tableau
			if (isset($summonerRankedInfos[$league->queue])) {
				$storedTier = $summonerRankedInfos[$league->queue]['tier'];
				$newTier = $queueInfos['tier'];
				// Test si le nouveau tier est meilleur que le tier stocké ou pas
				if (self::$TIER_HIERARCHY[$storedTier] > self::$TIER_HIERARCHY[$newTier]) {
					$summonerRankedInfos[$league->queue] = $queueInfos;
				}
				// Test si le nouveau et le tier stocké sont équivalents ou non
				elseif (self::$TIER_HIERARCHY[$storedTier] == self::$TIER_HIERARCHY[$newTier]) {
					$storedDivision = $summonerRankedInfos[$league->queue]['division'];
					$newDivision = $queueInfos['division'];
					// Test si la division stocké est meilleure que la nouvelle division ou non
					if (self::$DIVISION_HIERARCHY[$storedDivision] > self::$DIVISION_HIERARCHY[$newDivision]) {
						$summonerRankedInfos[$league->queue] = $queueInfos;
					}
				}
			}
			else {
				$summonerRankedInfos[$league->queue] = $queueInfos;
			}
		}
		
		return $summonerRankedInfos;
	}
	
	public function setBuzz(Browser $buzz) 
	{
		$this->buzz = $buzz;
	}
}
