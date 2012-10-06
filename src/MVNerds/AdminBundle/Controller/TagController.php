<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use MVNerds\CoreBundle\Form\Type\UserType;
use MVNerds\CoreBundle\Model\TagType;
use MVNerds\CoreBundle\Model\ChampionTag;
/**
 * @Route("/tags")
 */
class TagController extends Controller
{

	/**
	 * Liste tous les tags de la plateforme
	 *
	 * @Route("/", name="admin_tags_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsAdminBundle:Tag:index.html.twig', array(
			'tags' => $this->get('mvnerds.tag_manager')->findAll()
		));
	}
	
	/**
	 * Permet de créer un nouveau tag
	 * 
	 * @Route("/creer", name="admin_tags_create")
	 */
	public function createAction()
	{
		return $this->redirect($this->generateUrl('admin_tags_index'));
	}
	/**
	 * Permet d'éditer un tag
	 * 
	 * @Route("/{label}/editer", name="admin_tags_edit")
	 */
	public function editAction($label)
	{
		return $this->redirect($this->generateUrl('admin_tags_index'));
	}
	
	/**
	 * Permet d'affecter des champions à un tag
	 * 
	 * @Route("/{label}/gerer-affectations", name="admin_tags_manage_champions_affectation")
	 */
	public function manageChampionsAffectationAction($label)
	{		
		/* @var $tag \MVNerds\CoreBundle\Model\Tag*/
		$tag = $this->get('mvnerds.tag_manager')->findOneByLabel($label);
		
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$championName = $request->get('champion-name');
			if(null != $championName && $championName != '')
			{
				$championTag = new ChampionTag();
				$championTag->setChampion($this->get('mvnerds.champion_manager')->findByName($championName));
				$championTag->setTag($tag);
				
				$tag->addChampionTag($championTag);
				
				// On créé l'utilisateur s'il contient des données valides
				$this->get('mvnerds.tag_manager')->save($tag);

				// Ajout d'un message de flash de succès
				$this->get('mvnerds.flash_manager')->setSuccessMessage('Flash.success.affect.tag');
			}
			else
			{
				// Ajout d'un message de flash de succès
				$this->get('mvnerds.flash_manager')->setErrorMessage('Flash.error.affect.tag');
			}
			// On redirige l'utilisateur vers la liste des utilisateurs
			return $this->redirect($this->generateUrl('admin_tags_manage_champions_affectation', array('label' => $label)));
		}
		$c = new \Criteria();
		$c ->addAscendingOrderByColumn(\MVNerds\CoreBundle\Model\ChampionPeer::NAME);
		return $this->render('MVNerdsAdminBundle:Tag:manage_champions_affectation.html.twig', array(
			'tag'				=> $tag,
			'championsTagsAffected'	=> $tag->getChampionTagsJoinChampion($c),
			'championsNames'	=> json_encode($this->get('mvnerds.champion_manager')->getChampionsName()->toArray())
		));
	}
	
	/**
	 * Permet de retirer l affectation d'un champion à un tag
	 * 
	 * @Route("/{label}/{name}/retirer-affectation", name="admin_tags_remove_champions_affectation")
	 */
	public function removeChampionAffectationAction($label, $name)
	{
		/* @var $tag \MVNerds\CoreBundle\Model\Tag*/
		$tag = $this->get('mvnerds.tag_manager')->findOneByLabel($label);
		
		if(null != $name && $name != '')
		{
			$champion = $this->get('mvnerds.champion_manager')->findByName($name);

			$this->get('mvnerds.champion_tag_manager')->removeTagFromChampion($tag, $champion);

			// Ajout d'un message de flash de succès
			$this->get('mvnerds.flash_manager')->setSuccessMessage('Flash.success.unaffect.tag');
		}
		else
		{
			// Ajout d'un message de flash de succès
			$this->get('mvnerds.flash_manager')->setErrorMessage('Flash.error.unaffect.tag');
		}
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('admin_tags_manage_champions_affectation', array('label' => $label)));
	}
	
	/**
	 * Supprime un tag via son label
	 * 
	 * @Route("/{label}/supprimer", name="admin_tags_delete")
	 */
	public function deleteAction($label)
	{
		return $this->redirect($this->generateUrl('admin_tags_index'));
	}

}
