<?php

namespace MVNerds\VoteBundle\Propel;

use \Behavior;

class MVNerdsVotableBehavior extends Behavior
{
	// default parameters value
	protected $parameters = array(
		'like_count_column'	=> 'like_count',
		'dislike_count_column'	=> 'dislike_count',
		'vote_status_column'	=> 'vote_status',
		'value_set'			=> 'DEFAULT, APPROVED, FEATURED',
		'default_value'		=> 'DEFAULT'
	);
	
	 public function modifyTable()
	{
		if (!$this->getTable()->containsColumn($this->getParameter('like_count_column'))) {
			$column = $this->getTable()->addColumn(array(
				'name'    => $this->getParameter('like_count_column'),
				'type'    => 'INTEGER',
				'default' => 0
			));
		}
		
		if (!$this->getTable()->containsColumn($this->getParameter('dislike_count_column'))) {
			$column = $this->getTable()->addColumn(array(
				'name'    => $this->getParameter('dislike_count_column'),
				'type'    => 'INTEGER',
				'default' => 0
			));
		}
		
		if (!$this->getTable()->containsColumn($this->getParameter('vote_status_column'))) {
			$column = $this->getTable()->addColumn(array(
				'name'    => $this->getParameter('vote_status_column'),
				'type'    => 'ENUM',
				'valueSet'	=> $this->getParameter('value_set'),
				'default'	=> $this->getParameter('default_value')
			));
		}
	}
	
	public function objectFilter(&$script)
	{
		$pattern = '/abstract class (\w+) extends (\w+) implements (\w+)/i';
		$replace = 'abstract class ${1} extends ${2} implements ${3}, IVote';
		$script = preg_replace($pattern, $replace, $script);
	}
	
	public function objectMethods($builder)
	{
		$builder->declareClassNamespace('IVote','MVNerds\CoreBundle\Vote');
	}
}
