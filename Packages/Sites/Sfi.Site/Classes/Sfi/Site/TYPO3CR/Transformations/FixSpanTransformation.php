<?php
namespace Sfi\Site\TYPO3CR\Transformations;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Migration\Transformations\AbstractTransformation;

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
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeData $node
	 * @return boolean
	 */
	public function isTransformable(\TYPO3\TYPO3CR\Domain\Model\NodeData $node) {
		return $node->hasProperty($this->propertyName);
	}
	/**
	 * Wrap <span or <small tags
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeData $node
	 * @return void
	 */
	public function execute(\TYPO3\TYPO3CR\Domain\Model\NodeData $node) {
		$text = $node->getProperty($this->propertyName);

		$text = preg_replace('/^((<small|<span).*?)([\r\n]|$)/mui', '<p>$1</p>', $text);
		$text = preg_replace('/<span\s+?class="interview".*?>(.+?)<\/span>/mui', '<strong>$1</strong>', $text);
		$node->setProperty($this->propertyName, $text);
	}
}
