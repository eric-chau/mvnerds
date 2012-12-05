<?php

namespace MVNerds\ItemHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\SecurityExtraBundle\Annotation\Secure;

use MVNerds\CoreBundle\Model\ChampionItemBuild;
use MVNerds\CoreBundle\Model\UserPreference;
use  MVNerds\CoreBundle\Model\ItemBuildItems;

/**
 * @Route("/pimp-my-recommended-items")
 */
class ItemBuilderController extends Controller
{

	const MAX_ITEM_BUILDS = 7;
	
	/**
	 * @Route("/create", name="item_builder_create")
	 */
	public function createAction()
	{
		$canSaveBuild = false;
		$lolDir = null;
		
		if ($this->get('security.context')->isGranted('ROLE_USER')) 
		{
			$user = $this->get('security.context')->getToken()->getUser();
			if($this->get('security.context')->isGranted('ROLE_ADMIN'))
			{
				$canSaveBuild = true;
			}
			else
			{
				$nbItemBuilds = $this->get('mvnerds.item_build_manager')->countNbBuildsByUserId($user->getId());
				if ($nbItemBuilds < self::MAX_ITEM_BUILDS)
				{
					$canSaveBuild = true;
				}
			}
			try{
				$lolDirPreference = $this->get('mvnerds.preference_manager')->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
				$lolDir = $lolDirPreference->getValue();
			} catch(\Exception $e) {
				$lolDir= null;
			}
		}	
		
		return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:create_index.html.twig', array(
			'champions'		=> $this->get('mvnerds.champion_manager')->findAllWithTags(),
			'items'		=> $this->get('mvnerds.item_manager')->findAllActive(),
			'can_save_build'	=> $canSaveBuild,
			'lol_dir'		=> $lolDir
		));
	}
	
	/**
	 * 
	 * @Route("/list/{championSlug}", name="item_builder_list", defaults={"championSlug"=null}, options={"expose"=true})
	 */
	public function listAction($championSlug) 
	{
		try {
			$champion = $this->get('mvnerds.champion_manager')->findBySlug($championSlug);
			return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:list_index.html.twig', array(
				'itemBuilds'	=> $this->get('mvnerds.item_build_manager')->findAllPublic(),
				'championSlug'	=> $championSlug
			));
		} catch(Exception $e) {
			return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:list_index.html.twig', array(
				'itemBuilds'	=> $this->get('mvnerds.item_build_manager')->findAllPublic()
			));
		}
	}
	
	/**
	 * 
	 * @Route("/view/{itemBuildSlug}", name="item_builder_view")
	 */
	public function viewAction($itemBuildSlug) 
	{
		try{
			$itemBuild = $this->get('mvnerds.item_build_manager')->findOneBySlug($itemBuildSlug);
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('item_builder_list'));
		}
		$itemBuildItemsCollection = $itemBuild->getItemBuildItemss();
		$itemBlocks = array();
		foreach ($itemBuildItemsCollection as $itemBuildItems)
		{
			$item = $itemBuildItems->getItem();
			$type = $itemBuildItems->getType();
			$position = $itemBuildItems->getPosition();
			$ecapedName = preg_replace('/ +/', '_', $type);
			if (! isset($itemBlocks[$position]))
			{
				$itemBlocks[$position] = array('type' => $type, 'escaped' => $ecapedName, 'items' => array());
			}

			$itemBlocks[$position]['items'][] = $item;
		}
		
		return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:view_index.html.twig', array(
				'itemBuild'	=> $itemBuild,
				'itemBlocks'	=> $itemBlocks
		));
	}
	
	/**
	 * Re-génération d'un build déjà éxistant avec les nouveaux chemins $path
	 * 
	 * @Route("/generate-rec-items-from-slug", name="item_builder_generate_rec_item_file_from_slug", options={"expose"=true})
	 */
	public function generateRecItemsFileFromSlugAction()
	{
		/* @var $itemBuildManager \MVNerds\CoreBundle\ItemBuild\ItemBuildManager */
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
		
		$request = $this->getRequest();	
		if (!$request->isXmlHttpRequest() && !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be XmlHttp and POST method!');
		}
		
		$itemBuildSlug = $request->get('itemBuildSlug');
		$path = $request->get('path');
		
		try {
			$itemBuild = $itemBuildManager->findOneBySlug($itemBuildSlug);
		} catch (\Exception $e ) {
			throw new HttpException(500, 'Invalid slug given : '.$itemBuildSlug);
		}
		
		/* @var $batchManager \MVNerds\CoreBundle\Batch\BatchManager */
		$batchManager = $this->get('mvnerds.batch_manager');
		if($this->get('security.context')->isGranted('ROLE_USER'))
		{
			$user = $this->get('security.context')->getToken()->getUser();
			$preferenceManager = $this->get('mvnerds.preference_manager');
			if (null === $path || '' == $path ) 
			{
				try{
					$lolDirectoryPreference = $preferenceManager->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
					$path = $lolDirectoryPreference->getValue();
				}catch(\Exception $e) {
					$path = null;
				}
			} 
			else
			{
				try{
					$userPreference = $this->get('mvnerds.preference_manager')->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
				} catch (\Exception $e) {
					$userPreference = new UserPreference();
				}
				$userPreference->setValue($path);
				$userPreference->setUserId($user->getId());
				try{
					$preference = $preferenceManager->findByUniqueName('LEAGUE_OF_LEGENDS_DIRECTORY');
					$userPreference->setPreference($preference);
					$userPreference->save();
				} catch (\Exception $e) {}
			}
		}
		
		$itemBuild->setSlug($itemBuildSlug . '__' . $this->get('session')->getId() . '__');
		
		$batchManager->createRecItemBuilder($itemBuild, $path);
		
		return new Response(json_encode($itemBuildSlug));
	}
	/**
	 * @Route("/generate-rec-items", name="item_builder_generate_rec_item_file", options={"expose"=true})
	 */
	public function generateRecItemsFileAction()
	{
		/* @var $itemBuildManager \MVNerds\CoreBundle\ItemBuild\ItemBuildManager */
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
		
		$request = $this->getRequest();	
		if (!$request->isXmlHttpRequest() && !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be XmlHttp and POST method!');
		}
		
		$championsSlugs = $request->get('championsSlugs');
		$itemsSlugs = $request->get('itemsSlugs');
		$gameMode = $request->get('gameMode');
		$buildName = $request->get('buildName');//TODO a echaper
		$saveBuild = $request->get('saveBuild');
		$path = $request->get('path');
		$itemBuildSlug = $request->get('itemBuildSlug');
		
		if ($itemBuildSlug != null) {
			try {
				$itemBuild = $itemBuildManager->findOneBySlug($itemBuildSlug);
			} catch(\Exception $e) {
				throw new HttpException(500, 'Unable to find item build with slug '.$itemBuildSlug.'!');
			}
		} else {
			$itemBuild = new \MVNerds\CoreBundle\Model\ItemBuild();
		}
		
		/* @var $itemManager \MVNerds\CoreBundle\Item\ItemManager */
		$itemManager = $this->get('mvnerds.item_manager');
		
		$gameModes = array(
			'dominion'		=> 2,
			'classic'		=> 1,
			'aram'			=> 3,
			'twisted-treeline'	=> 5
		);
		
		if (!key_exists($gameMode, $gameModes)) {
			$gameMode = 'classic';
		}
		
		$i = 1;
		
		$itemBuilditemsCollection = new \PropelCollection();
		foreach ($itemsSlugs as $itemBlock)
		{
			$itemBlockName = $itemBlock['name'];
			$items = $itemBlock['items'];
			
			$itemBlockName = preg_replace('/[^a-zA-Z0-9 ]+/','',$itemBlockName);
			
			foreach ($items as $itemSlug)
			{
				try {
					$item = $itemManager->findBySlug($itemSlug);
				} catch (\Exception $e) {
					throw new HttpException(500, 'Invalid recommended items : item not found! slug given : '.$itemSlug);
				}
				if (strpos($item->getGameModesToString(), 'shared') === false && strpos($item->getGameModesToString(), $gameMode) === false) {
					throw new HttpException(500, 'Invalid recommended items : game mode!');
				}
				$itemBuildItems = new ItemBuildItems();
				$itemBuildItems->setItemId($item->getId());
				$itemBuildItems->setType($itemBlockName);
				$itemBuildItems->setPosition($i);
				$itemBuildItems->setCount(1);

				$itemBuilditemsCollection->append($itemBuildItems);
			}
			$i ++;
		}
		$itemBuild->setItemBuildItemss($itemBuilditemsCollection);
		
		$itemBuild->setName($buildName);
		
		$newItemBuildSlug = preg_replace('/[^\w\/]+/u', '-', $buildName);
		$itemBuild->setSlug($newItemBuildSlug);
		$championItemBuilds = new \PropelCollection();
		
		/* @var $championManager \MVNerds\CoreBundle\Champion\ChampionManager */
		$championManager = $this->get('mvnerds.champion_manager');
		
		foreach ($championsSlugs as $championSlug)
		{
			try {
				$champion = $championManager->findBySlug($championSlug);
			} catch (\Exception $e) {
				continue;
			}
			$championItemBuild = new ChampionItemBuild();
			$championItemBuild->setChampion($champion);
			$championItemBuild->setGameModeId($gameModes[$gameMode]);
			$championItemBuild->setIsDefaultBuild(false);
			$championItemBuilds->append($championItemBuild);
		}
		//Si on a au moins un champion dans la liste
		if($championItemBuilds->count() > 0) {
			$itemBuild->setChampionItemBuilds($championItemBuilds);
			
			foreach ($itemBuilditemsCollection as $itemBuildItems)
			{
				/* @var $itemBuildItems ItemBuildItems */
				$item = $itemBuildItems->getItem();
				
				//Si l item est associé a un champion
				if ($item->getChampionId() != null) {
					//S il y a plus d un champion dans la liste c est que c est une erreur
					if($championItemBuilds->count() > 1) {
						throw new HttpException(500, 'Invalid champion-specific item given!');
					} elseif ($item->getChampionId() != $championItemBuilds->getFirst()->getChampionId()) {
						//Sinon on verifie que l id du champion de la liste differe de l'id associé a l item
						throw new HttpException(500, 'Invalid champion-specific item given!');
					}
				}
			}
			
			
			
			if (null != $saveBuild && $saveBuild == 'true' && $this->get('security.context')->isGranted('ROLE_USER')) {
				$user = $this->get('security.context')->getToken()->getUser();
				
				$nbItemBuilds = $itemBuildManager->countNbBuildsByUserId($user->getId());
				
				if($this->get('security.context')->isGranted('ROLE_ADMIN'))
				{
					$itemBuild->setUser($user);
					$itemBuild->save();
				} 
				elseif ($nbItemBuilds < self::MAX_ITEM_BUILDS) 
				{
					$itemBuild->setUser($user);
					$itemBuild->save();
				}
			}
		} else {
			throw new HttpException(500, 'No valid champion given!');
		}
		
		/* @var $batchManager \MVNerds\CoreBundle\Batch\BatchManager */
		$batchManager = $this->get('mvnerds.batch_manager');
		if($this->get('security.context')->isGranted('ROLE_USER'))
		{
			$user = $this->get('security.context')->getToken()->getUser();
			$preferenceManager = $this->get('mvnerds.preference_manager');
			if (null === $path || '' == $path ) 
			{
				try{
					$lolDirectoryPreference = $preferenceManager->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
					$path = $lolDirectoryPreference->getValue();
				}catch(\Exception $e) {
					$path = null;
				}
			} 
			else
			{
				try{
					$userPreference = $this->get('mvnerds.preference_manager')->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
				} catch (\Exception $e) {
					$userPreference = new UserPreference();
				}
				$userPreference->setValue($path);
				$userPreference->setUserId($user->getId());
				try{
					$preference = $preferenceManager->findByUniqueName('LEAGUE_OF_LEGENDS_DIRECTORY');
					$userPreference->setPreference($preference);
					$userPreference->save();
				} catch (\Exception $e) {}
			}
		}
		
		 $itemBuild->setSlug($newItemBuildSlug . '__' . $this->get('session')->getId() . '__');
		
		$batchManager->createRecItemBuilder($itemBuild, $path);

		return new Response(json_encode($newItemBuildSlug));
	}
	
	/**
	 * @Route("/{itemBuildSlug}/download-rec-items", name="item_builder_download_file", options={"expose"=true})
	 */
	public function executeDownloadItemBuildAction($itemBuildSlug)
	{
		try{
			/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
			$itemBuild = $this->get('mvnerds.item_build_manager')->findOneBySlug($itemBuildSlug);
			$itemBuild->setDownload($itemBuild->getDownload()+1);
			$itemBuild->save();
		} catch (\Exception $e) {
			//Si le build n est pas trouvé en base de données on ne fait rien
		}
		
		$path = $this->container->getParameter('item_builds_path') . $itemBuildSlug . '__' . $this->get('session')->getId() .'__.bat';
		
		$response = new Response();
		
		if (file_exists($path))
		{
			$response->headers->set('Content-Type', 'application/octetstream');
			$response->headers->set('Content-Disposition', 'attachment;filename='.$itemBuildSlug.'.bat');
			$response->headers->set('Content-Transfer-Encoding', 'binary');
			$response->headers->set('Content-Length', filesize($path));

			@readfile($path);
			
			try{
				$statistic = $this->get('mvnerds.statistics_manager')->findByUniqueName('ITEM_BUILDS_TOTAL_DOWNLOADED');
				$statistic->setValue($statistic->getValue() + 1);
				$statistic->save();
			} catch(\Exception $e) {
				
			}
		}
		return $response;
	}
	
	/**
	 * @Route("/get-items-name", name="item_builder_get_items_name", options={"expose"=true})
	 */
	public function getItemsNameAction()
	{
		$request = $this->getRequest();
		if  (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'La requête doit être effectuée en AJAX et en method POST !');
		}
		return new Response(json_encode($this->get('mvnerds.item_manager')->getPublicItemsName()->toArray()));
	}
	
	/**
	 * @Route("/get-item-popover-content", name="item_builder_get_item_popover_content", options={"expose"=true})
	 */
	public function getItemPopoverContentAction()
	{
		$request = $this->getRequest();
		if  (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'La requête doit être effectuée en AJAX et en method POST !');
		}
		
		$slug = $request->get('slug');
		try{
			$item = $this->get('mvnerds.item_manager')->findBySlugForPopover($slug);
		} catch (\Exception $e) {
			return new Response(json_encode('Impossible de trouver l\'item'));
		}
		
		return $this->render('MVNerdsItemHandlerBundle:Popover:item_popover_content.html.twig', array(
			'item' => $item
		));
	}
	
	/**
	 * @Route("/edit-build/{itemBuildSlug}", name="item_builder_edit_build")
	 * @Secure(roles="ROLE_USER")
	 */
	public function editBuildAction($itemBuildSlug) 
	{		
		/* @var $itemBuildManager \MVNerds\CoreBundle\ItemBuild\ItemBuildManager */
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
			
		try {
			/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
			$itemBuild = $itemBuildManager->findOneBySlug($itemBuildSlug);
		} catch (\Exception $e ) {
			return $this->redirect($this->generateUrl('item_builder_list'));
		}
		
		if( ! ($this->get('security.context')->getToken()->getUser()->getId() == $itemBuild->getUserId() || $this->get('security.context')->isGranted('ROLE_ADMIN')))
		{
			throw new AccessDeniedException();
		}
		
		$selectedChampions = array();
		foreach ($itemBuild->getChampionItemBuildsJoinChampion() as $championItemBuild) 
		{
			$selectedChampions[] = $championItemBuild->getChampion()->getSlug();
		}
		
		$itemBuildItemsCollection = $itemBuild->getItemBuildItemss();
		$selectedItems = array();
		foreach ($itemBuildItemsCollection as $itemBuildItems)
		{
			$item = $itemBuildItems->getItem();
			$type = $itemBuildItems->getType();
			$position = $itemBuildItems->getPosition();
			$count = $itemBuildItems->getCount();
			$ecapedName = preg_replace('/ +/', '_', $type);
			if (! isset($selectedItems[$position]))
			{
				$selectedItems[$position] = array('type' => $type, 'escaped' => $ecapedName, 'items' => array());
			}

			$selectedItems[$position]['items'][] = $item;
		}
		
		return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:create_index.html.twig', array(
			'champions'			=> $this->get('mvnerds.champion_manager')->findAllWithTags(),
			'items'			=> $this->get('mvnerds.item_manager')->findAllActive(),
			'selectedChampions'	=> $selectedChampions,
			'selectedItems'		=> $selectedItems,
			'buildName'			=> $itemBuild->getName(),
			'gameMode'			=> $itemBuild->getChampionItemBuilds()->getFirst()->getGameMode()->getLabel(),
			'itemBuildSlug'		=> $itemBuildSlug
		));
	}
	
	/**
	 * @Route("/delete-build/{itemBuildSlug}", name="item_builder_delete_build")
	 * @Secure(roles="ROLE_USER")
	 */
	public function deleteBuildAction($itemBuildSlug) 
	{
		/* @var $itemBuildManager \MVNerds\CoreBundle\ItemBuild\ItemBuildManager */
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
			
		try {
			/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
			$itemBuild = $itemBuildManager->findOneBySlug($itemBuildSlug);
		} catch (\Exception $e ) {
			return $this->redirect($this->generateUrl('summoner_profile_index'));
		}
		
		if($this->get('security.context')->getToken()->getUser()->getId() == $itemBuild->getUserId() || $this->get('security.context')->isGranted('ROLE_ADMIN'))
		{
			$itemBuild->delete();
		}
		
		return $this->redirect($this->generateUrl('summoner_profile_index'));
	}
}
