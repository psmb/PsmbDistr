<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Service\NodeServiceInterface;
use Ttree\ContentRepositoryImporter\Importer\Importer as BaseImporter;
use Ttree\ContentRepositoryImporter\DataType\Slug;

use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Media\Domain\Model\Image;
use TYPO3\Media\Domain\Model\ImageVariant;
use TYPO3\Media\Domain\Repository\ImageRepository;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;

abstract class Importer extends BaseImporter
{
	/**
	* @Flow\Inject
	* @var NodeServiceInterface
	*/
	protected $nodeService;

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

	protected function createUniqueNode($parentNode, $nodeTemplate, $proposedNodeName) {
		// run ./flow node:repair later on to generate nodepath segments
		$nodeName = $this->nodeService->generateUniqueNodeName($parentNode->getPath());
		$nodeTemplate->setName($nodeName);
		$nodeTemplate->setProperty('uriPathSegment', $nodeName);
		return $parentNode->createNodeFromTemplate($nodeTemplate);
	}

	protected function importImage($filename) {
		$resource = $this->resourceManager->importResource($filename);

		$image = new Image($resource);
		$this->imageRepository->add($image);

		$processingInstructions = [];
		return $this->objectManager->get('TYPO3\Media\Domain\Model\ImageVariant', $image, $processingInstructions);
	}

	protected function getFilePath($fileName) {
		if (in_array(pathinfo(strtolower($fileName), PATHINFO_EXTENSION), ["jpg", "jpeg", "gif", "png"])) {
			$filePath = FLOW_PATH_ROOT . $fileName;
			if (!file_exists($filePath)) {
				$this->log("Missing file: " . $filePath);
			} else {
				return $filePath;
			}
		} else {
			$this->log("Illegal mediaItem file extension of file: " . $fileName);
			return null;
		}
	}
}
