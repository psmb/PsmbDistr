<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class NewsImporter extends Importer
{
  /**
   * Import data from the given data provider
   *
   * @return void
   */
  public function process()
  {
    $targetNodeName = $this->options['targetNode'];
    $this->storageNode = $this->siteNode->getNode($targetNodeName);
    if ($this->storageNode === null) {
      $storageNodeTemplate = new NodeTemplate();
      $storageNodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('TYPO3.Neos:Shortcut'));
      $storageNodeTemplate->setProperty('title', $targetNodeName);
      $storageNodeTemplate->setProperty('uriPathSegment', $targetNodeName);
      $storageNodeTemplate->setName($targetNodeName);
      $this->storageNode = $this->siteNode->createNodeFromTemplate($storageNodeTemplate);
    }

    $nodeTemplate = new NodeTemplate();
    $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Sfi.Site:News'));
    $this->processBatch($nodeTemplate);
  }

  /**
   * @param NodeTemplate $nodeTemplate
   * @param array $data
   */
  public function processRecord(NodeTemplate $nodeTemplate, array $data)
  {
    $this->unsetAllNodeTemplateProperties($nodeTemplate);

    $title = $data['title'];
    $externalIdentifier = $data['__externalIdentifier'];
    $desiredNodeName = Slug::create($title)->getValue();
    // if ($this->skipNodeProcessing($externalIdentifier, '123', $this->siteNode, false)) {
      // return null;
    // }
    $nodeTemplate->setProperty('title', $title);
    $nodeTemplate->setProperty('teaser', $data['teaser']);
    $nodeTemplate->setProperty('important', $data['important']);
    $nodeTemplate->setProperty('date', $data['datetime']);
    $nodeTemplate->setProperty('announcementPlace', $data['announcementPlace']);
    $nodeTemplate->setProperty('originalIdentifier', $externalIdentifier);

    $node = $this->storageNode->createNodeFromTemplate($nodeTemplate);
    $node = $this->createUniqueNode($this->storageNode, $nodeTemplate, $desiredNodeName);

    $this->registerNodeProcessing($node, $externalIdentifier);
  }
}
