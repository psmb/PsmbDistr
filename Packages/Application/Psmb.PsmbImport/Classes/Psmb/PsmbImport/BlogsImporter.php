<?php
namespace Psmb\PsmbImport;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class BlogsImporter extends Importer
{
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
			$storageNodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('TYPO3.Neos:Shortcut'));
			$storageNodeTemplate->setProperty('title', $targetNodeName);
			$storageNodeTemplate->setProperty('uriPathSegment', $targetNodeName);
			$storageNodeTemplate->setName($targetNodeName);
			$this->storageNode = $this->siteNode->createNodeFromTemplate($storageNodeTemplate);
		}

		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Sfi.Site:Blog'));
		$this->processBatch($nodeTemplate);
	}

	/**
	 * @param NodeTemplate $nodeTemplate
	 * @param array $data
	 */
	public function processRecord(NodeTemplate $nodeTemplate, array $data)
	{
		if (!isset($data['author'])) {
			$this->log("Author empty: " . print_r($data, 1));
			return null;
		}
		if (!isset($data['title'])) {
			$this->log("Title empty: " . print_r($data, 1));
			return null;
		}
		if (!isset($data['url'])) {
			$this->log("Url empty: " . print_r($data, 1));
			return null;
		}
		if (!isset($data['userpic'])) {
			$this->log("Usserpic empty: " . print_r($data, 1));
			return null;
		}

		$this->unsetAllNodeTemplateProperties($nodeTemplate);

		$title = $data['title'];
		$externalIdentifier = $data['__externalIdentifier'];
		$desiredNodeName = Slug::create($title)->getValue();
		$author = $this->createAuthor($data['author'], $data['userpic']);
		$nodeTemplate->setProperty('author', $author);
		$nodeTemplate->setProperty('title', $title);
		$nodeTemplate->setProperty('isExternal', true);
		$nodeTemplate->setProperty('url', $data['url']);

		$node = $this->createUniqueNode($this->storageNode, $nodeTemplate, $desiredNodeName);

		$this->registerNodeProcessing($node, $externalIdentifier);
	}

	protected function createAuthor($name, $userpic) {
		$authorsNodeName = 'authors';
		$authorStorageNode = $this->siteNode->getNode($authorsNodeName);
		if ($authorStorageNode === null) {
			$authorStorageNodeTemplate = new NodeTemplate();
			$authorStorageNodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('TYPO3.Neos:Shortcut'));
			$authorStorageNodeTemplate->setProperty('title', $authorsNodeName);
			$authorStorageNodeTemplate->setProperty('uriPathSegment', $authorsNodeName);
			$authorStorageNodeTemplate->setName($authorsNodeName);
			$authorStorageNode = $this->siteNode->createNodeFromTemplate($authorStorageNodeTemplate);
		}

		$q = new FlowQuery(array($authorStorageNode));
		$author = $q->find('[instanceof Sfi.Site:Author]')->filter('[name = "' . $name  .'"]')->get(0);
		if (!$author) {
			$nodeTemplate = new NodeTemplate();
			$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Sfi.Site:Author'));
			$nodeTemplate->setProperty('name', $name);

			$filePath = $this->getFilePath('userpics/' . $userpic);
			if ($filePath) {
				$image = $this->importImage($filePath);
				$nodeTemplate->setProperty('photo', $image);
			}

			$author = $authorStorageNode->createNodeFromTemplate($nodeTemplate);
		}
		return $author;
	}
}
