<?php

namespace Madia\Bundle\TwittoroBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\CronBundle\Command\Logger\OutputLogger;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\ConfigBundle\Config\UserConfigManager;

use Madia\Bundle\TwittoroBundle\Model\Api\Tweets;
use Madia\Bundle\TwittoroBundle\Entity\Tweet;

/**
 * Update tweets command class
 * This class represents the class for the updating tweets cron command.
 * 
 * This will class will try to get the raw JSON response from the twitter api
 * and persist this data for the \Madia\Bundle\TwittoroBundle\Entity\Tweet entity.
 */
class UpdateTweetsCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const COMMAND_NAME   = 'oro:cron:madia:twittoro:update-tweets';

    
    /**
     * {@internaldoc}
     */
    public function getDefaultDefinition()
    {
        return $this->getConfig()->get('madia_twittoro.update_tweets_cron_schedule');
    }

    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Update tweets about a specific hashtag')
            ->addOption('hashtag', null,InputOption::VALUE_OPTIONAL, 'The hashtag you want to import...');
    }

    /**
     * Runs command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        
        $hashtag = $input->getOption('hashtag');
        
        if(!$hashtag) {
            $output->writeln(sprintf('Updating tweets for all hashtags..'));
            $hashtag = 'all';
        }else {
            $output->writeln(sprintf('Updating tweets for hashtag %s..'), $hashtag);
        }
        
        if (!$this->getConfig()->get('madia_twittoro.update_tweets_enabled')) {
            $output->writeln(sprintf('You did not enable the update tweet cron in the System Configuration Settings of your Application'));
            return;
        }

        $oAuthToken = $this->getConfig()->get('madia_twittoro.update_tweets_oauth_access_token');
        $oAuthTokenSecret = $this->getConfig()->get('madia_twittoro.update_tweets_oauth_access_token_secret');
        $consumerKey = $this->getConfig()->get('madia_twittoro.update_tweets_consumer_key');
        $consumerSecret = $this->getConfig()->get('madia_twittoro.update_tweets_consumer_secret');
        $_output = $output;
        $output = new OutputLogger($_output);

        if (!$oAuthToken) {
            $output->notice($this->getTranslator()->trans('madia.twittoro.update_tweets.oauth_access_token_not_configured'));
            return;
        }
        
        if (!$oAuthTokenSecret) {
            $output->notice($this->getTranslator()->trans('madia.twittoro.update_tweets.oauth_access_token_secret_not_configured'));
            return;
        }
        
        if (!$consumerKey) {
            $output->notice($this->getTranslator()->trans('madia.twittoro.update_tweets.consumer_key_not_configured'));
            return;
        }
        
        if (!$consumerSecret) {
            $output->notice($this->getTranslator()->trans('madia.twittoro.update_tweets.consumer_secret_not_configured'));
            return;
        }

        /**
         * some logic here we need to cycle through the following:
         * 1. fetch all tweets from the twitter api
         * 2. prepare tweets data for persisting to db
         * 3. persist data to database.
         */
        
        /** @var Tweets $tweets */
        $tweetsApi = $this->getContainer()->get('madia_twittoro.api_tweets');
        $tweetJsonData = $tweetsApi->makeApiCall($oAuthToken, $oAuthTokenSecret, $consumerKey, $consumerSecret, $hashtag);

        $tweetData = json_decode($tweetJsonData);
        if(isset($tweetData->errors)) {
            foreach($tweetData->errors as $error){
                throw new \Exception('['.$error->code.'] => '.$error->message);
            }
        }
        
        $this->_persistTweetData($tweetData, $hashtag, $_output);
        
        $output->notice($this->getTranslator()->trans('madia.twittoro.update_tweets.tweets_have_been_updated'));

    }

    /**
     * @return UserConfigManager
     */
    protected function getConfig()
    {
        return $this->getContainer()->get('oro_config.user');
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->getContainer()->get('translator');
    }
    
    protected function _persistTweetData($tweetData, $hashtag, $_output) {
        //get entitymanager
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        if($hashtag == 'all') {
            $hashtag = '#orotraining'; 
        }
        //$tweetData is an object of the stdClass..
        //can't handle this object as an array..
        foreach($tweetData->statuses as $tweet) {
           
           //creating new tweet entity
           $tweetEntity = new Tweet();
           
           $tweetEntity->setUsername($tweet->user->screen_name);
           $tweetEntity->setTweet($tweet->text);
           $tweetEntity->setRetweets((int)$tweet->retweet_count);
           $createdAt = new \DateTime($tweet->created_at);           
           $tweetEntity->setTweetStamp($createdAt);
           $tweetEntity->setHashtag($hashtag);
           $tweetEntity->setCreatedAt(new \DateTime());
           $tweetEntity->setUpdatedAt(new \DateTime());
           
           $output = new OutputLogger($_output);
           try {
                
                $entityManager->persist($tweetEntity);
                $entityManager->flush();
                
                //do some logging if the record has been updated.
                $output->notice('record updated: '. $tweet->user->screen_name . ' ' . $tweet->text. ' '. $tweet->retweet_count . ' '. $tweet->created_at. ' '. $hashtag);
 
           } catch (Exception $e) {
               $output->notice('Something went wrong');
               $output->notice($e);
               return;
           }           
       }
    }
}