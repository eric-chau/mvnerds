<?php

namespace MVNerds\ItemHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

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
			'champions' => $this->get('mvnerds.champion_manager')->findAll(),
			'items'	=> $this->get('mvnerds.item_manager')->findAll()
		));
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
		
		$itemBuild = new ItemBuild();

		$championsSlugs = $request->get('championsSlugs');
		$itemsSlugs = $request->get('itemsSlugs');
		$gameMode = $request->get('gameMode');
		$buildName = $request->get('buildName');

		/* @var $itemManager \MVNerds\CoreBundle\Item\ItemManager */
		$itemManager = $this->get('mvnerds.item_manager');
		
		$i = 1;
		foreach ($itemsSlugs as $itemSlug)
		{
			$item = $itemManager->findBySlug($itemSlug);
			$method = 'setItem'.$i.'Id';
			$itemBuild->$method($item->getId());
			$i ++;
		}
		
		$itemBuild->setName($buildName);
		
		$itemBuild->save();
		
		$gameModes = array(
			'dominion'	=> 2,
			'classic'	=> 1,
			'aram'		=> 3
		);
		
		/* @var $championManager \MVNerds\CoreBundle\Champion\ChampionManager */
		$championManager = $this->get('mvnerds.champion_manager');
		
		foreach ($championsSlugs as $championSlug)
		{
			$champion = $championManager->findBySlug($championSlug);
			
			$championItemBuild = new ChampionItemBuild();
			$championItemBuild->setChampion($champion);
			$championItemBuild->setItemBuild($itemBuild);
			$championItemBuild->setGameModeId($gameModes[$gameMode]);
			$championItemBuild->setIsDefaultBuild(false);
			$championItemBuild->save();
		}
		
		/* @var $batchManager \MVNerds\CoreBundle\Batch\BatchManager */
		$batchManager = $this->get('mvnerds.batch_manager');

		$batchManager->createRecItemBuilder($itemBuild);

		return new Response(json_encode($itemBuild->toArray()));
	}
}
