<?php

namespace Madia\Bundle\TwittoroBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class TweetRepository extends EntityRepository
{
    /**
     * Get get tweets the tweet count grouped by the username
     * Some usernames have a couple of tweets and those need 
     * to be on top of the list. The list is ordered by tweet_count
     * per username in a descending order (max count first).
     *
     * @param $aclHelper AclHelper
     * @return array
     *  [
     *      'data' => [id, value]
     *      'labels' => [id, label]
     *  ]
     */
    public function getTweetsByUsername(AclHelper $aclHelper, $hashtag)
    {
        /**
         * Query for getting the tweets by username
         * SELECT  `username` , COUNT(  `tweet` ) AS  `tweet_count` 
         * FROM  `madia_twittoro` 
         * GROUP BY  `username` 
         * ORDER BY  `tweet_count` DESC 
         * 
         */
        $hashtag = '#'.$hashtag;
        $qb = $this->createQueryBuilder('tweets');
        $qb->select('tweets.username', 'COUNT(tweets.tweet) as tweet_count')
             ->where('tweets.hashtag = :hashtag')
             ->setParameter('hashtag', $hashtag)
             ->groupBy('tweets.username')
             ->orderBy('tweet_count', 'DESC');

        $data = $aclHelper->apply($qb)
             ->getArrayResult();

        $resultData = [];
        $labels = [];
        $counter = 1;
        foreach ($data as $index => $dataValue) {
            if($counter < 10) {
                $resultData[$index] = [$index, (int)$dataValue['tweet_count']];
                $labels[$index] = $dataValue['username'];
                $counter++;
            }
        }

        return ['data' => $resultData, 'labels' => $labels];
    }
    
    public function findOneByHashtag($hashtag) {
        $hashtag = '#'.$hashtag;
        $qb = $this->createQueryBuilder('h');
        $result = $qb->select('COUNT(h)')
            ->where('h.hashtag = :hashtag')
            ->setParameter('hashtag', $hashtag)    
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function getMaxIdForHashtag($hashtag) {
        $hashtag = '#'.$hashtag;
        $qb = $this->createQueryBuilder('h');
        $result = $qb->select('h.maxId')
            ->where('h.hashtag = :hashtag')
            ->setParameter('hashtag', $hashtag)
            ->orderBy('h.maxId', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }
}
