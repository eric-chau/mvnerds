<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ChampionController extends Controller
{
    /**
     * @Route("/hello/{name}", name="champions_index")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }
}
