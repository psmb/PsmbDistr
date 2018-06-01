<?php
namespace Sfi\Site\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;

class SiteHelper implements ProtectedContextAwareInterface
{
    /**
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
    protected $entityManager;

    /**
     * @param string $url
     * @return integer
     */
    public function resolveAncientUrl($url)
    {
        $sql = "SELECT value_id FROM tx_realurl_uniqalias_old WHERE value_alias='" . $url . "'";
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        if ($result) {
            return intval($result[0]['value_id']);
        } else {
            throw new \Exception('Ancient url not resolved: ' . $url);
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
