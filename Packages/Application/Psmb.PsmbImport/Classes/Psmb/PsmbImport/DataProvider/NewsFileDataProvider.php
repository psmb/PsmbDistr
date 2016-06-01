<?php
namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\DataProvider;
use Ttree\ContentRepositoryImporter\DataType\StringValue;

class NewsFileDataProvider extends DataProvider {
  /**
   * @return array
   */
  public function fetch() {
    $result = [];
    $query = $this->createQuery()
    ->select('*')
    ->from('tx_news_domain_model_file', 'f')
    ->where('f.hidden=0 AND f.deleted=0')
    ->orderBy('f.sorting');
    $statement = $query->execute();
    while ($record = $statement->fetch()) {
      $result[] = [
        '__externalIdentifier' => (int)$record['uid'],
        '__parentIdentifier' => (int)$record['parent'],
        'filename' => StringValue::create($record['file'])->getValue(),
        'title' => StringValue::create($record['title'])->getValue()
      ];
    }
    $this->count = count($result);
    return $result;
  }
}
