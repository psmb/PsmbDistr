<?php

namespace Psmb\PsmbImport;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Repository\ImageRepository;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class SaintImporter extends Importer
{
	/**
	 * @Flow\Inject
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @Flow\Inject
	 * @var ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var ImageRepository
	 */
	protected $imageRepository;

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
			$storageNodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Neos.Neos:Shortcut'));
			$storageNodeTemplate->setProperty('title', 'Жития святых');
			$storageNodeTemplate->setProperty('uriPathSegment', $targetNodeName);
			$storageNodeTemplate->setName($targetNodeName);
			$this->storageNode = $this->siteNode->createNodeFromTemplate($storageNodeTemplate);
		}

		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Sfi.Site:Saint'));
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
		if ($this->skipNodeProcessing($externalIdentifier, '123', $this->siteNode, false)) {
			return null;
		}
		$nodeTemplate->setProperty('title', $title);
		$nodeTemplate->setProperty('uriPathSegment', $externalIdentifier);
		$nodeTemplate->setProperty('originalIdentifier', $externalIdentifier);

		$node = $this->createUniqueNode($this->storageNode, $nodeTemplate, $desiredNodeName);

		$this->importBodytext($node, $data['bodytext']);

		$image = $this->importImage($data['icon']);

		$nodeTemplate->setProperty('image', $image);

		$this->registerNodeProcessing($node, $externalIdentifier);
	}

	protected function importBodytext($sermonNode, $bodytext)
	{
		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Psmb.NodeTypes:Text'));
		$nodeTemplate->setProperty('text', $bodytext);
		$sermonNode->getNode('main')->createNodeFromTemplate($nodeTemplate);
	}

	protected function importImage($filename)
	{
		$resource = $this->resourceManager->importResource($filename);

		$image = new Image($resource);
		$this->imageRepository->add($image);

		$processingInstructions = [];
		return $this->objectManager->get('Neos\Media\Domain\Model\ImageVariant', $image, $processingInstructions);
	}
}
