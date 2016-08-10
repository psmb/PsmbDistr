<?php
namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\DataProvider;
use Ttree\ContentRepositoryImporter\DataType\StringValue;

class BlogsDataProvider extends DataProvider {
	/**
	 * @return array
	 */
	public function fetch() {
		$result = [];
		$query = $this->createQuery()
		->select('*')
		->from('tt_content', 't')
		->where('t.hidden=0 AND t.deleted=0 AND t.pid = 344 AND t.CType="fluidcontent_content"')
		->orderBy('t.sorting');
		$statement = $query->execute();
		while ($record = $statement->fetch()) {
			$xml = new \SimpleXMLElement($record['pi_flexform']);
			$content = [];
			foreach($xml->data->sheet->language->field as $el) {
				$key = (string) $el['index'];
				$content[$key] = (string) $el->value;
			}
			if (isset($content['url'])) {
				$result[] = [
					'__externalIdentifier' => (int)$record['uid'],
					'url' => $content['url'],
					'author' => $content['author'],
					'userpic' => str_replace('fileadmin/userpics/', '', $content['image']),
					'title' => $content['title']
				];
			}
		}
		$this->count = count($result);
		return $result;
	}
}
