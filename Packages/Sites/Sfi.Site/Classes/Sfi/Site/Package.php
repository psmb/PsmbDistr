<?php
namespace Sfi\Site;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\Package as BasePackage;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\TYPO3CR\Domain\Model\Workspace;

/**
 * Psmb.Newsletter
 */
class Package extends BasePackage
{
    /**
     * Capture firstPublicationDateTime property of a node when first publishing to live workspace
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(Workspace::class, 'beforeNodePublishing', function ($node, $targetWorkspace) use ($bootstrap) {
            if ($targetWorkspace->getName() === 'live' &&
                $node->hasProperty('firstPublicationDateTime') &&
                !$node->getProperty('firstPublicationDateTime')
            ) {
                $node->setProperty('firstPublicationDateTime', new \DateTime());
                $bootstrap->getObjectManager()->get(PersistenceManagerInterface::class)->persistAll();
            }
        });
    }
}