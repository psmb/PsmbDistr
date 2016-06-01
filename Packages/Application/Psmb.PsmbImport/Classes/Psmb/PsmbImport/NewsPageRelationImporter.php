<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class NewsPageRelationImporter extends Importer
{
  public function process()
  {
    $nodeTemplate = new NodeTemplate();
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

    $newsRecordMapping = $this->processedNodeService->get('Psmb\PsmbImport\NewsImporter', $data['__externalIdentifier']);
    if ($newsRecordMapping === null) {
        $this->log(sprintf('Skip "%s", missing node', $data['__externalIdentifier']), LOG_ERR);
        return null;
    }
    $newsNode = $this->siteNode->getNode($newsRecordMapping->getNodePath());

    $tagRecordMapping = $this->processedNodeService->get('Psmb\PsmbImport\CategoryImporter', $data['__parentPageIdentifier']);
    if ($tagRecordMapping === null) {
        $this->log(sprintf('Skip "%s", missing node', $data['__parentPageIdentifier']), LOG_ERR);
        return null;
    }
    $tagNode = $this->siteNode->getNode($tagRecordMapping->getNodePath());

    $tags = $newsNode->getProperty('tags');
    if ($tags === null) {
      $tags = [];
    }
    if (!in_array($tagNode, $tags)) {
      $tags[] = $tagNode;
      $newsNode->setProperty('tags', $tags);
    }

    return $newsNode;
  }
}
