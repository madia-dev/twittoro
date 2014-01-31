<?php

namespace Madia\Bundle\TwittoroBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
   const DEFAULT_SEND_STATISTICS_CRON_SCHEDULE = '* 08,17 * * *';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('madia_twittoro');
        
        SettingsBuilder::append(
            $rootNode,
        array(
                'update_tweets_cron_schedule' => array('value' => self::DEFAULT_SEND_STATISTICS_CRON_SCHEDULE),
                'update_tweets_oauth_access_token' => array('value' => null),
                'update_tweets_oauth_access_token_secret' => array('value' => null),
                'update_tweets_consumer_key' => array('value' => null),
                'update_tweets_consumer_secret' => array('value' => null),                
                'update_tweets_enabled' => array('value' => false, 'type' => 'boolean'),
            )
        );
        return $treeBuilder;
    }
}