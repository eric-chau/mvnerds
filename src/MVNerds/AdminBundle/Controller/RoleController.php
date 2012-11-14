<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use MVNerds\CoreBundle\Form\Type\RoleType;

/**
 * @Route("/roles")
 */
class RoleController extends Controller
{
    /**
	 * Liste tous les roles de la plateforme
	 * 
     * @Route("/", name="admin_roles_index")
     */
    public function indexAction()
    {		
        return $this->render('MVNerdsAdminBundle:Role:index.html.twig', array(
        	'roles'	=> $this->get('mvnerds.role_manager')->findAll()
    	));
    }
	

    /**
     * Formulaire d'ajout d'un nouveau rôle
     *
     * @Route("/ajouter", name="admin_role_add")
     */
    public function addRoleAction()
    {
        $form = $this->createForm(new RoleType());

        $request = $this->getRequest();
        if ($request->isMethod('POST')) 
        {
            $form->bind($request);
            if ($form->isValid()) 
            {
                $role = $form->getData();
                // On créé le r$ole s'il contient des données valides
				$this->get('mvnerds.role_manager')->save($role);

                // Ajout d'un message de flash de succès
                $this->get('mvnerds.flash_manager')->setSuccessMessage('Flash.success.add.role');
                
                // On redirige l'utilisateur vers la liste des rôles
                return $this->redirect($this->generateUrl('admin_roles_index'));
            }
        }

        return $this->render('MVNerdsAdminBundle:Role:add_role_form.html.twig', array(
            'form' => $form->createView()
        ));
    }
	
	
	/**
     * Formulaire d'édition rôle
     *
	 * @Route("/{id}/editer", name="admin_role_edit")
     */
    public function editRoleAction($id)
    {
		$role = $this->get('mvnerds.role_manager')->findById($id);
        $form = $this->createForm(new RoleType(), $role);

        $request = $this->getRequest();
        if ($request->isMethod('POST')) 
        {
            $form->bind($request);
            if ($form->isValid()) 
            {
                $role = $form->getData();
                // L'utilisateur a passé la validation, on peut donc le sauvegarder
				$this->get('mvnerds.role_manager')->save($role);

                // Ajout d'un message de flash pour notifier que les informations du rôle ont bien été modifié
                $this->get('session')->setFlash('success', 'Les informations du rôle '.$role->getUniqueName().' ont bien été mise à jour.');
                
                // On redirige l'utilisateur vers la liste des rôles
                return $this->redirect($this->generateUrl('admin_roles_index'));
            }
        }

        return $this->render('MVNerdsAdminBundle:Role:edit_role_form.html.twig', array(
            'form' => $form->createView(),
			'role' => $role
        ));
    }
	

    /**
     * Supprimer l'utilisateur $id de la base de données;
     * TODO: Retrouver l'utilisateur selon son pseudo et non son id
     *
     * @Route("/{id}/supprimer", name="admin_role_delete")
     */
    public function deleteUserAction($id)
    {
        $this->get('mvnerds.user_manager')->deleteById($id);

        return new Response(json_encode(true));
    }
	
	
	/**
	 * @Route("/{id}/detail", name="admin_role_detail")
	 */
	public function detailRoleAction($id)
	{
		$role = $this->get('mvnerds.role_manager')->findById($id);
		
		return $this->render('MVNerdsAdminBundle:Role:role_detail.html.twig', array(
			'role'	=> $role,
			'users' => $this->get('mvnerds.role_manager')->getUsersByRole($role)
		));
	}
	
	
	/**
	 * 
	 * @Route("/add-role/{roleId}/to-user/{username}", name="admin_roles_add_role_to_user", options={"expose"=true})
	 */
	public function addRoleToUserAction($username, $roleId)
	{
		$user = $this->get('mvnerds.user_manager')->findByUsername($username);
		$status = $this->get('mvnerds.role_manager')->assignRoleToUser($user, $roleId);
		
		$params = array();
		if ($status) {
			$params['user'] = $user;
		}
		
		return $this->render('MVNerdsAdminBundle:Role:user_role_row.html.twig', $params);
	}
	
	
	/**
	 * 
	 * @Route("/remove-role/{roleId}/to-user/{username}", name="admin_roles_remove_role_from_user", options={"expose"=true})
	 */
	public function removeRoleFromUserAction($username, $roleId)
	{
		$status = $this->get('mvnerds.role_manager')->removeRoleToUser($this->get('mvnerds.user_manager')->findByUsername($username), $roleId);
		
		return new Response(json_encode($status));
	}
}
