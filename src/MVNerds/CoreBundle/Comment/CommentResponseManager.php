<?php

namespace MVNerds\CoreBundle\Comment;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\CommentResponseQuery;
use MVNerds\CoreBundle\Model\CommentResponsePeer;

class CommentResponseManager
{
	public function findById($id)
	{
		$response = CommentResponseQuery::create()
			->add(CommentResponsePeer::ID, $id)
		->findOne();
		
		if (null == $response) {
			throw new InvalidArgumentException('No response for id `'. $id .'`');
		}
		
		return $response;
	}
}
