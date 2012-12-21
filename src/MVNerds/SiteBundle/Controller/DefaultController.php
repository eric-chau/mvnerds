<?php

namespace MVNerds\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/test-css")
     */
    public function indexAction()
    {
        return $this->render('MVNerdsSiteBundle:Default:index.html.twig');
    }
}
