<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/items")
 */
class ItemController extends Controller
{
	/**
	 * Liste tous les items de la base
	 *
	 * @Route("/", name="admin_items_index")
	 */
	public function indexAction()
	{		
		die(var_dump($this->get('mvnerds.item_manager')->getItemsName()));
		
	}
}

?>
