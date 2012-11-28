<?php

namespace MVNerds\CoreBundle\Comment;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\Comment;
use MVNerds\CoreBundle\Model\CommentPeer;
use MVNerds\CoreBundle\Model\CommentQuery;
use MVNerds\CoreBundle\Comment\IComment;
use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Model\UserReportComment;
use MVNerds\CoreBundle\Model\UserReportCommentPeer;
use MVNerds\CoreBundle\Model\UserReportCommentQuery;

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
		$comments = CommentQuery::create()
			->joinWith('User')
			->joinWith('User.Profile')
			->joinWith('Profile.Avatar')
			->add(CommentPeer::OBJECT_NAMESPACE, get_class($object))
			->add(CommentPeer::OBJECT_ID, $object->getId())
			->orderBy(CommentPeer::CREATE_TIME, 'desc')
			->limit(5)
		->find();
		
		$comments->populateRelation('UserReportComment');
		
		return $comments;
	}
	
	public function findById($id)
	{
		$comment = CommentQuery::create()
			->add(CommentPeer::ID, $id)
		->findOne();
		
		if (null == $comment) {
			throw new InvalidArgumentException('No comment for id `'. $id .'`');
		}
		
		return $comment;
	}
	
	private function increaseObjectCommentCountByOne(IComment $object)
	{
		$object->setCommentCount($object->getCommentCount() + 1);
		
		// Finally
		$object->save();
	}
	
	public function doReportComment(User $user, $commentID)
	{
		$comment = null;
		try {
			$comment = $this->findById($commentID);
		}
		catch (InvalidArgumentException $e) {
			return false;
		}
		
		$report = UserReportCommentQuery::create()
			->add(UserReportCommentPeer::COMMENT_ID, $comment->getId())
			->add(UserReportCommentPeer::USER_ID, $user->getId())
		->findOne();
		
		if (null != $report) {
			return false;
		}
		
		$report = new UserReportComment();
		$report->setComment($comment);
		$report->setUser($user);
		
		// Finally
		$report->save();
		
		return true;
	}
}
