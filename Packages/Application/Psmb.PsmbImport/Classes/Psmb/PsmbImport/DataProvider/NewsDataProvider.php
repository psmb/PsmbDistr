<?php
namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\DataProvider;
use Ttree\ContentRepositoryImporter\DataType\StringValue;

class NewsDataProvider extends DataProvider {
	/**
	 * @return array
	 */
	public function fetch() {
		$result = [];
		$query = $this->createQuery()
		->select('*')
		->from('tx_news_domain_model_news', 'n')
		->where('n.hidden=0 AND n.deleted=0')
		->orderBy('n.datetime');
		$statement = $query->execute();
		while ($record = $statement->fetch()) {
			$date = new \DateTime();
			$date->setTimestamp($record['datetime']);
			$result[] = [
				'__externalIdentifier' => (int)$record['uid'],
				'__parentPageIdentifier' => 'p' . $record['pid'],
				'title' => StringValue::create($record['title'])->getValue(),
				'teaser' => StringValue::create($record['teaser'])->getValue(),
				'bodytext' => StringValue::create($record['bodytext'])->getValue(),
				'datetime' => $date,
				'important' => $record['istopnews'],
				'announcementPlace' => $record['author_email'] // Don't ask me why...
			];
		}
		$this->count = count($result);
		return $result;
	}
}
