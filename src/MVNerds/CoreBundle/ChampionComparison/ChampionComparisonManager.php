<?php

namespace MVNerds\CoreBundle\ChampionComparison;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;

use \ComparisonListHasChampionException;
use \ComparisonListFullException;

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
	 * Constante contenant le nombre de comparaisons simultanées de champions
	 */
	const MAX_CHAMPION_COMPARISON = 10;
	
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
	 * Permet d'initialiser la liste des champions
	 */
	public function initComparisonList()
	{
		$this->setList(array());
	}
	
	public function setList($list)
	{
		$this->session->set(self::TAG, $list);
	}
	
	/**
	 * Permet de savoir si la liste de comparaison a été settée ou non et si c'est bien un tableau
	 * 
	 * @return true si la liste a été sétée dans la session en tant que array et false sinon
	 */
	public function isListSet()
	{
		return $this->session->has(self::TAG) && is_array($this->getList());
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
			}
			else
			{
				throw new ComparisonListHasChampionException('Champion with slug ' . $champion->getSlug() . ' is already in the comparison list');
			}
		}
		else
		{
			throw new ComparisonListFullException('Champion comparison list is full');
		}
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
		}
		else
		{
			throw new ComparisonListHasNotChampionException('Champion with slug ' . $champion->getSlug() . ' is not in the comparison list');
		}
	}
	
	/**
	 * Permet de vider la liste de comparaison des champions
	 */
	public function cleanList()
	{
		$this->setList(null);
	}
}

?>
