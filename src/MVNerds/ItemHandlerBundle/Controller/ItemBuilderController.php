<?php

namespace MVNerds\ItemHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use MVNerds\CoreBundle\Model\ItemBuild;
use MVNerds\CoreBundle\Model\ChampionItemBuild;
/**
 * @Route("/item-builder")
 */
class ItemBuilderController extends Controller
{

	/**
	 * @Route("/create", name="item_builder_create")
	 */
	public function createAction()
	{				
		return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:create_index.html.twig', array(
			'champions' => $this->get('mvnerds.champion_manager')->findAllWithTags(),
			'items'	=> $this->get('mvnerds.item_manager')->findAllWithTags()
		));
	}
	
	/**
	 * 
	 * @Route("/list/{championSlug}", name="item_builder_list", defaults={"championSlug"=null})
	 */
	public function listAction($championSlug) 
	{
		try {
			$champion = $this->get('mvnerds.champion_manager')->findBySlug($championSlug);
			return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:list_index.html.twig', array(
				'itemBuilds'		=> $this->get('mvnerds.item_build_manager')->findAll(),
				'championSlug'	=> $championSlug
			));
		} catch( \Exception $e) {
			return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:list_index.html.twig', array(
				'itemBuilds'	=> $this->get('mvnerds.item_build_manager')->findAll()
			));
		}
	}
	
	/**
	 * @Route("/batch", name="item_builder_batch")
	 */
	public function batchAction()
	{
		/* @var $itemBuildManager \MVNerds\CoreBundle\ItemBuild\ItemBuildManager */
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
		
		$itemBuild = new ItemBuild();
		$itemBuild = $itemBuildManager->findOneById(1);
		
		/* @var $batchManager \MVNerds\CoreBundle\Batch\BatchManager */
		$batchManager = $this->get('mvnerds.batch_manager');
		
		die($batchManager->createRecItemBuilder($itemBuild));
		
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
		
		$itemBuild = new \MVNerds\CoreBundle\Model\ItemBuild();
		
		$championsSlugs = $request->get('championsSlugs');
		$itemsSlugs = $request->get('itemsSlugs');
		$gameMode = $request->get('gameMode');
		$buildName = $request->get('buildName');//TODO a echaper
		$saveBuild = $request->get('saveBuild');
		
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
		foreach ($itemsSlugs as $itemSlug)
		{
			try {
				$item = $itemManager->findBySlug($itemSlug);
			} catch (\Exception $e) {
				throw new HttpException(500, 'Invalid recommended items : item not found!');
			}
			if (strpos($item->getGameModesToString(), 'shared') === false && strpos($item->getGameModesToString(), $gameMode) === false) {
				throw new HttpException(500, 'Invalid recommended items : game mode!');
			}
			
			$method = 'setItem'.$i.'Id';
			$itemBuild->$method($item->getId());
			$i ++;
		}
		
		$itemBuild->setName($buildName);
		
		$itemBuild->setSlug(preg_replace('/[^\w\/]+/u', '-', $buildName));
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
			
			//On vérifie qu il n y a pas d items spécifiques a un champion qui ne devrait pas etre la
			for ($i = 1; $i <=6; $i++) {
				$method = 'getItemRelatedByItem'.$i.'Id';
				/* @var $item \MVNerds\CoreBundle\Model\Item */
				$item = $itemBuild->$method();
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
			
			if (null != $saveBuild && $saveBuild == 'true') {
				$itemBuild->save();
			}
		} else {
			throw new HttpException(500, 'No valid champion given!');
		}
		
		/* @var $batchManager \MVNerds\CoreBundle\Batch\BatchManager */
		$batchManager = $this->get('mvnerds.batch_manager');

		$batchManager->createRecItemBuilder($itemBuild);

		return new Response(json_encode($itemBuild->getSlug()));
	}
	
	/**
	 * @Route("/{itemBuildSlug}/download-rec-items", name="item_builder_download_file", options={"expose"=true})
	 */
	public function executeDownloadItemBuildAction($itemBuildSlug)
	{
		$path = $this->container->getParameter('item_builds_path') . $itemBuildSlug . '.bat';
		
		$response = new Response();
		
		if (file_exists($path))
		{			
			$response->headers->set('ContentType', 'application/octetstream');
			$response->headers->set('Content-Disposition', 'attachment;filename='.basename($path));
			$response->headers->set('Content-Transfer-Encoding', 'binary');
			$response->headers->set('Content-Length', filesize($path));

			@readfile($path);
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
		return new Response(json_encode($this->get('mvnerds.item_manager')->getItemsName()->toArray()));
	}
}
