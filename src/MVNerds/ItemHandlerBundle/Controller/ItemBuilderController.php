<?php

namespace MVNerds\ItemHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
			
		));
    }
}
