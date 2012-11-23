<?php

namespace MVNerds\CoreBundle\Comment;

interface IComment 
{
	public function getId();
	
	public function getCommentCount();
	
	public function setCommentCount($v);
}
