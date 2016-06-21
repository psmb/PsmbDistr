<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Media\Domain\Model\Asset;
use TYPO3\Media\Domain\Repository\AssetRepository;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class NewsFileImporter extends Importer
{
	/**
	 * @Flow\Inject
	 * @var ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var AssetRepository
	 */
	protected $assetRepository;

	public function process()
	{
		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Psmb.NodeTypes:Asset'));
		$this->processBatch($nodeTemplate);
	}

	/**
	 * @param NodeTemplate $nodeTemplate
	 * @param array $data
	 */
	public function processRecord(NodeTemplate $nodeTemplate, array $data)
	{
		$this->unsetAllNodeTemplateProperties($nodeTemplate);

		$externalIdentifier = $data['__externalIdentifier'];
		if ($this->skipNodeProcessing($externalIdentifier, '123', $this->siteNode, false)) {
			return null;
		}

		$newsRecordMapping = $this->processedNodeService->get('Psmb\PsmbImport\NewsImporter', $data['__parentIdentifier']);
		if ($newsRecordMapping) {
			$newsNode = $this->siteNode->getNode($newsRecordMapping->getNodePath());
			$mainCollection = $newsNode->getNode('main');

			$asset = $this->importFile($data['filename']);
			$nodeTemplate->setProperty('asset', $asset);
			$nodeTemplate->setProperty('title', $data['title']);
			$mainCollection->createNodeFromTemplate($nodeTemplate);
			$this->registerNodeProcessing($newsNode, $externalIdentifier);
		} else {
			$this->log("No news node with identifier " . $data['__parentIdentifier']);
			return null;
		}
	}

	protected function importFile ($fileName) {
		$filePath = FLOW_PATH_ROOT . 'uploads/' . $fileName;
		$resource = $this->resourceManager->importResource($filePath);
		$asset = new Asset($resource);
		$this->assetRepository->add($asset);
		return $asset;
	}
}
