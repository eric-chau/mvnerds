<?php

namespace MVNerds\ItemHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Exception;

use MVNerds\CoreBundle\Model\ChampionItemBuild;
use MVNerds\CoreBundle\Model\UserPreference;
use MVNerds\CoreBundle\Model\ItemBuildBlock;
use MVNerds\CoreBundle\Model\ItemBuildBlockItem;

/**
 * @Route("/pimp-my-recommended-items")
 */
class ItemBuilderController extends Controller
{

	const MAX_ITEM_BUILDS = 7;
	
	/**
	 * @Route("/create", name="pmri_create", options={"expose"=true})
	 */
	public function createAction()
	{		
		$canSaveBuild = false;
		$lolDir = null;
		$user = $this->getUser();
		if ($this->get('security.context')->isGranted('ROLE_USER')) {
			if($this->get('security.context')->isGranted('ROLE_ADMIN')) {
				$canSaveBuild = true;
			}
			else {
				$nbItemBuilds = $this->get('mvnerds.item_build_manager')->countNbBuildsByUserId($user->getId());
				if ($nbItemBuilds < self::MAX_ITEM_BUILDS) {
					$canSaveBuild = true;
				}
			}
			try {
				$lolDirPreference = $this->get('mvnerds.preference_manager')->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
				$lolDir = $lolDirPreference->getValue();
			} 
			catch(\Exception $e) {}
		}	
		
		/* @var $tagManager \MVNerds\CoreBundle\Tag\TagManager */
		$tagManager = $this->get('mvnerds.tag_manager');
		$tags = array();
		$tags['attack'] = $tagManager->findByParentName('BASE_ITEM_ATTACK');
		$tags['magic'] = $tagManager->findByParentName('BASE_ITEM_MAGIC');
		$tags['defense'] = $tagManager->findByParentName('BASE_ITEM_DEFENSE');
		$tags['other'] = $tagManager->findByParentName('BASE_ITEM_OTHER');
		
		return $this->render('MVNerdsItemHandlerBundle:PMRI:pmri_create.html.twig', array(
			'champions'		=> $this->get('mvnerds.champion_manager')->findAllWithTags(),
			'items'		=> $this->get('mvnerds.item_manager')->findAllActive(),
			'can_save_build'	=> $canSaveBuild,
			'lol_dir'		=> $lolDir,
			'tags'			=> $tags
		));
	}
	
	/**
	 * 
	 * @Route("/old-list", name="item_builder_list", options={"expose"=true})
	 */
	public function oldListAction() 
	{
		return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:list_index.html.twig');
	}
	
	/**
	 * 
	 * @Route("/list", name="pmri_list")
	 */
	public function listAction() 
	{
		return $this->render('MVNerdsItemHandlerBundle:PMRI:pmri_list_index.html.twig');
	}
	
	/**
	 * 
	 * @Route("/list-ajax", name="item_builder_list_ajax", options={"expose"=true})
	 */
	public function listAjaxAction() 
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest())
		{
			throw new HttpException(500, 'Request must be AJAX');
		}
		
		$aColumns = array(
			'',
			'',
			'',
			'Champions',
			'Download',
			'UpdateTime',
			'user.USERNAME',
			'CreateTime',
			'CommentCount',
			'Name',
			'View'
		);
		
		$limitStart = 0;
		$limitLength = -1;
		//Pagination
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$limitStart = $_GET['iDisplayStart'];
			$limitLength = $_GET['iDisplayLength'];
		}
		//Tri
		$orderArr = array();
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$orderArr[$aColumns[intval($_GET['iSortCol_'.$i])]] = ($_GET['sSortDir_'.$i]);
				}
			}
		}
		if (count($orderArr) <= 0) {
			$orderArr = array('CreateTime' => 'desc');
		}
		
		//Recherche par colonne
		$whereArr = array();
		$championName = null;
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
			{
				if ($aColumns[$i] == 'user.USERNAME' || $aColumns[$i] == 'Name')
				{
					$whereArr[$aColumns[$i]] = ($_GET['sSearch_'.$i]);
				} 
				else if ($aColumns[$i] == 'Champions')
				{
					$championName = ($_GET['sSearch_'.$i]);
				}
			}
		}
		
		$translator = $this->get('translator');
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
		
		$itemBuilds = $itemBuildManager->findAllPublicAjax($limitStart, $limitLength, $orderArr, $whereArr, $championName);
		
		$jsonItemBuilds = array(
			"tab" => $itemBuilds->count(),
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $itemBuildManager->countAllPublic(),
			"iTotalDisplayRecords" => $itemBuildManager->countAllPublicAjax($whereArr, $championName),
			'aaData' => array()
		);
		
		foreach($itemBuilds as $itemBuild)
		{
			$jsonItemBuilds['aaData'][] = array(
				$this->renderView('MVNerdsItemHandlerBundle:PMRI:pmri_list_table_row_champion.html.twig', array('item_build' => $itemBuild)),
				$this->renderView('MVNerdsItemHandlerBundle:PMRI:pmri_list_table_row_name.html.twig', array('item_build' => $itemBuild, 'user' => $itemBuild->getUser())),
				$translator->trans($itemBuild->getChampionItemBuilds()->getFirst()->getGameMode()->getLabel()),
				//$this->renderView('MVNerdsItemHandlerBundle:ItemBuilder:list_column_actions.html.twig', array('itemBuild' => $itemBuild)),
				$itemBuild->getChampionsNamesToString(),
				$itemBuild->getDownload(),
				$itemBuild->getUpdateTime('YmdHims'),
				$itemBuild->getUser()->getUsername(),
				$itemBuild->getCreateTime('YmdHims'),
				$itemBuild->getCommentCount(),
				$itemBuild->getName(),
				$itemBuild->getView()
			);
		}
		return new Response(json_encode($jsonItemBuilds));
	}
	
	/**
	 * 
	 * @Route("/view/{itemBuildSlug}/{dl}", name="pmri_list_detail", defaults={"dl"=null}, options={"expose"=true})
	 */
	public function viewAction($itemBuildSlug, $dl)
	{
		try {
			/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
			$itemBuild = $this->get('mvnerds.item_build_manager')->findBySlug($itemBuildSlug);
			$itemBuild->setView($itemBuild->getView() + 1);
			$itemBuild->keepUpdateDateUnchanged();
			$itemBuild->save();
		} 
		catch (\Exception $e) {
			return $this->redirect($this->generateUrl('item_builder_list'));
		}
		
		$lolDir = null;
		$canEdit = false;
		
		if ($this->get('security.context')->isGranted('ROLE_USER')) {
			$user = $this->get('security.context')->getToken()->getUser();
			if (($itemBuild->getUser()->getId() == $user->getId()) || $this->get('security.context')->isGranted('ROLE_ADMIN')) {
				$canEdit = true;
			}
			try {
				$lolDirPreference = $this->get('mvnerds.preference_manager')->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
				$lolDir = $lolDirPreference->getValue();
			} 
			catch(\Exception $e) {}
		}
		
		$params = array(
			'item_build'	=> $itemBuild,
			'lol_dir'	=> $lolDir,
			'can_edit'	=> $canEdit
		);
		if ($dl != null && $dl == 'dl') {
			$params['start_dl'] = 'true';
		}
		
		return $this->render('MVNerdsItemHandlerBundle:PMRI:pmri_detail.html.twig', $params);
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
			$itemBuild = $itemBuildManager->findBySlug($itemBuildSlug);
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
		$buildName = $request->get('buildName');
		$buildDescription = $request->get('description');
		$isBuildPrivate = $request->get('isBuildPrivate');
		$saveBuild = $request->get('saveBuild');
		$path = $request->get('path');
		$itemBuildSlug = $request->get('itemBuildSlug');
		$isEdition = false;
		
		if ($itemBuildSlug != null) {
			try {
				$itemBuild = $itemBuildManager->findBySlug($itemBuildSlug);
				$isEdition = true;
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
		
		$itemBuild->setGameModeId($gameModes[$gameMode]);
		$itemBuild->setDescription($buildDescription);
		
		if ($isBuildPrivate == 'false') { 
			$itemBuild->setStatus(\MVNerds\CoreBundle\Model\ItemBuildPeer::STATUS_PUBLIC);
		} else {
			$itemBuild->setStatus(\MVNerds\CoreBundle\Model\ItemBuildPeer::STATUS_PRIVATE);
		}
		
		$i = 1;
		$itemBuildBlocks = new \PropelCollection();
		foreach ($itemsSlugs as $itemBlock)
		{	
			$itemBlockName = preg_replace('/[^a-zA-Z0-9 ]+/','',$itemBlock['name']);
			
			$itemBuildBlock = new ItemBuildBlock();
			$itemBuildBlock->setType($itemBlockName);
			$itemBuildBlock->setPosition($i++);
			
			if (isset($itemBlock['description']) && ($itemBuildBlockDescription = $itemBlock['description']) && '' != $itemBuildBlockDescription)
			{
				$itemBuildBlock->setDescription($itemBuildBlockDescription);
			}
			
			$items = $itemBlock['items'];
			foreach ($items as $item)
			{
				$itemSlug = $item['slug'];
				$itemCount = $item['count'] >= 1 ? $item['count'] : 1;
				$itemOrder = $item['order'] >= 1 ? $item['order'] : 1;
				
				try {
					$item = $itemManager->findBySlug($itemSlug);
				} catch (\Exception $e) {
					throw new HttpException(500, 'Invalid recommended items : item not found! slug given : '.$itemSlug);
				}
				
				$itemBuildBlockItem = new ItemBuildBlockItem();
				$itemBuildBlockItem->setItemId($item->getId());
				$itemBuildBlockItem->setCount($itemCount);
				$itemBuildBlockItem->setPosition($itemOrder);
				$itemBuildBlock->addItemBuildBlockItem($itemBuildBlockItem);
			}
			$itemBuildBlocks->append($itemBuildBlock);
		}
		$itemBuild->setItemBuildBlocks($itemBuildBlocks);
		
		$itemBuild->setName($buildName);
		
		$newItemBuildSlug = preg_replace('/[^\w]+/u', '-', $buildName);
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
			
			if (null != $saveBuild && $saveBuild == 'true' && $this->get('security.context')->isGranted('ROLE_USER')) {
				$user = $this->getUser();
				$nbItemBuilds = $itemBuildManager->countNbBuildsByUserId($user->getId());
				$itemBuild->setUser($user);
				
				if ($isEdition || $this->get('security.context')->isGranted('ROLE_ADMIN')) 
				{
					$itemBuild->save();
				}
				elseif ($nbItemBuilds < self::MAX_ITEM_BUILDS) 
				{
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
			$user = $this->getUser();
			$preferenceManager = $this->get('mvnerds.preference_manager');
			if (null === $path || '' == $path ) {
				try{
					$lolDirectoryPreference = $preferenceManager->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
					$path = $lolDirectoryPreference->getValue();
				}catch(\Exception $e) {
					$path = null;
				}
			} else {
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
		$finalSlug = $itemBuild->getSlug();
		$itemBuild->setSlug($itemBuild->getSlug() . '__' . $this->get('session')->getId() . '__');
		
		$batchManager->createRecItemBuilder($itemBuild, $path);

		return new Response(json_encode($finalSlug));
	}
	
	/**
	 * @Route("/{itemBuildSlug}/download-rec-items", name="item_builder_download_file", options={"expose"=true})
	 */
	public function executeDownloadItemBuildAction($itemBuildSlug)
	{
		try{
			/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
			$itemBuild = $this->get('mvnerds.item_build_manager')->findBySlug($itemBuildSlug);
			$itemBuild->setDownload($itemBuild->getDownload()+1);
			$itemBuild->keepUpdateDateUnchanged();
			$itemBuild->save();
		} catch (\Exception $e) {
			//Si le build n est pas trouvé en base de données on ne fait rien
		}
		
		$path = $this->container->getParameter('item_builds_path') . $itemBuildSlug . '__' . $this->get('session')->getId() .'__.bat';
		
		$response = new Response();
		
		if (file_exists($path))
		{
			$response->headers->set('Content-Type', 'application/octet-stream');
			$response->headers->set('Content-Disposition', 'attachment;filename="'.$itemBuildSlug.'.bat"');
			$response->headers->set('Content-Transfer-Encoding', 'binary');
			$response->headers->set('Content-Length', filesize($path));
			$response->sendHeaders();
			$response->setContent(readfile($path));
			
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
			return new Response(json_encode('getItemPopover : Impossible de trouver l\'item'));
		}
		
		return $this->render('MVNerdsItemHandlerBundle:Popover:item_popover_content.html.twig', array(
			'item' => $item
		));
	}
	
	/**
	 * @Route("/get-item-modal-content", name="item_builder_get_item_modal_content", options={"expose"=true})
	 */
	public function getItemModalContentAction()
	{
		$request = $this->getRequest();
		if  (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'La requête doit être effectuée en AJAX et en method POST !');
		}
		
		$slug = $request->get('slug');
		try{
			$item = $this->get('mvnerds.item_manager')->findBySlugForPopover($slug);
		} catch (\Exception $e) {
			return new Response(json_encode('getItemModalContent : Impossible de trouver l\'item'));
		}
		/* @var $item MVNerds\CoreBundle\Model\Item */
		
		$stdItem = new \stdClass();
		$stdItem->slug = $item->getSlug();
		$stdItem->name = $item->getName();
		$stdItem->totalCost = $item->getTotalCost();
		$stdItem->cost = $item->getCost();
		$stdItem->code = $item->getRiotCode();
		$stdItem->children = array();
		$stdItem->parents = array();
		$stdItem->primaryEffects = array();
		$stdItem->secondaryEffects = array();
				
		foreach ($item->getItemGeneologiesRelatedByParentId() as $itemGeneology) {
			$stdItem->children[] = $itemGeneology->getItemRelatedByChildId()->getSlug();
		}
		foreach ($item->getItemGeneologiesRelatedByChildId() as $itemGeneology) {
			$parentSlug = $itemGeneology->getItemRelatedByParentId()->getSlug();
			if ( !in_array($parentSlug, $stdItem->parents) )
			{
				$stdItem->parents[] = $parentSlug;
			}
		}
		foreach ($item->getItemPrimaryEffects() as $itemPrimaryEffect) {
			$stdItem->primaryEffects[] = $itemPrimaryEffect->getValue() . ' ' . $itemPrimaryEffect->getPrimaryEffect()->getLabel();
		}
		foreach ($item->getItemSecondaryEffects() as $itemSecondaryEffect) {
			$stdItem->secondaryEffects[] = $itemSecondaryEffect->getDescription();
		}
		
		return new Response(json_encode($stdItem));
	}
	
	/**
	 * @Route("/init-item-modal-array", name="item_builder_init_item_modal_array", options={"expose"=true})
	 */
	public function initItemModalArrayAction()
	{
		$request = $this->getRequest();
		if  (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'La requête doit être effectuée en AJAX et en method POST !');
		}
		
		
		$items = $this->get('mvnerds.item_manager')->findAllActiveForItemModal();
		
		$itemModalArray = array();
		
		foreach ($items as $item)
		{
		
			/* @var $item MVNerds\CoreBundle\Model\Item */

			$stdItem = new \stdClass();
			$stdItem->slug = $item->getSlug();
			$stdItem->name = $item->getName();
			$stdItem->totalCost = $item->getTotalCost();
			$stdItem->cost = $item->getCost();
			$stdItem->code = $item->getRiotCode();
			$stdItem->children = array();
			$stdItem->parents = array();
			$stdItem->primaryEffects = array();
			$stdItem->secondaryEffects = array();

			foreach ($item->getItemGeneologiesRelatedByParentId() as $itemGeneology) {
				$child =  $itemGeneology->getItemRelatedByChildId();
				if (!$child->getIsObsolete()) {
					$stdItem->children[] = $child->getSlug();
				}
			}
			foreach ($item->getItemGeneologiesRelatedByChildId() as $itemGeneology) {
				$parent = $itemGeneology->getItemRelatedByParentId();
				if (!$parent->getIsObsolete() && !in_array($parent->getSlug(), $stdItem->parents) )
				{
					$stdItem->parents[] = $parent->getSlug();
				}
			}
			foreach ($item->getItemPrimaryEffects() as $itemPrimaryEffect) {
				$stdItem->primaryEffects[] = $itemPrimaryEffect->getValue() . ' ' . $itemPrimaryEffect->getPrimaryEffect()->getLabel();
			}
			foreach ($item->getItemSecondaryEffects() as $itemSecondaryEffect) {
				$stdItem->secondaryEffects[] = $itemSecondaryEffect->getDescription();
			}
			
			$itemModalArray[$stdItem->slug] = $stdItem;
		}
		return new Response(json_encode($itemModalArray));
	}
	
	/**
	 * @Route("/edit/{itemBuildSlug}", name="pmri_edit")
	 * @Secure(roles="ROLE_USER")
	 */
	public function editBuildAction($itemBuildSlug) 
	{		
		/* @var $itemBuildManager \MVNerds\CoreBundle\ItemBuild\ItemBuildManager */
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
			
		try {
			/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
			$itemBuild = $itemBuildManager->findBySlug($itemBuildSlug);
		} catch (\Exception $e ) {
			return $this->redirect($this->generateUrl('item_builder_list'));
		}
		
		if( ! ($this->getUser()->getId() == $itemBuild->getUserId() || $this->get('security.context')->isGranted('ROLE_ADMIN')))
		{
			throw new AccessDeniedException();
		}
		
		$selectedChampions = array();
		foreach ($itemBuild->getChampionItemBuildsJoinChampion() as $championItemBuild) 
		{
			$selectedChampions[] = $championItemBuild->getChampion()->getSlug();
		}
		
		$itemBuildItemBlocks = $itemBuild->getItemBuildBlocks();
		$selectedItems = array();
		foreach ($itemBuildItemBlocks as $itemBuildItemBlock)
		{
			$type = $itemBuildItemBlock->getType();
			$ecapedName = preg_replace('/ +/', '_', $type);
			$position = $itemBuildItemBlock->getPosition();
			
			$selectedItems[$position] = array(
				'type'		=> $type, 
				'escaped'	=> $ecapedName, 
				'items'	=> array(),
				'description' => $itemBuildItemBlock->getDescription()
			);
			
			foreach ( $itemBuildItemBlock->getItemBuildBlockItems() as $itemBuildBlockItem)
			{
				$selectedItems[$position]['items'][] = array(
					'item'		=> $itemBuildBlockItem->getItem(),
					'count'	=> $itemBuildBlockItem->getCount()
				);
			}
		}
		
		/* @var $tagManager \MVNerds\CoreBundle\Tag\TagManager */
		$tagManager = $this->get('mvnerds.tag_manager');
		$tags = array();
		$tags['attack'] = $tagManager->findByParentName('BASE_ITEM_ATTACK');
		$tags['magic'] = $tagManager->findByParentName('BASE_ITEM_MAGIC');
		$tags['defense'] = $tagManager->findByParentName('BASE_ITEM_DEFENSE');
		$tags['other'] = $tagManager->findByParentName('BASE_ITEM_OTHER');
		
		return $this->render('MVNerdsItemHandlerBundle:PMRI:pmri_create.html.twig', array(
			'champions'			=> $this->get('mvnerds.champion_manager')->findAllWithTags(),
			'items'			=> $this->get('mvnerds.item_manager')->findAllActive(),
			'selectedChampions'	=> $selectedChampions,
			'selectedItems'		=> $selectedItems,
			'build_name'			=> $itemBuild->getName(),
			'build_description'		=> $itemBuild->getDescription(),
			'game_mode'		=> $itemBuild->getGameMode()->getLabel(),
			'item_build_slug'		=> $itemBuildSlug,
			'is_build_private'		=> ($itemBuild->getStatus() == \MVNerds\CoreBundle\Model\ItemBuildPeer::STATUS_PRIVATE),
			'edition_mode'		=> true,
			'tags'				=>$tags
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
			$itemBuild = $itemBuildManager->findBySlug($itemBuildSlug);
		} catch (\Exception $e ) {
			return $this->redirect($this->generateUrl('summoner_profile_index'));
		}
		
		if($this->getUser()->getId() == $itemBuild->getUserId() || $this->get('security.context')->isGranted('ROLE_ADMIN'))
		{
			$itemBuild->delete();
		}
		
		return $this->redirect($this->generateUrl('summoner_profile_index'));
	}
	
	/**
	 * @Route("/load-more-comment", name="item_build_load_more_comment", options={"expose"=true})
	 */
	public function loadMoreCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$itemBuildSlug = $request->get('object_slug', null);
		$page = $request->get('page', null);
		if (null == $itemBuildSlug || null == $page) {
			throw new HttpException(500, 'object_slug | page is/are missing!');
		}
		
		try {
			$itemBuild = $this->get('mvnerds.item_build_manager')->findBySlug($itemBuildSlug);
		}
		catch(Exception $e) {
			throw new InvalidArgumentException('Item build not found for slug:`'. $itemBuildSlug .'`');
		}

		return $this->forward('MVNerdsCommentBundle:Comment:loadMoreComment', array(
			'object'	=> $itemBuild,
			'page'		=> $page
		));
	}
}
