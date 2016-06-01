<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class NewsCategoryRelationImporter extends Importer
{
  public function process()
  {
    $nodeTemplate = new NodeTemplate();
    $this->processBatch($nodeTemplate);
  }
  /**
   * @param NodeTemplate $nodeTemplate
   * @param array $data
   */
  public function processRecord(NodeTemplate $nodeTemplate, array $data)
  {
    $this->unsetAllNodeTemplateProperties($nodeTemplate);

    $newsRecordMapping = $this->processedNodeService->get('Psmb\PsmbImport\NewsImporter', $data['newsId']);
    if ($newsRecordMapping) {
      $newsNode = $this->siteNode->getNode($newsRecordMapping->getNodePath());
    }

    $tagRecordMapping = $this->processedNodeService->get('Psmb\PsmbImport\CategoryImporter', $data['tagId']);
    if ($tagRecordMapping) {
      $tagNode = $this->siteNode->getNode($tagRecordMapping->getNodePath());
    }

    if (isset($newsNode) && isset($tagNode)) {
      $tags = $newsNode->getProperty('tags');
      if (!in_array($tagNode, $tags)) {
        $tags[] = $tagNode;
        $newsNode->setProperty('tags', $tags);
      }
    }
  }
}
