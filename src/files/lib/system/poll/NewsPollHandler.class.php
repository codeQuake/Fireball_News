<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\poll;

use cms\data\news\News;
use wcf\data\poll\Poll;
use wcf\system\poll\AbstractPollHandler;
use wcf\system\WCF;

/**
 * Poll handler for news.
 */
class NewsPollHandler extends AbstractPollHandler {
	/**
	 * @inheritDoc
	 */
	public function canStartPublicPoll() {
		return (WCF::getSession()->getPermission('user.fireball.news.canStartPublicPoll') ? true : false);
	}

	/**
	 * @inheritDoc
	 */
	public function canVote() {
		return (WCF::getSession()->getPermission('user.fireball.news.canVotePoll') ? true : false);
	}

	/**
	 * @inheritDoc
	 */
	public function getRelatedObject(Poll $poll) {
		$news = new News($poll->objectID);
		if ($news->newsID && $news->pollID == $poll->pollID) {
			return $news;
		}

		return null;
	}
}
