<?php

namespace MVNerds\VoteBundle\Propel;

use \Behavior;

class MVNerdsRatableBehavior extends Behavior
{
	// default parameters value
	protected $parameters = array(
		'rating_column'	=> 'rating'
	);
	
	 public function modifyTable()
	{
		if (!$this->getTable()->containsColumn($this->getParameter('rating_column'))) {
			$column = $this->getTable()->addColumn(array(
				'name'    => $this->getParameter('rating_column'),
				'type'    => 'INTEGER',
				'default' => 0
			));
		}
	}
	
	public function objectFilter(&$script)
	{
		$pattern = '/abstract class (\w+) extends (\w+) implements (\w+)/i';
		$replace = 'abstract class ${1} extends ${2} implements ${3}, IRate';
		$script = preg_replace($pattern, $replace, $script);
	}
	
	public function objectMethods($builder)
	{
		$builder->declareClassNamespace('IRate','MVNerds\CoreBundle\Vote');
	}
}
