<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Service\NodeServiceInterface;
use Ttree\ContentRepositoryImporter\Importer\Importer as BaseImporter;
use Ttree\ContentRepositoryImporter\DataType\Slug;

abstract class Importer extends BaseImporter
{
  /**
 * @Flow\Inject
 * @var NodeServiceInterface
 */
 protected $nodeService;

 protected function createUniqueNode($parentNode, $nodeTemplate, $proposedNodeName) {
    $nodeName = $this->nodeService->generateUniqueNodeName($parentNode->getPath(), $proposedNodeName);
    $nodeTemplate->setName($nodeName);
    $nodeTemplate->setProperty('uriPathSegment', $nodeName);
    return $parentNode->createNodeFromTemplate($nodeTemplate);
  }
}
