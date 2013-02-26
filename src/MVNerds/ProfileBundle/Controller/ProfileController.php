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
use MVNerds\CoreBundle\Model\User;

class ProfileController extends Controller
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
			'user'					=> $user,
			'user_items_builds'		=> $this->get('mvnerds.item_build_manager')->findByUserId($user->getId()),
			'form'					=> $this->createForm(new ChangeLoLDirectoryType(), new ChangeLoLDirectoryModel($this->get('mvnerds.preference_manager'), $user))->createView(),
			'avatars'				=> $this->get('mvnerds.profile_manager')->findAvatarByUserRoles($user),
			'user_comment_count'	=> $this->get('mvnerds.comment_manager')->countCommentForUser($user)
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
		
		if (null != $this->getUser() && $this->getUser()->getId() == $user->getId()) {
			return $this->forward('MVNerdsProfileBundle:Profile:loggedSummonerIndex');
		}
		
		return $this->render('MVNerdsProfileBundle:Profile:profile_index.html.twig', array(
			'user'					=> $user,
			'user_items_builds'		=> $this->get('mvnerds.item_build_manager')->findPublicByUserId($user->getId()),
			'user_comment_count'	=> $this->get('mvnerds.comment_manager')->countCommentForUser($user)
		));
	}
	
	/**
	 * @Route("/profile/save-summoner-preference", name="summoner_profile_save_preference", options={"expose"=true})
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
	
	/**
	 * @Route("/profile/save-new-avatar", name="summoner_profile_save_avatar", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function saveSummonerAvatarAction()
	{
		$request = $this->getRequest();
		if (!$request->isMethod('POST') || !$request->isXmlHttpRequest()) {
			throw new HttpException(500, 'XMLHttpRequest and POST method expected!');
		}
		
		$newAvatarName = $request->get('new_avatar_name', null);
		if (null == $newAvatarName) {
			throw new HttpException(500, 'preference_unique_name and/or preference_value is/are missing!');
		}
		
		return new Response(json_encode($this->get('mvnerds.profile_manager')->saveAvatarIfValid($this->getUser(), $newAvatarName)));
	}
	
	/**
	 * @Route("/profile/change-password", name="summoner_profile_change_password", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function changeSummonerPasswordAction()
	{
		$this->get('mvnerds.user_manager')->initForgotPasswordProcess($this->getUser());
		
		return new Response(json_encode(true));
	}
	
	/**
	 * 
	 */
	public function renderLastestCommentsBlockAction(User $user)
	{
		return $this->render('MVNerdsProfileBundle:Profile:lastest_comments_block.html.twig', array(
			'comments'	=> $this->get('mvnerds.comment_manager')->getLastestCommentsByUser($user),
			'user'		=> $user
		));
	}
}
