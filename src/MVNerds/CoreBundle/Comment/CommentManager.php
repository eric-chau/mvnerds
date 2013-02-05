<?php

namespace MVNerds\CoreBundle\Comment;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
	const COMMENT_PER_PAGE = 10;
	
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
	
	public function editComment($commentID, User $user, $commentString)
	{
		$comment = $this->findById($commentID);
		if ($comment->getUserId() != $user->getId()) {
			throw new AccessDeniedException();
		}
		
		$comment->setContent($commentString);
		$comment->save();
		
		return $comment;
	}
	
	public function countCommentForUser(User $user)
	{
		return $comments = CommentQuery::create()
			->add(CommentPeer::USER_ID, $user->getId())
		->count();
	}
	
	public function objectCommentCount(IComment $object)
	{
		return $comments = CommentQuery::create()
			->add(CommentPeer::OBJECT_ID, $object->getId())
			->add(CommentPeer::OBJECT_NAMESPACE, get_class($object))
		->count();
	}
	
	public function findByObject(IComment $object, $firstCommentID = null)
	{
		$offset = 0;
		if ($firstCommentID != null) {
			$offset = $this->countCommentSinceFirstLoad($object, $firstCommentID);
		}
		$comments = CommentQuery::create()
			->joinWith('User')
			->joinWith('User.Profile')
			->joinWith('Profile.Avatar')
			->add(CommentPeer::OBJECT_NAMESPACE, get_class($object))
			->add(CommentPeer::OBJECT_ID, $object->getId())
			->orderBy(CommentPeer::CREATE_TIME, 'desc')
			->offset($offset)
			->limit($firstCommentID != null? 0 : self::COMMENT_PER_PAGE)
		->find();
		
		$comments->populateRelation('UserReportComment');
		
		$commentsArray = array(
			'comments' => $comments
		);
		
		if ($firstCommentID != null) {
			$commentsArray['comment_count_since_first_load'] = $offset;
		}
		
		return $commentsArray;
	}
	
	private function countCommentSinceFirstLoad(IComment $object, $firstCommentID) {
		return CommentQuery::create()
			->add(CommentPeer::OBJECT_NAMESPACE, get_class($object))
			->add(CommentPeer::OBJECT_ID, $object->getId())
			->where(CommentPeer::ID . '> ?', $firstCommentID)
		->count() + 1; // +1 car on doit compter également le commentaire qui sert de référence ($firstCommentID)
	}
	
	public function getLastestComments(IComment $object, $lastCommentID)
	{
		$comments = CommentQuery::create()
			->joinWith('User')
			->joinWith('User.Profile')
			->joinWith('Profile.Avatar')
			->add(CommentPeer::OBJECT_NAMESPACE, get_class($object))
			->add(CommentPeer::OBJECT_ID, $object->getId())
			->where(CommentPeer::ID . '> ?', $lastCommentID)
			->orderBy(CommentPeer::CREATE_TIME, 'desc')
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
		if (method_exists($object, 'keepUpdateDateUnchanged')) 
		{
			$object->keepUpdateDateUnchanged();
		}
		
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
