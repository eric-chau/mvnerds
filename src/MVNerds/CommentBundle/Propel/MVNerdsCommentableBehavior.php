<?php

namespace MVNerds\CommentBundle\Propel;

use \Behavior;

class MVNerdsCommentableBehavior extends Behavior
{
	 public function modifyTable()
	{
		if (!$this->getTable()->containsColumn('comment_count')) {
			$column = $this->getTable()->addColumn(array(
				'name'    => 'comment_count',
				'type'    => 'INTEGER',
				'default' => 0
			));
		}
	}
	
	public function objectFilter(&$script)
	{
		$pattern = '/abstract class (\w+) extends (\w+) implements (\w+)/i';
		$replace = 'abstract class ${1} extends ${2} implements ${3}, IComment';
		$script = preg_replace($pattern, $replace, $script);
	}
	
	public function objectMethods($builder)
	{
		$builder->declareClassNamespace('IComment','MVNerds\CoreBundle\Comment');
	}
}
