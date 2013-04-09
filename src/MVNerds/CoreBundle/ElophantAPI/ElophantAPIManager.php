<?php

namespace MVNerds\CoreBundle\ElophantAPI;

use Buzz\Browser;
use RuntimeException;
use Exception;
use JMS\SecurityExtraBundle\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\ElophantApiResponseCache;
use MVNerds\CoreBundle\Model\ElophantApiResponseCachePeer;
use MVNerds\CoreBundle\Model\ElophantApiResponseCacheQuery;
use MVNerds\CoreBundle\Exception\ElophantAPILimitExceedException;
use MVNerds\CoreBundle\Exception\ServiceUnavailableException;
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
		$response = null;
		try {
			$response = $this->sendRequest($region . '/summoner/' . rawurlencode($summonerName));
		}
		catch (InvalidArgumentException $e) {
			throw new InvalidSummonerNameException();
		}
		
		$gameAccount = new GameAccount();
		$gameAccount->setSummonerName($summonerName);
		$gameAccount->setRegion($region);
		$gameAccount->setSummonerAccountId($response->acctId);
		$gameAccount->setSummonerId($response->summonerId);
		$gameAccount->generateActivationCode();
				
		return $gameAccount;
	}
	
	public function checkActivationCodeWithMasteriesPage(GameAccount $gameAccount)
	{
		$response = $this->sendRequest($gameAccount->getRegion() . '/mastery_pages/' . $gameAccount->getSummonerId());
		
		$success = false;
		foreach ($response->bookPages as $page) {
			if (strcmp($page->name, $gameAccount->getActivationCode()) == 0) {
				$success = true;
				$gameAccount->activate();
				$gameAccount->save();
				break;
			}
		}
		
		return $success;
	}	
	
	private function updateSummonerLeagues(GameAccount $gameAccount)
	{
		$response = $this->getSummonerLeagues($gameAccount->getRegion(), $gameAccount->getSummonerId());

		$summonerRankedInfos = array();
		foreach ($response->summonerLeagues as $league) {
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
		
		foreach ($summonerRankedInfos as $key => $value) {
			switch ($key) {
				case 'RANKED_TEAM_3x3':
					$gameAccount->setRankedTeam3x3League($value['tier'], $value['division']);
					break;
				case 'RANKED_SOLO_5x5':
					$gameAccount->setRankedSolo5x5League($value['tier'], $value['division']);
					break;
				case 'RANKED_TEAM_5x5':
					$gameAccount->setRankedTeam5x5League($value['tier'], $value['division']);
					break;
				default:
					throw new Exception('Point normalement jamais atteint !');
			}
		}
	}
	
	private function updateRankedStats(GameAccount $gameAccount)
	{
		// préparation de la route de la requête à envoyer
		$response = $this->sendRequest($gameAccount->getRegion() . '/ranked_stats/'. $gameAccount->getSummonerAccountId());

		$rankedStatsInfos = array();
		foreach ($response->lifetimeStatistics as $stat) {
			if (0 == $stat->championId) {
				$rankedStatsInfos[$stat->statType] = $stat->value;
			}
		}
		
		// Général
		$gameAccount->setGoldEarned($rankedStatsInfos['TOTAL_GOLD_EARNED']);
		
		// Relatif au PVE
		$gameAccount->setTurretsKilled($rankedStatsInfos['TOTAL_TURRETS_KILLED']);
		$gameAccount->setMinionKills($rankedStatsInfos['TOTAL_MINION_KILLS']);
		$gameAccount->setMonsterKills($rankedStatsInfos['TOTAL_NEUTRAL_MINIONS_KILLED']);
		
		// Relatif aux kills des champions
		$gameAccount->setPentaKills($rankedStatsInfos['TOTAL_PENTA_KILLS']);
		$gameAccount->setQuadraKills($rankedStatsInfos['TOTAL_QUADRA_KILLS']);
		$gameAccount->setTripleKills($rankedStatsInfos['TOTAL_TRIPLE_KILLS']);
		$gameAccount->setDoubleKills($rankedStatsInfos['TOTAL_DOUBLE_KILLS']);
		$gameAccount->setChampionKills($rankedStatsInfos['TOTAL_CHAMPION_KILLS']);
		$gameAccount->setAssists($rankedStatsInfos['TOTAL_ASSISTS']);
		$gameAccount->setMaxChampionsKilled($rankedStatsInfos['MOST_CHAMPION_KILLS_PER_SESSION']);
		$gameAccount->setMaxDeaths($rankedStatsInfos['MAX_NUM_DEATHS']);
		$gameAccount->setTimeSpentDead($rankedStatsInfos['TOTAL_TIME_SPENT_DEAD']);
		$gameAccount->setDeaths($rankedStatsInfos['TOTAL_DEATHS_PER_SESSION']);
		$gameAccount->setMaxTimeSpentLiving($rankedStatsInfos['MAX_TIME_SPENT_LIVING']);
		$gameAccount->setMaxTimeGameDuration($rankedStatsInfos['MAX_TIME_PLAYED']);
		$gameAccount->setKillingSpree($rankedStatsInfos['KILLING_SPREE']);
		$gameAccount->setLargestKillingSpree($rankedStatsInfos['MAX_LARGEST_KILLING_SPREE']);
		
		// Relatif aux parties
		$gameAccount->setGamesPlayed($rankedStatsInfos['TOTAL_SESSIONS_PLAYED']);
		$gameAccount->setTotalVictory($rankedStatsInfos['TOTAL_SESSIONS_WON']);
		$gameAccount->setTotalDefeat($rankedStatsInfos['TOTAL_SESSIONS_PLAYED'] - $rankedStatsInfos['TOTAL_SESSIONS_WON']);
	}
	
	public function updateRankedStatsIfNeeded(GameAccount $gameAccount)
	{
		$lastUpdateTimestamp = $gameAccount->getLastUpdateTime();
		if ( null != $lastUpdateTimestamp && ($lastUpdateTimestamp + 15 * 60 > time()) ) {
			return;
		}
		
		try {
			$this->updateSummonerLeagues($gameAccount);
			$this->updateRankedStats($gameAccount);
		}
		catch (ServiceUnavailableException $e) {
			return;
		}
		
		$gameAccount->updateTime();
		$gameAccount->save();
	}
	
	public function findTeamByTagOrName($region, $tagOrName)
	{
		return $this->sendRequest($region . '/find_team/' . rawurlencode($tagOrName));
	}
	
	public function getSummonerLeagues($region, $summonerID)
	{
		return $this->sendRequest($region . '/leagues/' . $summonerID);
	}
	
	public function setBuzz(Browser $buzz) 
	{
		$this->buzz = $buzz;
	}
	
	/**
	 * Mise à jour du nombre de requête effectuer vers Elophant
	 * Note : Stocké avec le cache APC
	 * 
	 * @param int $value le nombre de requête à incrémenter, par défaut à 1
	 */
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
	
	/**
	 * 
	 * @param type $function
	 * @throws ServiceUnavailableException Si ELOPHANT n'est pas disponible
	 */
	private function sendRequest($function)
	{
		$apiResponseCache = $this->findElophantAPIResponseByKey($function);
		if (null != $apiResponseCache && $apiResponseCache->getUpdateTime()->getTimestamp() + 30 * 60 > time()) {
			return $apiResponseCache->getResponse();
		}
		
		$url = $this->apiBaseUrl . $function;
		
		$response = null;
		try {
			$response = $this->buzz->get($url .'?key=' . $this->developerAPIKey);
		}
		catch (RuntimeException $e) {
			throw new ServiceUnavailableException();
		}

		$this->updateRequestSendCount();
		$contentObject = json_decode($response->getContent());

		if (null == $contentObject) {
			throw new ServiceUnavailableException();
		}
		
		if (!$contentObject->success) {
			if ($contentObject->error == 'No active connection found for the given region.') {
				throw new ServiceUnavailableException();
			}
			elseif ($contentObject->error == 'The global rate limit has been exceeded.') {
				throw new ElophantAPILimitExceedException();
			}
			
			throw new InvalidArgumentException();
		}
		
		if (null == $apiResponseCache) {
			$apiResponseCache = new ElophantApiResponseCache();
			$apiResponseCache->setKey($function);
		}
		
		$apiResponseCache->setResponse($contentObject->data);
		try {
			$apiResponseCache->save();
		}
		catch (Exception $e) {
			$apiResponseCache = $this->findElophantAPIResponseByKey($function);
		}
		
		return $apiResponseCache->getResponse();
	}
	
	public function findElophantAPIResponseByKey($key)
	{
		return ElophantApiResponseCacheQuery::create()
			->add(ElophantApiResponseCachePeer::KEY, $key)
		->findOne();
	}
}
