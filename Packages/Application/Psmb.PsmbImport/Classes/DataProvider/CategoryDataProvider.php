<?php
namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\DataProvider;

class CategoryDataProvider extends DataProvider {
  protected $result;
  /**
   * @return array
   */
  public function fetch() {
    $this->result = [];
    $this->fetchByParent();
    $this->count = count($this->result);
    return $this->result;
  }

  private function fetchByParent($parent = 0) {
    $query = $this->createQuery()
        ->select('*')
        ->from('sys_category')
        ->where('parent = :parent AND hidden=0 AND deleted=0')
        ->setParameter(':parent', $parent)
        ->orderBy('sorting');
    $statement = $query->execute();
    while ($record = $statement->fetch()) {
      $this->result[] = [
        '__externalIdentifier' => (integer)$record['uid'],
        '__parentIdentifier' => (integer)$record['parent'],
        'title' => String::create($record['title'])->getValue(),
        'replaceVariants' => String::create(str_replace("\n", ", ", $record['altnames']))->getValue()
      ];
      $this->fetchByParent($record['uid']);
    }
  }
}
