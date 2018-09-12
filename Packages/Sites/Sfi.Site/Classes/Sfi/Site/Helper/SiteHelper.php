<?php
namespace Sfi\Site\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Flowpack\ElasticSearch\ContentRepositoryAdaptor\LoggerInterface;

class SiteHelper implements ProtectedContextAwareInterface
{
    /**
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
    protected $entityManager;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string $url
     * @return integer
     */
    public function resolveAncientUrl($url)
    {
		if (!$url) {
			$this->logger->log('Ancient url not resolved: empty url', \LOG_NOTICE);
			return null;
		}
        $sql = "SELECT value_id FROM tx_realurl_uniqalias_old WHERE value_alias='" . $url . "'";
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        if ($result) {
            return intval($result[0]['value_id']);
        } else {
            $this->logger->log(sprintf('Ancient url not resolved: %s', $url), \LOG_NOTICE);
            return null;
        }
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
