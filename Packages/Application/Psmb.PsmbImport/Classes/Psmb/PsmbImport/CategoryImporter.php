<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\Importer\Importer;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class CategoryImporter extends Importer
{
  /**
   * Import data from the given data provider
   *
   * @return void
   */
  public function process()
  {
    $this->storageNode = $this->siteNode->getNode('categories');
    if ($this->storageNode === null) {
      $storageNodeTemplate = new NodeTemplate();
      $storageNodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('TYPO3.Neos:Shortcut'));
      $storageNodeTemplate->setProperty('title', 'Категории');
      $storageNodeTemplate->setProperty('uriPathSegment', 'categories');
      $storageNodeTemplate->setName('categories');
      $this->storageNode = $this->siteNode->createNodeFromTemplate($storageNodeTemplate);
    }

    $nodeTemplate = new NodeTemplate();
    $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Sfi.Site:Tag'));
    $this->processBatch($nodeTemplate);
  }

  /**
   * @param NodeTemplate $nodeTemplate
   * @param array $data
   * @return NodeInterface
   */
  public function processRecord(NodeTemplate $nodeTemplate, array $data)
  {
    $this->unsetAllNodeTemplateProperties($nodeTemplate);

    $title = $data['title'];
    $externalIdentifier = $data['__externalIdentifier'];
    $nodeName = Slug::create($title)->getValue();
    if ($this->skipNodeProcessing($externalIdentifier, $nodeName, $this->storageNode)) {
      return $this->storageNode->getNode($nodeName);
    }
    $nodeTemplate->setName($nodeName);
    $nodeTemplate->setProperty('title', $title);
    $nodeTemplate->setProperty('originalIdentifier', $externalIdentifier);
    $nodeTemplate->setProperty('replaceVariants', $data['replaceVariants']);

    $parentNode = $data['__parentIdentifier'] ? $this->getCategoryByOriginalId($data['__parentIdentifier']) : $this->storageNode;
    $node = $parentNode->createNodeFromTemplate($nodeTemplate);

    $this->registerNodeProcessing($node, $externalIdentifier);

    return $node;
  }

  protected function getCategoryByOriginalId($id) {
    $q = new FlowQuery(array($this->siteNode));
    return $q->find('[instanceof Sfi.Site:Tag]')->filter('[originalIdentifier = "' . $id  .'"]')->get(0);
  }
}
