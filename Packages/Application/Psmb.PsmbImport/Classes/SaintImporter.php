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

		$recordMapping = $this->getNodeProcessing($externalIdentifier);
		if ($recordMapping !== null) {
			$node = $this->storageNode->getContext()->getNodeByIdentifier($recordMapping->getNodeIdentifier());
			if ($node === null) {
				throw new \Exception(sprintf('Failed retrieving existing node for update. External identifier: %s Node identifier: %s. Maybe the record mapping in the database does not match the existing (imported) nodes anymore.', $externalIdentifier, $recordMapping->getNodeIdentifier()), 1462971366086);
			}
			if ($data['icon']) {
				$image = $this->importImage($data['icon']);
				$node->setProperty('image', $image);
			}
		} else {
			$nodeTemplate->setProperty('title', $title);
			$nodeTemplate->setProperty('uriPathSegment', $externalIdentifier);
			$nodeTemplate->setProperty('originalIdentifier', $externalIdentifier);

			$node = $this->storageNode->createNodeFromTemplate($nodeTemplate);

			$this->importBodytext($node, $data['bodytext']);



			$this->registerNodeProcessing($node, $externalIdentifier);
		}
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
