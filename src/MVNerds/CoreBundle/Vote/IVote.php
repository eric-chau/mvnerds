<?php

namespace MVNerds\CoreBundle\Vote;

interface IVote 
{	
	public function getLikeCount();
	
	public function setLikeCount($v);
	
	public function getDislikeCount();
	
	public function setDislikeCount($v);
}
