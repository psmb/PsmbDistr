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
			$segment = explode('/', $tagNode->getParentPath())[3];
			if (in_array($segment, ['tags', 'collections', 'events'])) {
				$tags = $newsNode->getProperty($segment);
				if (!is_array($tags)) {
					$tags = [];
				}
				if (!in_array($tagNode, $tags)) {
					$tags[] = $tagNode;
					$newsNode->setProperty($segment, $tags);
				}
			} else {
				$this->log(sprintf('Unrecognized segment "%s"', $segment), LOG_ERR);
			}
		}
	}
}
