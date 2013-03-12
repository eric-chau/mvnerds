<?php

namespace MVNerds\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use MVNerds\CoreBundle\Exception\ServiceUnavailableException;
use MVNerds\CoreBundle\Exception\InvalidSummonerNameException;
use MVNerds\ItemHandlerBundle\Form\Model\ChangeLoLDirectoryModel;
use MVNerds\ItemHandlerBundle\Form\Type\ChangeLoLDirectoryType;
use MVNerds\CoreBundle\Model\User;

class ProfileController extends Controller
{
	/**
	 * Permet d'accéder à la page de listing de tous les membres
	 * @Route("/{_locale}/profile-list", name="profile_list")
	 */
	public function listAction() 
	{
		return $this->render('MVNerdsProfileBundle:Profile:profile_list.html.twig');
	}
	
	/**
	 * Permet de lister tous les utilisateurs en AJAX
	 * @Route("/{_locale}/list-ajax", name="users_list_ajax", options={"expose"=true})
	 */
	public function listAjaxAction() 
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest()) {
			throw new HttpException(500, 'Request must be AJAX');
		}
		
		$aColumns = array(
			'',
			'',
			'',
			'username',
			'created_at'
		);
		
		$limitStart = 0;
		$limitLength = -1;
		//Pagination
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
			$limitStart = $_GET['iDisplayStart'];
			$limitLength = $_GET['iDisplayLength'];
		}
		//Tri
		$orderArr = array();
		if ( isset( $_GET['iSortCol_0'] ) ) {
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ) {
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ) {
					$orderArr[$aColumns[intval($_GET['iSortCol_'.$i])]] = ($_GET['sSortDir_'.$i]);
				}
			}
		}
		if (count($orderArr) <= 0) {
			$orderArr = array('created_at' => 'desc');
		}
		//Recherche par colonne
		$whereArr = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
				if ($aColumns[$i] == 'username') {
					$whereArr[$aColumns[$i]] = ($_GET['sSearch_'.$i]);
				}
			}
		}
		
		$userManager = $this->get('mvnerds.user_manager');
		
		$users = $userManager->findAllActiveAjax($limitStart, $limitLength, $orderArr, $whereArr);
		
		$jsonUsers = array(
			"tab" => $users->count(),
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $userManager->countAllActive(),
			"iTotalDisplayRecords" => $userManager->countAllActiveAjax($whereArr),
			'aaData' => array()
		);
		
		foreach($users as $user) {
			
			$jsonUsers['aaData'][] = array(
				$this->renderView('MVNerdsProfileBundle:Profile:profile_list_avatar_cell.html.twig', array('user' => $user)),
				$this->renderView('MVNerdsProfileBundle:Profile:profile_list_username_cell.html.twig', array('user' => $user)),
				$this->renderView('MVNerdsProfileBundle:Profile:profile_list_game_account_cell.html.twig', array('user' => $user)),
				$user->getUsername(),
				$user->getCreatedAt('YmdHims')
			);
		}
		return new Response(json_encode($jsonUsers));
	}
	
    /**
	 * Affiche la page de profil de l'invocateur authentifié et connecté
	 * 
	 * @Route("/{_locale}/profile", name="summoner_profile_index")
	 * @Secure(roles="ROLE_USER")
	 */
	public function loggedSummonerIndexAction()
	{		
		$user = $this->getUser();
		$gameAccount = $this->get('mvnerds.profile_manager')->getGameAccountByUser($user);
		
		if (null !=$gameAccount) {
			$this->get('mvnerds.elophant_api_manager')->updateRankedStatsIfNeeded($gameAccount);
		}
		
		$videoManager = $this->get('mvnerds.video_manager');
		
		return $this->render('MVNerdsProfileBundle:Profile:profile_index.html.twig', array(
			'user'					=> $user,
			'user_items_builds'		=> $this->get('mvnerds.item_build_manager')->findByUserId($user->getId()),
			'videos'				=> $videoManager->findByUser($user),
			'video_categories'			=> $videoManager->findAllVideoCatgories(),
			'form'					=> $this->createForm(new ChangeLoLDirectoryType(), new ChangeLoLDirectoryModel($this->get('mvnerds.preference_manager'), $user))->createView(),
			'avatars'				=> $this->get('mvnerds.profile_manager')->findAvatarByUserRoles($user),
			'user_comment_count'	=> $this->get('mvnerds.comment_manager')->countCommentForUser($user),
			'game_account'			=> $gameAccount
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
	
	/**
	 * @Route("{_locale}/profile/lol-account-existence", name="profile_check_lol_account_existence", options={"expose"=true})
	 */
	public function checkLoLAccountProvidedAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'La requête doit être AJAXienne et en POST !');
		}
		
		$region = $request->get('region', null);
		$summonerName = $request->get('summoner_name', null);
		if ($region == null || $summonerName == null) {
			throw new HttpException(500, 'Paramètre(s) manquant(s) !');
		}
		
		$elophantManager = $this->get('mvnerds.elophant_api_manager');
		$translator = $this->get('translator');
		$gameAccount = null;
		try {
			$gameAccount = $elophantManager->getGameAccountFromRegionAndUsername($region, $summonerName);
		}
		catch (ServiceUnavailableException $e) {
			return new Response($translator->trans('Profile.error.elophant_afk'), 503);
		}
		catch (InvalidSummonerNameException $e) {
			return new Response($translator->trans('Profile.error.invalid_summoner_name.' . $summonerName), 400);
		}
		
		if ($this->get('mvnerds.profile_manager')->isSummonerNameAlreadyLinked($summonerName)) {
			return new Response($translator->trans('Profile.error.summoner_name_already_linked.' . $summonerName), 400);
		}
				
		$profile = $this->getUser()->getProfile();
		$profile->setGameAccount($gameAccount);
		$profile->save();
		
		return $this->render('MVNerdsProfileBundle:Modal:link_lol_accout_modal_step_2.html.twig', array(
			'summoner_name'		=> $summonerName,
			'activation_code'	=> $gameAccount->getActivationCode()
		));
	}
	
	/**
	 * @Route("{_locale}/profile/link-account-process/last-step", name="profile_end_of_link_account_process", options={"expose"=true})
	 */	
	public function endOfLinkAccountProcessAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest()) {
			throw new HttpException(500, 'La requête doit être AJAXienne !');
		}
		
		$gameAccount = $this->get('mvnerds.profile_manager')->getGameAccountByUser($this->getUser());
		
		if (null == $gameAccount || $gameAccount->isActive()) {
			throw new AccessDeniedException('Pas de compte associé ou alors déjà actif !');
		}
		
		// On bloque l'utilisateur a une vérification toutes les minutes
		$session = $this->get('session');
		$lastCheckCodeActivationTime = $session->get('profile_last_check_code_activation_time', null);
		if (null != $lastCheckCodeActivationTime && $lastCheckCodeActivationTime + 60 >= time()) {
			return new Response($this->get('translator')->trans('Profile.error.wait_more_please'), 400);
		}
		
		$session->set('profile_last_check_code_activation_time', time());
		$success = false;
		try {
			$success = $this->get('mvnerds.elophant_api_manager')->checkActivationCodeWithMasteriesPage($gameAccount);
		}
		catch (ServiceUnavailableException $e) {
			return new Response($this->get('translator')->trans('Profile.error.elophant_afk'), 503);
		}
		
		if (!$success) {
			return new Response($this->get('translator')->trans('Profile.error.activation_code_not_found'), 400);
		}
		
		$session->remove('profile_last_check_code_activation_time');
		$this->get('mvnerds.profile_manager')->removeInactiveGameAccountBySummonerName($gameAccount->getSummonerName());
		
		return new Response(json_encode($success), 200);
	}
	
	/**
	 * @Route("/{_locale}/profile/cancel-link-account-process", name="profile_cancel_link_account_process", options={"expose"=true})
	 */
	public function cancelLinkAccountProcessAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest()) {
			throw new HttpException(500, 'La requête doit être AJAXienne !');
		}
		
		$this->get('mvnerds.profile_manager')->removeGameAccountFromProfile($this->getUser()->getProfile());
		
		return new Response(json_encode(true), 200);
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
		
		$gameAccount = $this->get('mvnerds.profile_manager')->getGameAccountByUser($user);
		if (null !=$gameAccount) {
			$this->get('mvnerds.elophant_api_manager')->updateRankedStatsIfNeeded($gameAccount);
		}
		
		$videoManager = $this->get('mvnerds.video_manager');
		
		return $this->render('MVNerdsProfileBundle:Profile:profile_index.html.twig', array(
			'user'					=> $user,
			'user_items_builds'		=> $this->get('mvnerds.item_build_manager')->findPublicByUserId($user->getId()),
			'videos'				=> $videoManager->findByUser($user),
			'video_categories'			=> $videoManager->findAllVideoCatgories(),
			'user_comment_count'		=> $this->get('mvnerds.comment_manager')->countCommentForUser($user),
			'game_account'			=> $gameAccount
		));
	}
}

