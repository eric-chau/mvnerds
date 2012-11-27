<?php

namespace MVNerds\CoreBundle\Comment;

use MVNerds\CoreBundle\Model\Comment;
use MVNerds\CoreBundle\Model\CommentPeer;
use MVNerds\CoreBundle\Model\CommentQuery;
use MVNerds\CoreBundle\Comment\IComment;
use MVNerds\CoreBundle\Model\User;

class CommentManager
{
	public function addComment(IComment $object, User $user, $commentString)
	{
		$comment = new Comment();
		$comment->setObjectNamespace(get_class($object));
		$comment->setObjectId($object->getId());
		$comment->setUser($user);
		$comment->setContent($commentString);
		$this->increaseObjectCommentCountByOne($object);		
		
		// Finally
		$comment->save();
		
		return $comment;
	}
	
	public function countCommentForUser(User $user)
	{
		return $comments = CommentQuery::create()
			->add(CommentPeer::USER_ID, $user->getId())
		->count();
	}
	
	public function findByObject(IComment $object)
	{
		return $comments = CommentQuery::create()
			->joinWith('User')
			->joinWith('User.Profile')
			->joinWith('Profile.Avatar')
			->add(CommentPeer::OBJECT_NAMESPACE, get_class($object))
			->add(CommentPeer::OBJECT_ID, $object->getId())
			->orderBy(CommentPeer::CREATE_TIME, 'desc')
			->limit(5)
		->find();
	}
	
	private function increaseObjectCommentCountByOne(IComment $object)
	{
		$object->setCommentCount($object->getCommentCount() + 1);
		
		// Finally
		$object->save();
	}
}
