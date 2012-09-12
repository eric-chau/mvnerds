<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\Form\Type\UserType;
use MVNerds\CoreBundle\Model\UserQuery;

/**
 * @Route("/utilisateurs")
 */
class UserController extends Controller
{
    /**
	 * Liste tous les utilisateurs de la plateforme
	 * 
     * @Route("/", name="admin_users_index")
     */
    public function indexAction()
    {
        return $this->render('MVNerdsAdminBundle:User:index.html.twig', array(
        	'users'	=> UserQuery::create()->find()
    	));
    }

    /**
     * Formulaire d'ajout d'un nouvel utilisateur
     *
     *@Route("/ajouter", name="admin_users_add")
     */
    public function addUserAction()
    {
        $form = $this->createForm(new UserType());

        $request = $this->getRequest();
        if ($request->isMethod('POST')) 
        {
            $form->bind($request);

            if ($form->isValid()) 
            {
                $user = $form->getData();
                // Persistance de l'objet en base de données
                $user->save();

                // Ajout d'un message de flash pour notifier que l'utilisateur a bien été créé
                $this->get('session')->setFlash('success', 'L\'utilisateur '.$user->getEmail().' a bien été ajouté.');
                
                // On redirige l'utilisateur vers la liste des utilisateurs
                return $this->redirect($this->generateUrl('admin_users_index'));
            }
        }

        return $this->render('MVNerdsAdminBundle:User:add_user_form.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
