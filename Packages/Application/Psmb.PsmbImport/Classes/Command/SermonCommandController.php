<?php

namespace Psmb\PsmbImport\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Service\NodeServiceInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * CLI commands related to Sermon import and management.
 * @Flow\Scope("singleton")
 */
class SermonCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeServiceInterface
     */
    protected $nodeService;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Log\SystemLoggerInterface
     */
    protected $logger;

    /**
     * Assign categories to sermons based on JSON mapping files.
     *
     * Reads sermon data (hash + categories) and a hash-to-uriPathSegment mapping,
     * finds the corresponding sermon and category nodes (creating categories if needed),
     * and assigns the categories to the sermon's 'categories' property.
     *
     * @param bool $dryRun If set, no changes will be persisted
     */
    public function assignCategoriesCommand(
        bool $dryRun = false
    ): void {
        $workspace = 'live';
        $siteNodePath = '/sites/site';
        $sermonDataPath = '/data/www-provisioned/sermons_out.json';
        $hashToIdPath = '/data/www-provisioned/hashToSermonId.json';
        $sermonParentNodePath = 's';
        $categoryParentNodePath = 'node-iwmlbogowo5o2';
        $sermonNodeType = 'Sfi.Site:Sermon';
        $categoryNodeType = 'Sfi.Site:Tag';
        $categoryPropertyName = 'categories';

        $context = $this->contextFactory->create(['workspaceName' => $workspace]);
        $siteNode = $context->getNode($siteNodePath);

        if ($siteNode === null) {
            $this->outputLine('<error>Site node not found at path "%s"</error>', [$siteNodePath]);
            $this->quit(1);
        }

        $sermonStorageNode = $siteNode->getNode($sermonParentNodePath);
        if ($sermonStorageNode === null) {
            $this->outputLine('<error>Sermon storage node not found at path "%s" relative to site node "%s"</error>', [$sermonParentNodePath, $siteNodePath]);
            $this->quit(1);
        }

        $categoryStorageNode = $siteNode->getNode($categoryParentNodePath);
        if ($categoryStorageNode === null) {
            $this->outputLine('<comment>Category storage node not found at path "%s" relative to site node "%s". Attempting to create.</comment>', [$categoryParentNodePath, $siteNodePath]);
            // Logic to create category storage node if needed (e.g., using NodeTemplate)
            // For now, we'll error out if it doesn't exist, assuming it should be pre-created.
            // If creation is desired, implement similar logic as in the original SermonImporter::process()
            $this->outputLine('<error>Category storage node creation not implemented. Please ensure node exists at "%s".</error>', [$siteNodePath . '/' . $categoryParentNodePath]);
            $this->quit(1);
            // Placeholder: $categoryStorageNode = $this->createCategoryStorageNode($siteNode, $categoryParentNodePath);
        }

        // --- Read JSON data ---
        if (!file_exists($sermonDataPath)) {
            $this->outputLine('<error>Sermon data file not found at "%s"</error>', [$sermonDataPath]);
            $this->quit(1);
        }
        if (!file_exists($hashToIdPath)) {
            $this->outputLine('<error>Hash to ID mapping file not found at "%s"</error>', [$hashToIdPath]);
            $this->quit(1);
        }

        $sermonsData = json_decode(file_get_contents($sermonDataPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->outputLine('<error>Error decoding sermon data JSON "%s": %s</error>', [$sermonDataPath, json_last_error_msg()]);
            $this->quit(1);
        }

        $hashToIdMap = json_decode(file_get_contents($hashToIdPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->outputLine('<error>Error decoding hash to ID map JSON "%s": %s</error>', [$hashToIdPath, json_last_error_msg()]);
            $this->quit(1);
        }

        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $nodesToPublish = [];

        $this->outputLine('Starting category assignment process...');
        if ($dryRun) {
            $this->outputLine('<comment>Dry run enabled. No changes will be persisted.</comment>');
        }

        // --- Process Sermons ---
        foreach ($sermonsData as $sermonEntry) {
            $hash = $sermonEntry['id'] ?? null;
            $categoryNamesOriginal = $sermonEntry['categories'] ?? [];
            $categoryNames = array_slice($categoryNamesOriginal, 0, 3);

            if (!$hash || empty($categoryNames)) {
                $this->logger->log(sprintf('Skipping entry due to missing hash or categories: %s', json_encode($sermonEntry, JSON_UNESCAPED_UNICODE)));
                $skippedCount++;
                continue;
            }

            $uriPathSegment = $hashToIdMap[$hash] ?? null;
            if (!$uriPathSegment) {
                $this->logger->log(sprintf('Skipping sermon hash %s: uriPathSegment not found in map.', $hash));
                $skippedCount++;
                continue;
            }

            /** @var NodeInterface $sermonNode */
            $sermonNode = $sermonStorageNode->getNode($uriPathSegment);

            if ($sermonNode === null) {
                $this->logger->log(sprintf('Skipping sermon hash %s: Node with pathSegment "%s" not found under %s.', $hash, $uriPathSegment, $sermonStorageNode->getPath()));
                $skippedCount++;
                continue;
            }

            if ($sermonNode->getNodeType()->getName() !== $sermonNodeType) {
                $this->logger->log(sprintf('Skipping node %s: Expected type %s, but got %s.', $sermonNode->getPath(), $sermonNodeType, $sermonNode->getNodeType()->getName()));
                $skippedCount++;
                continue;
            }

            try {
                $categoryNodes = [];
                foreach ($categoryNames as $categoryName) {
                    $categoryNode = $this->getOrCreateCategoryByName($categoryName, $categoryStorageNode, $categoryNodeType, $dryRun);
                    if ($categoryNode) {
                        $categoryNodes[] = $categoryNode;
                        // Track nodes that might need publishing (newly created categories)
                        if (!isset($nodesToPublish[$categoryNode->getPath()])) {
                            $nodesToPublish[$categoryNode->getPath()] = $categoryNode;
                        }
                    }
                }

                if (!empty($categoryNodes)) {
                    $this->outputLine('Updating categories for sermon: %s', [$sermonNode->getPath()]);
                    if (!$dryRun) {
                        $sermonNode->setProperty($categoryPropertyName, $categoryNodes);
                        if (!isset($nodesToPublish[$sermonNode->getPath()])) {
                            $nodesToPublish[$sermonNode->getPath()] = $sermonNode;
                        }
                    }
                    $this->logger->log(sprintf('Assigned %d categories to sermon %s', count($categoryNodes), $sermonNode->getPath()));
                    $processedCount++;
                } else {
                    $this->logger->log(sprintf('No valid category nodes found/created for sermon %s', $sermonNode->getPath()));
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $this->logger->log(sprintf('Error processing sermon %s (%s): %s', $uriPathSegment, $hash, $e->getMessage()), ['exception' => $e]);
                $this->outputLine('<error>Error processing sermon %s (%s): %s</error>', [$uriPathSegment, $hash, $e->getMessage()]);
                $errorCount++;
            }
        }

        // --- Publish Changes ---
        if (!$dryRun && count($nodesToPublish) > 0) {
            $this->outputLine('Publishing %d changed nodes...', [count($nodesToPublish)]);
            // In Flow > 7, publishNodes might be preferred if available and suitable.
            // For broader compatibility, persistAll() is used here. Consider implications.
            try {
                $this->persistenceManager->persistAll();
                $this->outputLine('<success>Changes persisted successfully.</success>');
            } catch (\Exception $e) {
                $this->logger->log('Error persisting changes: ' . $e->getMessage(), ['exception' => $e]);
                $this->outputLine('<error>Error persisting changes: %s</error>', [$e->getMessage()]);
                $errorCount++; // Count persistence errors
            }
        } elseif ($dryRun) {
            $this->outputLine('Dry run finished. %d nodes would have been persisted.', [count($nodesToPublish)]);
        } else {
            $this->outputLine('No changes to persist.');
        }


        $this->outputLine('Category assignment finished.');
        $this->outputLine('Processed: %d, Skipped: %d, Errors: %d', [$processedCount, $skippedCount, $errorCount]);
        $this->quit($errorCount > 0 ? 1 : 0);
    }

    /**
     * Finds or creates a category tag node by its name under the given parent node.
     *
     * @param string $name The name (title) of the category
     * @param NodeInterface $parentNode The parent node for categories
     * @param string $nodeTypeName The node type for the category tag
     * @param bool $dryRun If true, node creation will be skipped
     * @return NodeInterface|null The found or created category node, or null on error/skip
     * @throws \Exception
     */
    protected function getOrCreateCategoryByName(string $name, NodeInterface $parentNode, string $nodeTypeName, bool $dryRun): ?NodeInterface
    {
        $trimmedName = trim($name);
        if (empty($trimmedName)) {
            $this->logger->log('Skipping category creation/retrieval: name is empty.');
            return null;
        }

        $context = $parentNode->getContext(); // Use context from parent

        // Sanitize name for query. Basic quote escaping. Consider more robust slugification/sanitization if needed.
        $sanitizedName = str_replace('"', '\"', $trimmedName);

        $q = new FlowQuery([$parentNode]);
        /** @var NodeInterface $node */
        $node = $q->find(sprintf('[instanceof %s]', $nodeTypeName))
            ->filter(sprintf('[title = "%s"]', $sanitizedName))
            ->get(0);

        if ($node === null) {
            $this->logger->log(sprintf('Category "%s" not found, creating new node.', $trimmedName));
            if ($dryRun) {
                $this->outputLine('Dry Run: Would create category "%s"', [$trimmedName]);
                return null; // Cannot return a non-existent node in dry run
            }

            $nodeTemplate = new NodeTemplate();
            $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($nodeTypeName));
            $nodeTemplate->setProperty('title', $trimmedName);

            try {
                $node = $parentNode->createNodeFromTemplate($nodeTemplate);
                $this->logger->log(sprintf('Created category node: %s', $node->getPath()));
                $this->outputLine('Created category: %s', [$node->getPath()]);
                // No need to persist here, handled at the end
                return $node;
            } catch (\Exception $e) {
                $this->logger->log(sprintf('Failed to create category node "%s": %s', $trimmedName, $e->getMessage()), ['exception' => $e]);
                $this->outputLine('<error>Failed to create category node "%s": %s</error>', [$trimmedName, $e->getMessage()]);
                // Re-throw or return null depending on desired error handling
                // Returning null to allow the main loop to continue
                return null;
            }
        } else {
            $this->logger->log(sprintf('Found existing category "%s": %s', $trimmedName, $node->getPath()));
            return $node;
        }
    }
}
