<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\DataType\Slug;


class NewsContentImporter extends Importer
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
		// $this->log(print_r($data, 1));
		$this->unsetAllNodeTemplateProperties($nodeTemplate);

		$externalIdentifier = $data['__externalIdentifier'];
		if ($this->skipNodeProcessing($externalIdentifier, '123', $this->siteNode, false)) {
			return null;
		}

		$newsRecordMapping = $this->processedNodeService->get('Psmb\PsmbImport\NewsImporter', $data['__externalIdentifier']);
		if ($newsRecordMapping === null) {
				$this->log(sprintf('Skip "%s", missing node', $data['__externalIdentifier']), LOG_ERR);
				return null;
		}
		$newsNode = $this->siteNode->getNode($newsRecordMapping->getNodePath());
		$newsNode->setProperty('credit', $data['credit']);

		if (isset($data['image']['filename'])) {
			$filePath = $this->getFilePath('uploads/' . $data['image']['filename']);
			if ($filePath) {
				$image = $this->importImage($filePath);
				$newsNode->setProperty('image', $image);
			}
		}

		if (isset($data['coverMedia'])) {
			$coverCollection = $newsNode->getNode('cover');
			$coverNodeTemplate = $this->processContentItem($data['coverMedia']);
			$coverCollection->createNodeFromTemplate($coverNodeTemplate);
		}

		if (is_array($data['main'])) {
			$mainCollection = $newsNode->getNode('main');
			foreach ($data['main'] as $contentItem) {
				$nodeTemplate = $this->processContentItem($contentItem);
				$mainCollection->createNodeFromTemplate($nodeTemplate);
			}
		}

		if (is_array($data['gallery'])) {
			$galleryCollection = $newsNode->getNode('gallery');
			foreach ($data['gallery'] as $contentItem) {
				$nodeTemplate = $this->processContentItem($contentItem);
				$galleryCollection->createNodeFromTemplate($nodeTemplate);
			}
		}

		$this->registerNodeProcessing($newsNode, $externalIdentifier);
	}

	protected function processContentItem($contentItem) {
		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($contentItem['_type']));
		switch ($contentItem['_type']) {
			case 'Psmb.NodeTypes:Text':
				$nodeTemplate->setProperty('text', $contentItem['text']);
				break;
			case 'Psmb.NodeTypes:Image':
				$filePath = $this->getFilePath('uploads/' . $contentItem['filename']);
				if ($filePath) {
					$image = $this->importImage($filePath);
					$nodeTemplate->setProperty('image', $image);
					$nodeTemplate->setProperty('caption', $contentItem['caption']);
				}
				break;
			case 'Sfi.YouTube:YouTube':
				$nodeTemplate->setProperty('videoUrl', $contentItem['videoUrl']);
				$nodeTemplate->setProperty('caption', $contentItem['caption']);
				break;
			default:
				$nodeTemplate->setProperty('text', $contentItem['text']);
				break;
		}
		return $nodeTemplate;
	}
}
