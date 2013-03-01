<?php

namespace MVNerds\CoreBundle\ElophantAPI;

use Buzz\Browser;
use Exception;
use RuntimeException;

use MVNerds\CoreBundle\Exception\ElophantAFKException;
use MVNerds\CoreBundle\Exception\InvalidSummonerNameException;
use MVNerds\CoreBundle\Model\GameAccount;

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
	
	public function getGameAccountFromRegionAndUsername($region, $summonerName)
	{
		// Récupération l'ID du compte
		$url = $this->apiBaseUrl . $region . '/summoner/' . rawurlencode($summonerName) . '?key=' . $this->developerAPIKey;
		try {
			$response = $this->buzz->get($url);
		}
		catch (RuntimeException $e) {
			throw new ElophantAFKException();
		}
		
		$this->updateRequestSendCount();
		$contentObject = json_decode($response->getContent());
		if (!$contentObject->success) {
			throw new InvalidSummonerNameException();
		}
		
		$contentObject = $contentObject->data;
		$gameAccount = new GameAccount();
		$gameAccount->setSummonerName($summonerName);
		$gameAccount->setRegion($region);
		$gameAccount->setSummonerAccountId($contentObject->acctId);
		$gameAccount->setSummonerId($contentObject->summonerId);
		$gameAccount->generateActivationCode();
				
		return $gameAccount;
	}
	
	public function checkActivationCodeWithMasteriesPage(GameAccount $gameAccount)
	{
		$url = $this->apiBaseUrl . $gameAccount->getRegion() . '/mastery_pages/' . $gameAccount->getSummonerId() . '?key=' . $this->developerAPIKey;
		try {
			$response = $this->buzz->get($url);
		}
		catch (RuntimeException $e) {
			throw new ElophantAFKException();
		}
		
		$this->updateRequestSendCount();
		$contentObject = json_decode($response->getContent());
		if (!$contentObject->success) {
			throw new ElophantAFKException();
		}
		
		$contentObject = $contentObject->data;
		$success = false;
		foreach ($contentObject->bookPages as $page) {
			if (strcmp($page->name, $gameAccount->getActivationCode()) == 0) {
				$success = true;
				$gameAccount->activate();
				$gameAccount->save();
				break;
			}
		}
		
		return $success;
	}
	
	public function getSummonerLastTenGames($summonerAccountID, $region)
	{
		// Récupération des champions et leurs elophant ids
		$url = $this->apiBaseUrl . '/champions?key=' . $this->developerAPIKey;
		$response = null;

		
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
	
	private function updateRequestSendCount($value = 1)
	{
		$requestSendInfos = apc_fetch('elophant_request_count_per_fifteen_minutes');
		if (false == $requestSendInfos) {
			$requestSendInfos = array(
				'request_count' => $value,
				'since_time'	=> time()
			);
		}
		else {
			if (($requestSendInfos['since_time'] + 15 * 60) > time()) {
				$requestSendInfos['request_count'] += $value;
			}
			else {
				$requestCountHistory = apc_fetch('elophant_request_count_history');
				if (false == $requestCountHistory) {
					$requestCountHistory = array();
				}
				
				$requestCountHistory[] = $requestSendInfos;
				apc_store('elophant_request_count_history', $requestCountHistory);
				$requestSendInfos = array(
					'request_count' => $value,
					'since_time'	=> time()
				);
			}
		}
				
		apc_store('elophant_request_count_per_fifteen_minutes', $requestSendInfos);
	}
}
