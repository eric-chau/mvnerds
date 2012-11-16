<?php

namespace MVNerds\CoreBundle\Statistics;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\Statistics;
use MVNerds\CoreBundle\Model\StatisticsQuery;
use MVNerds\CoreBundle\Model\StatisticsPeer;

class StatisticsManager
{
	public function findByUniqueName($name)
	{
		$statistic = StatisticsQuery::create()
				->add(StatisticsPeer::UNIQUE_NAME, $name)
		->findOne();

		if (null === $statistic)
		{
			throw new InvalidArgumentException('Statistic with unique_name :' . $name . ' does not exist!');
		}

		return $statistic;
	}
}
