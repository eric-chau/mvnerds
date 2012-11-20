<?php

namespace MVNerds\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

use MVNerds\ItemHandlerBundle\Form\Model\ChangeLoLDirectoryModel;
use MVNerds\ItemHandlerBundle\Form\Type\ChangeLoLDirectoryType;

class FrontController extends Controller
{
    /**
	 * Affiche la page de profil de l'invocateur authentifié et connecté
	 * 
	 * @Route("/{_locale}/profile", name="summoner_profile_index")
	 * @Secure(roles="ROLE_USER")
	 */
	public function loggedSummonerIndexAction()
	{		
		$user = $this->getUser();
		
		return $this->render('MVNerdsProfileBundle:Profile:profile_index.html.twig', array(
			'user'				=> $user,
			'user_items_builds' => $this->get('mvnerds.item_build_manager')->findByUserId($user->getId()),
			'form'				=> $this->createForm(new ChangeLoLDirectoryType(), new ChangeLoLDirectoryModel($this->get('mvnerds.preference_manager'), $user))->createView()
		));
	}
	
	/**
	 * @Route("/profile", name="summoner_profile_proxy")
	 */	
	public function summonerProfileProxyAction()
	{
		$this->redirect($this->generateUrl('summoner_profile_index', array(
			'_locale' => $this->getRequest()->getLocale()
		)));
	}
	
	/**
	 * @Route("/{_locale}/profile/{userSlug}", name="summoner_profile_view")
	 */
	
	public function viewProfileAction($userSlug)
	{
		$user = $this->get('mvnerds.user_manager')->findBySlug($userSlug);
		
		if ($this->getUser()->getId() == $user->getId()) {
			return $this->forward('MVNerdsProfileBundle:Front:loggedSummonerIndex');
		}
		
		return $this->render('MVNerdsProfileBundle:Profile:profile_index.html.twig', array(
			'user'				=> $user,
			'user_items_builds' => $this->get('mvnerds.item_build_manager')->findByUserId($user->getId())
		));
	}
	
	/**
	 * @Route("/save-summoner-preference", name="summoner_profile_save_preference", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function saveSummonerPreferenceAction()
	{
		$request = $this->getRequest();
		if (!$request->isMethod('POST') || !$request->isXmlHttpRequest()) {
			throw new HttpException(500, 'XMLHttpRequest and POST method expected!');
		}
		
		$preferenceUniqueName = $request->get('preference_unique_name', null);
		$preferenceValue = $request->get('preference_value', null);
		
		if (null == $preferenceUniqueName) {
			throw new HttpException(500, 'preference_unique_name and/or preference_value is/are missing!');
		}
		
		$response = true;
		try {
			$this->get('mvnerds.preference_manager')->saveUserPreference($this->getUser(), $preferenceUniqueName, $preferenceValue);
		}
		catch(InvalidArgumentException $e) {
			$response = false;
		}
		
		return new Response(json_encode($response));
	}
}
