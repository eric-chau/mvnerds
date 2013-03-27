<?php

namespace MVNerds\TeamSeekerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

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
		$this->get('mvnerds.team_seeker_manager')->findTeamFromTagOrName('euw', 'mvn3rd');
		
        return $this->render('::base.html.twig');
    }
	
	/**
	 * @Route("/", name="team_seeker_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsTeamSeekerBundle:TeamSeeker:team_seeker_index.html.twig');
	}
	
	/**
	 * @Route("/seek", name="team_seeker_seek_ajax", options={"expose"=true})
	 */
	public function seekAction()
	{
		return new Response();
	}
}
