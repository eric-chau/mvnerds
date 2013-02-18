<?php

namespace MVNerds\ReportBundle\Propel;

use \Behavior;

class MVNerdsReportableBehavior extends Behavior
{
	// default parameters value
	protected $parameters = array(
		'report_status_column' => 'report_status',
		'value_set' => 'FALSE, SOFT, HARD',
		'default_value' => 'FALSE'
	);
	
	 public function modifyTable()
	{
		if (!$this->getTable()->containsColumn('report_status')) {
			$column = $this->getTable()->addColumn(array(
				'name'	=> $this->getParameter('report_status_column'),
				'type'		=> 'ENUM',
				'valueSet'	=> $this->getParameter('value_set'),
				'default'	=> $this->getParameter('default_value')
			));
		}
	}
	
	public function objectFilter(&$script)
	{
		$pattern = '/abstract class (\w+) extends (\w+)  implements (\w+)/i';
		$replace = 'abstract class ${1} extends ${2}  implements ${3}, IReport';
		$script = preg_replace($pattern, $replace, $script);
	}
	
	public function objectMethods($builder)
	{
		$builder->declareClassNamespace('IReport','MVNerds\CoreBundle\Report');
	}
}
