<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVnerds\CoreBundle\Model\UserQuery;

class UserController extends Controller
{
    /**
     * @Route("/utilisateurs", name="admin_users_index")
     */
    public function indexAction()
    {
        return $this->render('MVNerdsAdminBundle:User:index.html.twig', array(
        	'users'	=> UserQuery::create()->find()
    	));
    }
}
