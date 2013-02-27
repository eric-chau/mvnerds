<?php

namespace MVNerds\ViewBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\View\IView;

class ViewController extends Controller
{
    /**
	 * 
     */
    public function renderSimpleViewCountAction(IView $object)
    {
		$object->setView($object->getView() + 1);
		$object->save();
		
        return $this->render('MVNerdsViewBundle:View:render_simple_view_count.html.twig', array(
			'view_count' => $object->getView()
		));
    }
}
