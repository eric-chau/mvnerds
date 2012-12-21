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
	 * @Route("/create", name="item_builder_create", options={"expose"=true})
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
	 * @Route("/list", name="item_builder_list", options={"expose"=true})
	 */
	public function listAction() 
	{
		return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:list_index.html.twig');
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
				$this->renderView('MVNerdsItemHandlerBundle:ItemBuilder:list_column_champion.html.twig', array('itemBuild' => $itemBuild)),
				$this->renderView('MVNerdsItemHandlerBundle:ItemBuilder:list_column_name.html.twig', array('itemBuild' => $itemBuild, 'user' => $itemBuild->getuser())),
				$translator->trans($itemBuild->getChampionItemBuilds()->getFirst()->getGameMode()->getLabel()),
				$this->renderView('MVNerdsItemHandlerBundle:ItemBuilder:list_column_actions.html.twig', array('itemBuild' => $itemBuild)),
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
	 * @Route("/view/{itemBuildSlug}/{dl}", name="item_builder_view", defaults={"dl"=null}, options={"expose"=true})
	 */
	public function viewAction($itemBuildSlug, $dl)
	{
		try{
			/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
			$itemBuild = $this->get('mvnerds.item_build_manager')->findOneBySlug($itemBuildSlug);
			$itemBuild->setView($itemBuild->getView() + 1);
			$itemBuild->save();
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('item_builder_list'));
		}
		$itemBuildItemsCollection = $itemBuild->getItemBuildItemss();
		$itemBlocks = array();
		
		$length = count($itemBuildItemsCollection);		
		for ($i = 0; $i < $length; $i++)
		{
			/* @var $itemBuildItems \MVNerds\CoreBundle\Model\ItemBuildItems */
			$itemBuildItems = $itemBuildItemsCollection[$i];
			
			$item = $itemBuildItems->getItem();
			$type = $itemBuildItems->getType();
			$position = $itemBuildItems->getPosition();
			$ecapedName = preg_replace('/ +/', '_', $type);
			if (! isset($itemBlocks[$position]))
			{
				$itemBlocks[$position] = array('type' => $type, 'escaped' => $ecapedName, 'items' => array());
			}
			
			$itemBlocks[$position]['items'][] = array('item' => $item, 'count' => $itemBuildItems->getCount());
		}
		ksort($itemBlocks);
		
		$lolDir = null;
		$canEdit = false;
		
		if ($this->get('security.context')->isGranted('ROLE_USER'))
		{
			$user = $this->get('security.context')->getToken()->getUser();
			if (($itemBuild->getUser()->getId() == $user->getId()) || $this->get('security.context')->isGranted('ROLE_ADMIN'))
			{
				$canEdit = true;
			}
			try{
				$lolDirPreference = $this->get('mvnerds.preference_manager')->findUserPreferenceByUniqueNameAndUserId('LEAGUE_OF_LEGENDS_DIRECTORY', $user->getId());
				$lolDir = $lolDirPreference->getValue();
			} catch(\Exception $e) {}
		}
		
		$params = array(
			'itemBuild'	=> $itemBuild,
			'itemBlocks'	=> $itemBlocks,
			'lol_dir'	=> $lolDir,
			'can_edit'	=> $canEdit
		);
		if ($dl != null && $dl == 'dl') {
			$params['start_dl'] = 'true';
		}
		
		return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:view_index.html.twig', $params);
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
		$isEdition = false;
		
		if ($itemBuildSlug != null) {
			try {
				$itemBuild = $itemBuildManager->findOneBySlug($itemBuildSlug);
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
		
		$i = 1;
		
		$itemBuilditemsCollection = new \PropelCollection();
		foreach ($itemsSlugs as $itemBlock)
		{
			$itemBlockName = $itemBlock['name'];
			$items = $itemBlock['items'];
			
			$itemBlockName = preg_replace('/[^a-zA-Z0-9 ]+/','',$itemBlockName);
			
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
				if (strpos($item->getGameModesToString(), 'shared') === false && strpos($item->getGameModesToString(), $gameMode) === false) {
					throw new HttpException(500, 'Invalid recommended items : game mode!');
				}
				$itemBuildItems = new ItemBuildItems();
				$itemBuildItems->setItemId($item->getId());
				$itemBuildItems->setType($itemBlockName);
				$itemBuildItems->setPosition($i);
				$itemBuildItems->setCount($itemCount);
				$itemBuildItems->setItemOrder($itemOrder);
				
				$itemBuilditemsCollection->append($itemBuildItems);
			}
			$i ++;
		}
		$itemBuild->setItemBuildItemss($itemBuilditemsCollection);
		
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
				
				if ($isEdition) 
				{
					$itemBuild->setUser($user);
					$itemBuild->save();
				}
				elseif($this->get('security.context')->isGranted('ROLE_ADMIN'))
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
			return new Response(json_encode('Impossible de trouver l\'item'));
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
			return new Response(json_encode('Impossible de trouver l\'item'));
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

			$selectedItems[$position]['items'][] = array('item' => $item, 'count' => $count);
		}
		ksort($selectedItems);
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
	
	/**
	 * @Route("/leave-comment", name="item_build_leave_comment", options={"expose"=true})
	 */
	public function leaveCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$itemBuildSlug = $request->get('object_slug', null);
		$userSlug = $request->get('user_slug', null);
		$commentMsg = $request->get('comment_msg', null);
		$lastCommentID = $request->get('last_comment_id', null);
		if (null == $itemBuildSlug || null == $userSlug || null == $commentMsg) {
			throw new HttpException(500, 'object_slug | user_slug | comment_msg is/are missing!');
		}
		
		if (0 != strcmp($userSlug, $this->getUser()->getSlug())) {
			throw new AccessDeniedException();
		}
		
		try {
			$itemBuild = $this->get('mvnerds.item_build_manager')->findOneBySlug($itemBuildSlug);
		}
		catch(Exception $e) {
			throw new InvalidArgumentException('Item build not found for slug:`'. $itemBuildSlug .'`');
		}
		
		return $this->forward('MVNerdsCommentBundle:Comment:leaveComment', array(
			'object'		=> $itemBuild,
			'user'			=> $this->getUser(),
			'commentMsg'	=> $commentMsg,
			'lastCommentID' => $lastCommentID
		));
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
			$itemBuild = $this->get('mvnerds.item_build_manager')->findOneBySlug($itemBuildSlug);
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
