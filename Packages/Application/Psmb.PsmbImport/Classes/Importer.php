<?php

namespace Psmb\PsmbImport;

use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Service\NodeServiceInterface;
use Ttree\ContentRepositoryImporter\Importer\AbstractImporter as BaseImporter;

abstract class Importer extends BaseImporter
{
	/**
	 * @Flow\Inject
	 * @var NodeServiceInterface
	 */
	protected $nodeService;

	protected function createUniqueNode($parentNode, $nodeTemplate, $proposedNodeName)
	{
		$nodeName = $this->nodeService->generateUniqueNodeName($parentNode->getPath(), $proposedNodeName);
		$nodeTemplate->setName($nodeName);
		$nodeTemplate->setProperty('uriPathSegment', $nodeName);
		return $parentNode->createNodeFromTemplate($nodeTemplate);
	}
}
