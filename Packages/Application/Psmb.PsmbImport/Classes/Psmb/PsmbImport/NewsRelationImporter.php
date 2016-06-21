<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class NewsRelationImporter extends Importer
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
		$this->unsetAllNodeTemplateProperties($nodeTemplate);

		$newsRecordMapping = $this->processedNodeService->get('Psmb\PsmbImport\NewsImporter', $data['newsId']);
		if ($newsRecordMapping === null) {
				$this->log(sprintf('Skip "%s", missing node', $data['newsId']), LOG_ERR);
				return null;
		}
		$newsNode = $this->siteNode->getNode($newsRecordMapping->getNodePath());

		$targetRecordMapping = $this->processedNodeService->get('Psmb\PsmbImport\NewsImporter', $data['targetNewsId']);
		if ($targetRecordMapping === null) {
				$this->log(sprintf('Skip "%s", missing node', $data['targetNewsId']), LOG_ERR);
				return null;
		}
		$targetNewsNode = $this->siteNode->getNode($targetRecordMapping->getNodePath());

		$relatedNews = $newsNode->getProperty('relatedNews');
		if ($relatedNews === null) {
			$relatedNews = [];
		}
		if (!in_array($targetNewsNode, $relatedNews)) {
			$relatedNews[] = $targetNewsNode;
			$newsNode->setProperty('relatedNews', $relatedNews);
		}

		return $newsNode;
	}
}
