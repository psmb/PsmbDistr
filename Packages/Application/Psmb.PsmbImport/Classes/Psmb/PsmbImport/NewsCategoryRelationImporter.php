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
		// Create places node
		$targetNodeName = 'places';
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

		if (isset($newsNode)) {
			if ($data['tagId'] == 'c9') {
				$newsNode->setProperty('isYandex', true);
			}
			if ($data['tagId'] == 'c87') {
				$newsNode->setProperty('isPhoto', true);
			}
			if ($data['tagId'] == 'c3') {
				$newsNode->setProperty('isVideo', true);
			}
			if ($data['tagId'] == 'c86') {
				$newsNode->setProperty('isAudio', true);
			}
			if ($data['tagId'] == 'c7') {
				$newsNode->setProperty('type', 'ourNews');
			}
			if ($data['tagId'] == 'c94') {
				$newsNode->setProperty('type', 'externalNews');
			}
		}
		if (isset($newsNode) && isset($tagNode)) {
			$parentNodePath = $tagNode->getParentPath();
			if (strrpos($parentNodePath, '/sites/site/projects/exhibitions/', -strlen($parentNodePath)) !== false) {
				$originalTagNode = $tagNode;
				$placeCategory = $this->getPlaceCategoryByTitle($tagNode);
				$this->addTag($newsNode, $placeCategory, 'places');

				// We set tagNode to its parent, to mark exhibition category
				$tagNode = $originalTagNode->getParent();
			}
			$segment = explode('/', $tagNode->getParentPath())[3];
			if (in_array($segment, ['tags', 'collections', 'projects'])) {
				$this->addTag($newsNode, $tagNode, $segment);
			} else {
				$this->log(sprintf('Unrecognized segment "%s"', $segment), LOG_ERR);
			}
		}
	}
	protected function addTag($newsNode, $tagNode, $property) {
		$tags = $newsNode->getProperty($property);
		if (!is_array($tags)) {
			$tags = [];
		}
		if (!in_array($tagNode, $tags)) {
			$tags[] = $tagNode;
			$newsNode->setProperty($property, $tags);
		}
	}
	protected function getPlaceCategoryByTitle($tagNode) {
		$title = $tagNode->getProperty('title');
		$q = new FlowQuery(array($this->storageNode));
		$placeCategory = $q->children()->filter('[title = "' . $title . '"]')->get(0);
		if (!$placeCategory) {
			$nodeName = Slug::create($title)->getValue();
			$nodeTemplate = new NodeTemplate();
			$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Sfi.Site:PlaceTag'));
			$nodeTemplate->setName($nodeName);
			$nodeTemplate->setProperty('uriPathSegment', $nodeName);
			$nodeTemplate->setProperty('title', $title);
			$nodeTemplate->setProperty('coordinates', $tagNode->getProperty('coordinates'));
			$placeCategory = $this->storageNode->createNodeFromTemplate($nodeTemplate);
		}
		return $placeCategory;
	}
}
