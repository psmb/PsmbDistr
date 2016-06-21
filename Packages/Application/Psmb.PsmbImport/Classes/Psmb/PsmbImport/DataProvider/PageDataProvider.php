<?php
namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\DataProvider;
use Ttree\ContentRepositoryImporter\DataType\StringValue;

class PageDataProvider extends DataProvider {
	protected $result;
	/**
	 * @return array
	 */
	public function fetch() {
		$this->result = [];
		$startingPoint = $this->options['startingPoint'] ? $this->options['startingPoint'] : 0;
		$this->fetchByParent($startingPoint);
		$this->count = count($this->result);
		return $this->result;
	}

	private function fetchByParent($parent = 0) {
		$query = $this->createQuery()
			->select('*')
			->from('pages', 'p')
			->where('p.pid = :parent AND p.hidden=0 AND p.deleted=0')
			->setParameter(':parent', $parent)
			->orderBy('p.sorting');
		$statement = $query->execute();
		while ($record = $statement->fetch()) {
			$this->result[] = [
				'__externalIdentifier' => StringValue::create("p" . $record['uid'])->getValue(),
				'__parentIdentifier' => ($record['pid'] == 0 || $record['pid'] == $this->options['startingPoint']) ? null : StringValue::create("p" . $record['pid'])->getValue(),
				'title' => StringValue::create($record['title'])->getValue()
			];
			$this->fetchByParent($record['uid']);
		}
	}
}
