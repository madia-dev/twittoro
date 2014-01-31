<?php

namespace Madia\Bundle\TwittoroBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class TweetRepository extends EntityRepository
{
    /**
     * Get opportunities by state
     *
     * @param $aclHelper AclHelper
     * @return array
     *  [
     *      'data' => [id, value]
     *      'labels' => [id, label]
     *  ]
     */
    public function getTweetsByUsername(AclHelper $aclHelper)
    {
        /**
         * SELECT  `username` , COUNT(  `tweet` ) AS  `tweet_count` 
FROM  `madia_twittoro` 
GROUP BY  `username` 
ORDER BY  `tweet_count` DESC 
         * 
         */
        
        $qb = $this->createQueryBuilder('tweets');
        $qb->select('tweets.username', 'COUNT(tweets.tweet) as tweet_count')
             ->groupBy('tweets.username')
             ->orderBy('tweet_count', 'DESC');

        $data = $aclHelper->apply($qb)
             ->getArrayResult();

        $resultData = [];
        $labels = [];

        foreach ($data as $index => $dataValue) {
            $resultData[$index] = [$index, (int)$dataValue['tweet_count']];
            $labels[$index] = $dataValue['username'];
        }

        return ['data' => $resultData, 'labels' => $labels];
    }
}
