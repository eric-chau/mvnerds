<?php

namespace MVNerds\CoreBundle\View;

interface IView
{	
	public function getView();
	
	public function setView($v);
	
	public function getId();
}
