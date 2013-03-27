<?php

namespace MVNerds\TeamSeekerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/team-seeker")
 */
class TeamSeekerController extends Controller
{
    /**
     * @Route("/test")
	 * 
	 * "PAS TOUCHE, A MOI !" - Ro0ny
     */
    public function testAction()
    {
        return $this->render('::base.html.twig');
    }
}
