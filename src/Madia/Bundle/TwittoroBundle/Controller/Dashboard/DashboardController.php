<?php

namespace Madia\Bundle\TwittoroBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/tweets_username/chart/{widget}",
     *      name="madia_twittoro_dashboard_tweets_by_username_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("MadiaTwittoroBundle:Dashboard:tweetsByUsername.html.twig")
     */
    public function tweetsByUsernameAction($widget)
    {
        return array_merge(
            [
                'items' => $this->getDoctrine()
                        ->getRepository('MadiaTwittoroBundle:Tweet')
                        ->getTweetsByUsername($this->get('oro_security.acl_helper'), 'orocrm')
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }

    /**
     * @Route(
     *      "/tweets_username/widget/",
     *      name="madia_twittoro_dashboard_number_of_tweets_widget"
     * )
     * @Template("MadiaTwittoroBundle:Dashboard:numberOfTweets.html.twig")
     */
    public function numberOfTweetsAction()
    {
        return 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('MadiaTwittoroBundle:Tweet')
                        ->getNumberOfTweets($this->get('oro_security.acl_helper'), 'orocrm')
            ];
    }
}
