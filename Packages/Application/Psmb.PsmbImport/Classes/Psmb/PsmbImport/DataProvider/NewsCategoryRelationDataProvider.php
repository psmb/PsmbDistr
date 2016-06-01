<?php
namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\DataProvider;
use Ttree\ContentRepositoryImporter\DataType\StringValue;

class NewsCategoryRelationDataProvider extends DataProvider {
  /**
   * @return array
   */
  public function fetch() {
    $result = [];
    $query = $this->createQuery()
    ->select('*')
    ->from('sys_category_record_mm', 'r')
    ->orderBy('r.sorting');
    $statement = $query->execute();
    while ($record = $statement->fetch()) {
      $result[] = [
        '__externalIdentifier' => $record['uid_local'] . '-' . $record['uid_foreign'],
        '__label' => $record['uid_local'] . ' - ' . $record['uid_foreign'],
        'newsId' => (int)$record['uid_foreign'],
        'tagId' => 'c' . $record['uid_local']
      ];
    }
    $this->count = count($result);
    return $result;
  }
}
