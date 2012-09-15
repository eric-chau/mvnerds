<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use MVNerds\CoreBundle\Form\Type\UserType;

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
        	'users'	=> $this->get('mvnerds.user_manager')->findAll()
    	));
    }
	

    /**
     * Formulaire d'ajout d'un nouvel utilisateur
     *
     * @Route("/ajouter", name="admin_users_add")
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
                // On créé l'utilisateur s'il contient des données valides
				$this->get('mvnerds.user_manager')->save($user);

                // Ajout d'un message de flash de succès
                $this->get('mvnerds.flash_manager')->setSuccessMessage('Flash.success.add.user');
                
                // On redirige l'utilisateur vers la liste des utilisateurs
                return $this->redirect($this->generateUrl('admin_users_index'));
            }
        }

        return $this->render('MVNerdsAdminBundle:User:add_user_form.html.twig', array(
            'form' => $form->createView()
        ));
    }
	
	
	/**
     * Formulaire d'édition utilisateur
	 * TODO: changer la diffusion de l'id dans l'url dès que possible
     *
	 * @Route("/editer/{id}", name="admin_users_edit")
     */
    public function editUserAction($id)
    {
		$user = $this->get('mvnerds.user_manager')->findById($id);
        $form = $this->createForm(new UserType(), $user);

        $request = $this->getRequest();
        if ($request->isMethod('POST')) 
        {
            $form->bind($request);
            if ($form->isValid()) 
            {
                $user = $form->getData();
                // On créé l'utilisateur s'il contient des données valides
				$this->get('mvnerds.user_manager')->save($user);

                // Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
                $this->get('session')->setFlash('success', 'Les informations de l\'utilisateur '.$user->getEmail().' ont bien été mise à jour.');
                
                // On redirige l'utilisateur vers la liste des utilisateurs
                return $this->redirect($this->generateUrl('admin_users_index'));
            }
        }

        return $this->render('MVNerdsAdminBundle:User:edit_user_form.html.twig', array(
            'form' => $form->createView(),
			'user' => $user
        ));
    }
	

    /**
     * Supprimer l'utilisateur $id de la base de données;
     * TODO: Retrouver l'utilisateur selon son pseudo et non son id
     *
     * @Route("/{id}/supprimer", name="admin_users_delete")
     */
    public function deleteUserAction($id)
    {
        $this->get('mvnerds.user_manager')->deleteById($id);

        return new Response(json_encode(true));
    }
}
