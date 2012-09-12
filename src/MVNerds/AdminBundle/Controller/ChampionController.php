<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use MVNerds\CoreBundle\Model\ChampionQuery;
use MVNerds\CoreBundle\Form\Type\ChampionType;

/**
 * @Route("/champions")
 */
class ChampionController extends Controller
{

	/**
	 * Liste tous les champions de la base
	 * @Route("/", name="admin_champions_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsAdminBundle:Champion:index.html.twig', array(
					'champions' => ChampionQuery::create()->find()
				));
	}

	/**
	 * Formulaire d'ajout d'un nouveau champion
	 *
	 * @Route("/ajouter", name="admin_champions_add")
	 */
	public function addChampionAction()
	{
		$form = $this->createForm(new ChampionType());

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);

			if ($form->isValid())
			{
				$champion = $form->getData();
				// Persistance de l'objet en base de données
				$champion->save();

				// Ajout d'un message de flash pour notifier que le champion a bien été ajouté
				$this->get('session')->setFlash('success', 'Le champion ' . $champion->getName() . ' a bien été ajouté.');

				// On redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('admin_champions_index'));
			}
		}
		return $this->render('MVNerdsAdminBundle:Champion:add_champion_form.html.twig', array(
			'form' => $form->createView()
		));
	}
}
