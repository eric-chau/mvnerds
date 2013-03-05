<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseGameAccount;


/**
 * Skeleton subclass for representing a row from the 'game_account' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class GameAccount extends BaseGameAccount {
	
	public function isActive()
	{
		return $this->getIsActive();
	}
	
	public function activate()
	{
		$this->setIsActive(true);
	}
	
	public function generateActivationCode()
	{
		$this->setActivationCode('MVNerds Code ' . substr(md5(uniqid(rand(), true)), 0, 12));
	}
	
	// **************************************************************
	// MÉTHODES LIÉES AUX STATISTIQUES EN PARTIE CLASSÉES D'UN JOUEUR
	// **************************************************************
	
	// > RANKED SOLO 5x5 LEAGUE
	
	public function setRankedSolo5x5League($tier, $division)
	{
		$this->setProperty('RANKED_SOLO_5X5_LEAGUE', $tier . '_' . $division);
	}
	
	public function getRankedSolo5x5League()
	{
		return $this->getProperty('RANKED_SOLO_5X5_LEAGUE');
	}
	
	// > RANKED TEAM 3x3 LEAGUE
	
	public function setRankedTeam3x3League($tier, $division)
	{
		$this->setProperty('RANKED_TEAM_3X3_LEAGUE', $tier . '_' . $division);
	}
	
	public function getRankedTeam3x3League()
	{
		return $this->getProperty('RANKED_TEAM_3X3_LEAGUE');
	}
	
	// > RANKED TEAM 5x5 LEAGUE
	
	public function setRankedTeam5x5League($tier, $division)
	{
		$this->setProperty('RANKED_TEAM_5X5_LEAGUE', $tier . '_' . $division);
	}
	
	public function getRankedTeam5x5League()
	{
		return $this->getProperty('RANKED_TEAM_5X5_LEAGUE');
	}
	
	// > CHAMPION KILLS
	
	public function setChampionKills($value)
	{
		$this->setProperty('TOTAL_CHAMPION_KILL', $value);
	}
	
	public function getChampionKills()
	{
		return $this->getProperty('TOTAL_CHAMPION_KILL');
	}
	
	// > MAX CHAMPIONS KILLED
	
	public function setMaxChampionsKilled($value)
	{
		$this->setProperty('MAX_CHAMPIONS_KILLED', $value);
	}
	
	public function getMaxChampionsKilled()
	{
		return $this->getProperty('MAX_CHAMPIONS_KILLED');
	}
	
	// > PENTAKILL
	
	public function setPentaKills($value)
	{
		$this->setProperty('TOTAL_PENTA_KILL', $value);
	}
	
	public function getPentaKills()
	{
		return $this->getProperty('TOTAL_PENTA_KILL');
	}
	
	// > QUADRAKILL
	
	public function setQuadraKills($value)
	{
		$this->setProperty('TOTAL_QUADRA_KILL', $value);
	}
	
	public function getQuadraKills()
	{
		return $this->getProperty('TOTAL_QUADRA_KILL');
	}
	
	// > TRIPLEKILL
	
	public function setTripleKills($value)
	{
		$this->setProperty('TOTAL_TRIPLE_KILL', $value);
	}
	
	public function getTripleKills()
	{
		return $this->getProperty('TOTAL_TRIPLE_KILL');
	}
	
	// > DOUBLE KILL
	
	public function setDoubleKills($value)
	{
		$this->setProperty('TOTAL_DOUBLE_KILL', $value);
	}
	
	public function getDoubleKills()
	{
		return $this->getProperty('TOTAL_DOUBLE_KILL');
	}
	
	// > KILLING SPREE
	
	public function setKillingSpree($value)
	{
		$this->setProperty('TOTAL_KILLING_SPREE', $value);
	}
	
	public function getKillingSpree()
	{
		return $this->getProperty('TOTAL_KILLING_SPREE');
	}
	
	// > LARGEST KILLING SPREE
	
	public function setLargestKillingSpree($value)
	{
		$this->setProperty('LARGEST_KILLING_SPREE', $value);
	}
	
	public function getLargestKillingSpree()
	{
		return $this->getProperty('LARGEST_KILLING_SPREE');
	}
	
	// > MINIONS KILLS
	
	public function setMinionKills($value)
	{
		$this->setProperty('TOTAL_MINION_KILLS', $value);
	}
	
	public function getMinionKills()
	{
		return $this->getProperty('TOTAL_MINION_KILLS');
	}
	
	// > MONSTERS KILLS
	
	public function setMonsterKills($value)
	{
		$this->setProperty('TOTAL_MONSTER_KILLS', $value);
	}
	
	public function getMonsterKills()
	{
		return $this->getProperty('TOTAL_MONSTER_KILLS');
	}
	
	// > TURRETS KILLED
	
	public function setTurretsKilled($value)
	{
		$this->setProperty('TOTAL_TURRETS_KILLED', $value);
	}
	
	public function getTurretsKilled()
	{
		return $this->getProperty('TOTAL_TURRETS_KILLED');
	}
	
	// > GOLD EARNED
	
	public function setGoldEarned($value)
	{
		$this->setProperty('TOTAL_GOLD_EARNED', $value);
	}
	
	public function getGoldEarned()
	{
		return $this->getProperty('TOTAL_GOLD_EARNED');
	}
	
	// > TIME SPENT DEAD
	
	public function setTimeSpentDead($value)
	{
		$this->setProperty('TOTAL_TIME_SPENT_DEAD', $value);
	}
	
	public function getTimeSpentDead()
	{
		return $this->getProperty('TOTAL_TIME_SPENT_DEAD');
	}
	
	// > ASSIST
	
	public function setAssists($value)
	{
		$this->setProperty('TOTAL_ASSIST', $value);
	}
	
	public function getAssists()
	{
		return $this->getProperty('TOTAL_ASSIST');
	}
	
	// > DEATH
	
	public function setDeaths($value)
	{
		$this->setProperty('TOTAL_DEATH', $value);
	}
	
	public function getDeaths()
	{
		return $this->getProperty('TOTAL_DEATH');
	}
	
	// > MAX DEATH
	
	public function setMaxDeaths($value)
	{
		$this->setProperty('MAX_DEATH', $value);
	}
	
	public function getMaxDeaths()
	{
		return $this->getProperty('MAX_DEATH');
	}
	
	// > VICTORY
	
	public function setTotalVictory($value)
	{
		$this->setProperty('TOTAL_VICTORY', $value);
	}
	
	public function getTotalVictory()
	{
		return $this->getProperty('TOTAL_VICTORY');
	}
	
	// > DEFEAT
	
	public function setTotalDefeat($value)
	{
		$this->setProperty('TOTAL_DEFEAT', $value);
	}
	
	public function getTotalDefeat()
	{
		return $this->getProperty('TOTAL_DEFEAT');
	}
	
	// > GAMES PLAYED
	
	public function setGamesPlayed($value)
	{
		$this->setProperty('TOTAL_GAME_PLAYED', $value);
	}
	
	public function getGamesPlayed()
	{
		return $this->getProperty('TOTAL_GAME_PLAYED');
	}
	
	// > TIME SPENT LIVING
	
	public function setMaxTimeSpentLiving($value)
	{
		$this->setProperty('MAX_TIME_SPENT_LIVING', $value);
	}
	
	public function getMaxTimeSpentLiving()
	{
		return $this->getProperty('MAX_TIME_SPENT_LIVING');
	}
	
	// > MAX TIME GAME DURATION
	
	public function setMaxTimeGameDuration($value)
	{
		$this->setProperty('MAX_TIME_GAME_DURATION', $value);
	}
	
	public function getMaxTimeGameDuration()
	{
		return $this->getProperty('MAX_TIME_GAME_DURATION');
	}
	
	// > LAST UPDATE TIME
	
	public function updateTime()
	{
		$this->setProperty('LAST_UPDATE_TIME', time());
	}
	
	public function getLastUpdateTime()
	{
		return $this->getProperty('LAST_UPDATE_TIME');
	}	
} // GameAccount
