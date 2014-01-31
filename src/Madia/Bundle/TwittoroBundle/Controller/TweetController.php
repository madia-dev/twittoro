<?php

namespace Madia\Bundle\TwittoroBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Madia\Bundle\TwittoroBundle\Entity\Tweet;

/**
 * @Route("/tweet")
 */
class TweetController extends Controller
{
    /**
     * @Route(
     *      ".{_format}",
     *      name="madia_twittoro_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @Acl(
     *      id="madia_twittoro_index",
     *      type="entity",
     *      class="MadiaTwittoroBundle:Tweet",
     *      permission="VIEW"
     * )
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/create", name="madia_twittoro_create")
     * @Template("MadiaTwittoroBundle:Tweet:update.html.twig")
     * @Acl(
     *      id="madia_twittoro_create",
     *      type="entity",
     *      class="MadiaTwittoroBundle:Tweet",
     *      permission="CREATE"
     * )
     */
    public function createAction()
    {
        $entity = new Tweet();

        return $this->update($entity);
    }

    /**
     * @Route("/view/{id}", name="madia_twittoro_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="madia_twittoro_view",
     *      type="entity",
     *      class="MadiaTwittoroBundle:Tweet",
     *      permission="VIEW"
     * )
     */
    public function viewAction(Tweet $entity)
    {
        return array('entity' => $entity);
    }

    /**
     * @Route("/update/{id}", name="madia_twittoro_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="madia_twittoro_update",
     *      type="entity",
     *      class="MadiaTwittoroBundle:Tweet",
     *      permission="EDIT"
     * )
     */
    public function updateAction(Tweet $entity)
    {
        return $this->update($entity);
    }

    /**
     * @param Tweet $entity
     * @return array
     */
    protected function update(Tweet $entity)
    {
        $request = $this->getRequest();
        $form = $this->createForm($this->get('madia_twittoro.form.type.tweet'), $entity);

        if ('POST' == $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->persist($entity);
                $this->getDoctrine()->getManager()->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('madia.twittoro.saved_message')
                );

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route' => 'madia_twittoro_update',
                        'parameters' => array('id' => $entity->getId()),
                    ),
                    array(
                        'route' => 'madia_twittoro_view',
                        'parameters' => array('id' => $entity->getId()),
                    )
                );
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
    
    /**
     * TODO: move this to a different controller class
     * 
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