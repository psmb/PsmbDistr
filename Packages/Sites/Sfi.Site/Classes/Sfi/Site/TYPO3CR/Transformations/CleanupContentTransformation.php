<?php
namespace Sfi\Site\TYPO3CR\Transformations;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Migration\Transformations\AbstractTransformation;

class CleanupContentTransformation extends AbstractTransformation {
	/**
	 * @var string
	 */
	protected $propertyName;

	/**
	 * @param string $propertyName
	 * @return void
	 */
	public function setPropertyName($propertyName) {
		$this->propertyName = $propertyName;
	}

	/**
	 * Text exists
	 *
	 * @param \Neos\ContentRepository\Domain\Model\NodeData $node
	 * @return boolean
	 */
	public function isTransformable(\Neos\ContentRepository\Domain\Model\NodeData $node) {
		return $node->hasProperty($this->propertyName);
	}
	/**
	 * Wrap <span or <small tags
	 *
	 * @param \Neos\ContentRepository\Domain\Model\NodeData $node
	 * @return void
	 */
	public function execute(\Neos\ContentRepository\Domain\Model\NodeData $node) {
		$text = $node->getProperty($this->propertyName);

		$text = preg_replace('/^((<small|<span).*?)([\r\n]|$)/mui', '<p>$1</p>', $text);
		$text = preg_replace('/<span\s+?class="interview".*?>(.+?)<\/span>/mui', '<strong>$1</strong>', $text);
		$node->setProperty($this->propertyName, $text);
	}
}
