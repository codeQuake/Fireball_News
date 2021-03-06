<?php

namespace cms\system\importer;

use cms\data\news\News;
use cms\data\news\NewsAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\importer\AbstractImporter;
use wcf\system\importer\ImportHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;

/**
 * Importer for news.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = News::class;

	private $importCategoryID = 0;

	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);

		if (!empty($additionalData['languageCode'])) {
			if (($language = LanguageFactory::getInstance()->getLanguageByCode($additionalData['languageCode'])) !== null) {
				$data['languageID'] = $language->languageID;
			}
		}

		if (is_numeric($oldID)) {
			$news = new News($oldID);
			if (!$news->newsID) {
				$data['newsID'] = $oldID;
			}
		}

		// save categories
		$categoryIDs = [];
		if (!empty($additionalData['categories'])) {
			foreach ($additionalData['categories'] as $oldCategoryID) {
				$newCategoryID = ImportHandler::getInstance()->getNewID('de.codequake.cms.category.news',
					$oldCategoryID);
				if ($newCategoryID) {
					$categoryIDs[] = $newCategoryID;
				}
			}
		}

		if (!empty($categoryIDs)) {
			$categoryIDs[] = $this->getImportCategoryID();
		}

		$action = new NewsAction([], 'create', [
			'data' => $data,
			'categoryIDs' => $categoryIDs,
		]);
		$returnValues = $action->executeAction();
		$newID = $returnValues['returnValues']->newsID;

		$news = new News($newID);

		// save tags
		if (!empty($additionalData['tags'])) {
			TagEngine::getInstance()->addObjectTags('de.codequake.cms.news', $news->newsID, $additionalData['tags'],
				($news->languageID ? : LanguageFactory::getInstance()->getDefaultLanguageID()));
		}

		ImportHandler::getInstance()->saveNewID('de.codequake.cms.news', $oldID, $news->newsID);

		return $news->newsID;
	}

	private function getImportCategoryID() {
		if (!$this->importCategoryID) {
			$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category',
				'de.codequake.cms.category.news');

			$sql = 'SELECT		categoryID
				FROM		wcf' . WCF_N . '_category
				WHERE		objectTypeID = ?
						AND parentCategoryID = ?
						AND title = ?
				ORDER BY	categoryID';
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([
				$objectTypeID,
				0,
				'Import',
			]);
			$row = $statement->fetchArray();
			if ($row !== false) {
				$this->importCategoryID = $row['categoryID'];
			}
			else {
				$sql = 'INSERT INTO	wcf' . WCF_N . '_category
							(objectTypeID, parentCategoryID, title, showOrder, time)
					VALUES		(?, ?, ?, ?, ?)';
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					$objectTypeID,
					0,
					'Import',
					0,
					TIME_NOW,
				]);
				$this->importCategoryID = WCF::getDB()->getInsertID('wcf' . WCF_N . '_category', 'categoryID');
			}
		}

		return $this->importCategoryID;
	}
}
