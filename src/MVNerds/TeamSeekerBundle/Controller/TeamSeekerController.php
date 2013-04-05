<?php

namespace MVNerds\TeamSeekerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\SecurityExtraBundle\Exception\InvalidArgumentException;
use MVNerds\TeamSeekerBundle\Exception\InvalidTeamNameOrTagException;

/**
 * @Route("/team-seeker")
 */
class TeamSeekerController extends Controller
{	
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
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'Request should be AJAXienne and POST!');
		}
		
		$region = $request->get('region', null);
		$teamTagOrName = $request->get('team_tag_or_name', null);
		if (null == $region || null == $teamTagOrName) {
			throw new InvalidArgumentException('Some parameters are missing!');
		}
		
		$team = null;
		try {
			$team = $this->get('mvnerds.team_seeker_manager')->findTeamByTagOrName($region, $teamTagOrName);
		}
		catch (InvalidTeamNameOrTagException $e) {
			return new Response($this->get('translator')->trans('TeamSeeker.seek.unknow_tag_or_name.%tagOrName%.%region%', array(
				'%tagOrName%' => $teamTagOrName, 
				'%region%' => $region
			)), 404);
		}
		
		if (null == $team) {
			return new Response($this->get('translator')->trans('profile_index.elophant.afk'), 503);
		}
		
		return $this->render('MVNerdsTeamSeekerBundle:TeamSeeker:team_overview_container.html.twig', array(
			'team'				=> $team,
			'region'			=> $region,
			'team_tag_or_name'	=> $teamTagOrName
		));
	}
	
	/**
	 * @Route("/get-player-solo-league", name="team_seeker_get_player_solo_league", options={"expose"=true})
	 */
	public function getTeamPlayerSoloLeagueAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'Request should be AJAXienne and POST!');
		}
		
		$region = $request->get('region');
		$teamTagOrName = $request->get('team_tag_or_name');
		$player = $this->get('mvnerds.team_seeker_manager')->updatePlayerSoloQLeagueIfNeeded($region, $teamTagOrName);
		
		if (false == $player) {
			return new Response(json_encode(false), 500);
		}
		
		return $this->render('MVNerdsTeamSeekerBundle:TeamSeeker:team_seeker_index_player_row.html.twig', array(
			'player'			=> $player,
			'region'			=> $region,
			'team_tag_or_name'	=> $teamTagOrName,
			'disable_ajax_load' => false
		));
	}
}
