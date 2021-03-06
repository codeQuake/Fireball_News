<?php

namespace cms\system\user\notification\object\type;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\WCF;

/**
 * Object type for news comment notifications.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = CommentUserNotificationObject::class;

	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Comment::class;

	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CommentList::class;

	/**
	 * @inheritDoc
	 */
	public function getOwnerID($objectID) {
		$sql = '
            SELECT news.userID
			FROM wcf' . WCF_N . '_comment comment
			LEFT JOIN cms' . WCF_N . '_news news
			ON (news.newsID = comment.objectID)
			WHERE comment.commentID = ?';
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectID]);
		$row = $statement->fetchArray();

		return ($row ? $row['userID'] : 0);
	}
}
