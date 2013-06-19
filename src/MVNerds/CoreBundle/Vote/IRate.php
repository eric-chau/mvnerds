<?php

namespace MVNerds\CoreBundle\Vote;

interface IRate 
{	
	public function getRating();
	
	public function setRating($v);
}
