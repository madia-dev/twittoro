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
                        ->getTweetsByUsername($this->get('oro_security.acl_helper'))
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }
}
