<?php

namespace MVNerds\CoreBundle\ChampionComparison;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Exception\Exception;

use MVNerds\CoreBundle\Flash\FlashManager;
use MVNerds\CoreBundle\Model\Champion;

class ChampionComparisonManager
{
	 /**
	 * Variable contenant la session courante utilisée pour affecter ou retirer des champions à une liste
	 * 
	 * @var \Symfony\Component\HttpFoundation\Session\Session la session courante 
	 */
	private $session;
	
	/**
	 *Variable permettant d'afficher des messages flashs
	 * 
	 * @var \MVNerds\CoreBundle\Flash\FlashManager
	 */
	private $flashManager;
	
	/**
	 * Constante contenant le nombre de comparaisons simultanées de champions
	 */
	const MAX_CHAMPION_COMPARISON = 2;
	
	/**
	 * Constante contenant le nom du tag associé à la liste de comparaison dans la session
	 */
	const TAG = 'comparison_list';
	
	/**
	 * Méthode appelée lors de l'instanciation du service pour setter la session courante
	 * 
	 * @param Symfony\Component\HttpFoundation\Session\Session $session la session à sauvegarder
	 */
	public function setSession(Session $session)
	{
		$this->session = $session;
	}
	
	/**
	 * Méthode appelée lors de l'instanciation du service pour setter le flash manager
	 * 
	 * @param \MVNerds\CoreBundle\Flash\FlashManager $flashManager
	 */
	public function setFlashManager(FlashManager $flashManager)
	{
		$this->flashManager = $flashManager;
	}	
	
	/**
	 * Permet de récupérer la liste de comparaison des champions stockée en session
	 * 
	 * @return array le tableau de champions
	 */
	public function getList()
	{
		if ( ! $this->isListSet() )
		{
			$this->initComparisonList();
		}
		return $this->session->get(self::TAG);
	}
	
	/**
	 * Permet d'enregistrer une liste de champions en session
	 * 
	 * @param array $list la liste de champions à enregistrer en session
	 */
	public function setList($list)
	{
		$this->session->set(self::TAG, $list);
	}
	
	/**
	 * Permet d'initialiser la liste des champions
	 */
	private function initComparisonList()
	{
		$this->setList(array());
	}
	
	/**
	 * Permet de savoir si la liste de comparaison a été settée ou non et si c'est bien un tableau
	 * 
	 * @return true si la liste a été sétée dans la session en tant que array et false sinon
	 */
	private function isListSet()
	{
		return $this->session->has(self::TAG) && is_array($this->session->get(self::TAG));
	}
	
	/**
	 * Permet d'obtenir le nombre de champions présents dans la liste de compraison
	 * 
	 * @return int le nombre de champions présents dans la liste
	 */
	public function getListSize()
	{
		return count($this->getList());
	}
	
	/**
	 * Permet de savoir si la liste de comparaison des champions est vide ou non
	 * 
	 * @return boolean renvoie true si la liste de comparaison est vide et false sinon
	 */
	public function isEmpty()
	{
		return $this->getListSize() == 0;
	}
	
	/**
	 * Permet de savoir si la liste est remplie ou non
	 * 
	 * @return  true si la liste à atteint le maximum et false sinon
	 */
	public function isFull()
	{
		return $this->getListSize() >= self::MAX_CHAMPION_COMPARISON;
	}
	
	/**
	 * Permet de déterminer si un champion est déjà présent dans la liste de comparaison ou non
	 * 
	 * @param Champion $champion le champion dont on vérifier la présence dans la liste
	 * 
	 * @return true si un champion du même slug existe dans la liste et false sinon
	 */
	public function championExists($champion)
	{
		return array_key_exists($champion->getSlug(), $this->getList());
	}
	
	/**
	 * Permet de savoir si la liste peut être comparée ou non
	 * 
	 * @return true si la liste peut être comparée et false sinon
	 */
	public function isComparable()
	{
		return $this->getListSize() >= 2;
	}
	
	/**
	 * Permet d'ajouter un champion a la liste de comparaison des champions
	 * 
	 * @param \MVNerds\CoreBundle\Model\Champion $champion le champion à ajouter à la liste de comparaison
	 * 
	 * @retrun true si le champion a bien été ajouté et false sinon
	 */
	public function addChampion(Champion $champion)
	{		
		//On vérifie que la taille du tableau ne soit pas dépassée
		if (! $this->isFull())
		{
			//Si le champion n'est pas déjà présent dans la liste
			if (!$this->championExists($champion))
			{
				//On récupère la liste
				$comparisonList = $this->getList();
				//On ajoute le nouveau champion
				$comparisonList[$champion->getSlug()] = $champion;
				//On enregistre la nouvelle liste dans la session
				$this->setList($comparisonList);
				
				return true;
			}
			else
			{
				throw new InvalidArgumentException('Flash.error.already_in_list.add_to_compare.champions');
			}
		}
		else
		{
			throw new Exception('Flash.error.max_reached.add_to_compare.champions');
		}
		return false;
	}
	
	/**
	 * Permet de retirer un champion de la liste de comparaison
	 * 
	 * @param \MVNerds\CoreBundle\Model\Champion $champion le champion à retirer de la liste
	 */
	public function removeChampion(Champion $champion)
	{
		//On récupère la liste
		$comparisonList = $this->getList();
		
		//Si le champion existe dans la liste
		if ($this->championExists($champion))
		{
			//On le retire
			unset($comparisonList[$champion->getSlug()]);
			//Et on sauvegarde la nouvelle liste en session
			$this->setList($comparisonList);
			$this->flashManager->setSuccessMessage('Flash.success.remove_from_compare.champions');
		}
		else
		{
			$this->flashManager->setErrorMessage('Le champion ' . $champion->getSlug() . ' n\'est pas dans la liste de comparaison');
		}
	}
	
	/**
	 * Permet de vider la liste de comparaison des champions
	 */
	public function cleanList()
	{
		//On vide la liste
		$this->setList(null);
		
		$this->flashManager->setSuccessMessage('Flash.success.clean_comparison.champions');
	}
}

?>
