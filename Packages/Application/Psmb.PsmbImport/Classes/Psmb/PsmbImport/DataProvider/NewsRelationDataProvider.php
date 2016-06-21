<?php
namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\DataProvider;
use Ttree\ContentRepositoryImporter\DataType\StringValue;

class NewsRelationDataProvider extends DataProvider {
	/**
	 * @return array
	 */
	public function fetch() {
		$result = [];
		$query = $this->createQuery()
		->select('*')
		->from('tx_news_domain_model_news_related_mm', 'r')
		->orderBy('r.sorting');
		$statement = $query->execute();
		while ($record = $statement->fetch()) {
			$result[] = [
				'__label' => $record['uid_local'] . ' - ' . $record['uid_foreign'],
				'newsId' => (int)$record['uid_local'],
				'targetNewsId' => (int)$record['uid_foreign']
			];
		}
		$this->count = count($result);
		return $result;
	}
}
