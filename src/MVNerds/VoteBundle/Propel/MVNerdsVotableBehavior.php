<?php

namespace MVNerds\VoteBundle\Propel;

use \Behavior;

class MVNerdsVotableBehavior extends Behavior
{
	 public function modifyTable()
	{
		if (!$this->getTable()->containsColumn('like_count')) {
			$column = $this->getTable()->addColumn(array(
				'name'    => 'like_count',
				'type'    => 'INTEGER',
				'default' => 0
			));
		}
		
		if (!$this->getTable()->containsColumn('dislike_count')) {
			$column = $this->getTable()->addColumn(array(
				'name'    => 'dislike_count',
				'type'    => 'INTEGER',
				'default' => 0
			));
		}
	}
	
	public function objectFilter(&$script)
	{
		$pattern = '/abstract class (\w+) extends (\w+)  implements (\w+)/i';
		$replace = 'abstract class ${1} extends ${2}  implements ${3}, IVote';
		$script = preg_replace($pattern, $replace, $script);
	}
	
	public function objectMethods($builder)
	{
		$builder->declareClassNamespace('IVote','MVNerds\CoreBundle\Vote');
	}
}
