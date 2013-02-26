<?php

namespace MVNerds\CoreBundle\View;

use \Behavior;

class MVNerdsViewableBehavior extends Behavior
{
	// default parameters value
	protected $parameters = array(
		'view_column'	=> 'view'
	);
	
	 public function modifyTable()
	{
		if (!$this->getTable()->containsColumn($this->getParameter('view_column'))) {
			$this->getTable()->addColumn(array(
				'name'    => $this->getParameter('view_column'),
				'type'    => 'INTEGER',
				'default' => 0
			));
		}
	}
	
	public function objectFilter(&$script)
	{
		$pattern = '/abstract class (\w+) extends (\w+)  implements (\w+)/i';
		$replace = 'abstract class ${1} extends ${2}  implements ${3}, IView';
		$script = preg_replace($pattern, $replace, $script);
	}
	
	public function objectMethods($builder)
	{
		$builder->declareClassNamespace('IView','MVNerds\CoreBundle\View');
	}
}
