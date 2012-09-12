<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Form\Type\UserType;
use MVNerds\CoreBundle\Model\UserQuery;
use MVNerds\CoreBundle\Model\UserPeer;

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

    /**
     * Supprimer l'utilisateur $id de la base de données;
     * TODO: Retrouver l'utilisateur selon son pseudo et non son id
     *
     * @Route("/{id}/supprimer", name="admin_users_delete")
     */
    public function deleteUserAction($id)
    {
        $user = UserQuery::create()
            ->add(UserPeer::ID, $id)
        ->findOne();

        if (null === $user)
        {
            throw new InvalidArgumentException('User with id:'.$id.' does not exist!');
        }

        // Finally
        $user->delete();

        return new Response(json_encode(true));
    }
}
