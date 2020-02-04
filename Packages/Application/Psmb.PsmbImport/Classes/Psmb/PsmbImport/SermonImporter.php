<?php

namespace Psmb\PsmbImport;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Ttree\ContentRepositoryImporter\DataType\Slug;

class SermonImporter extends Importer
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
			$storageNodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Neos.Neos:Shortcut'));
			$storageNodeTemplate->setProperty('title', 'Проповеди');
			$storageNodeTemplate->setProperty('uriPathSegment', $targetNodeName);
			$storageNodeTemplate->setName($targetNodeName);
			$this->storageNode = $this->siteNode->createNodeFromTemplate($storageNodeTemplate);
		}

		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Sfi.Site:Sermon'));
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
		$authorNode = $this->getOrCreateTagByName($data['author']);
		$nodeTemplate->setProperty('author', $authorNode);
		$nodeTemplate->setProperty('title', $title);
		$nodeTemplate->setProperty('teaser', $data['teaser']);
		$nodeTemplate->setProperty('magicDate', $data['magicDate']);
		$nodeTemplate->setProperty('originalIdentifier', $externalIdentifier);

		$node = $this->createUniqueNode($this->storageNode, $nodeTemplate, $desiredNodeName);

		$this->importBodytext($node, $data['bodytext']);

		$this->registerNodeProcessing($node, $externalIdentifier);
	}

	protected function importBodytext($sermonNode, $bodytext)
	{
		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Psmb.NodeTypes:Text'));
		$nodeTemplate->setProperty('text', $bodytext);
		$sermonNode->getNode('main')->createNodeFromTemplate($nodeTemplate);
	}

	protected function getOrCreateTagByName($name)
	{
		$peopleNode = $this->siteNode->getNode('tags/people');
		$q = new FlowQuery([$peopleNode]);
		$node = $q->find('[instanceof Sfi.Site:Tag]')->filter('[title = "' . $name	. '"]')->get(0);
		if (!$node) {
			$desiredNodeName = Slug::create($name)->getValue();
			$nodeTemplate = new NodeTemplate();
			$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Sfi.Site:Tag'));
			$nodeTemplate->setProperty('title', $name);
			$nodeTemplate->setProperty('uriPathSegment', $desiredNodeName);
			$nodeTemplate->setName($desiredNodeName);
			$node = $peopleNode->createNodeFromTemplate($nodeTemplate);
		}
		return $node;
	}
}
