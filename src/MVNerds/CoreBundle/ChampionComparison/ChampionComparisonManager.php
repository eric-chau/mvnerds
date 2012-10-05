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
	const MAX_CHAMPION_COMPARISON = 100;
	
	/**
	 * Constante contenant le nom de la clé associée à la liste de comparaison dans la session
	 */
	const COMPARISON_LIST_KEY = 'comparison_list';
	
	/**
	 * Constante contenant le nom de la clé associée au slug du champion de référence à comparer dans la session
	 */
	const REFERENCE_CHAMPION_KEY = 'reference_champion';
	
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
		return $this->session->get(self::COMPARISON_LIST_KEY);
	}
	
	/**
	 * Permet d'enregistrer une liste de champions en session
	 * 
	 * @param array $list la liste de champions à enregistrer en session
	 */
	public function setList($list)
	{
		$this->session->set(self::COMPARISON_LIST_KEY, $list);
	}
	
	/**
	 * Permet de récupérer le champion de référence pour le champion
	 * 
	 * @return Champion le champion de référence pour la comparaison s il existe et null sinon
	 */
	public function getReferenceChampion()
	{
		//Si la clé n existe pas en session on renvoie null
		if ( ! $this->isReferenceChampionSet() )
		{
			return null;
		}
		return $this->session->get(self::REFERENCE_CHAMPION_KEY);
	}
	
	/**
	 * Permet d enregistrer en session le champion de référence pour la comparaison
	 * 
	 * @param Champion $champion le champion dont on veut faire la référence
	 * 
	 * @throws InvalidArgumentException si le paramètre fourni n'est pas un champion ou qu'il n'apparaisse pas dans la liste de comparaison
	 */
	public function setReferenceChampion(Champion $champion)
	{
		//Si le champion existe dans la liste de comparaison
		if($this->championExists($champion))
		{
			//On l'indique comme champion de référence
			$this->session->set(self::REFERENCE_CHAMPION_KEY, $champion);
			//On fait passer le champion en premier dans la liste
			$this->sortListByChampion($champion);
		}
		else
		{
			throw new InvalidArgumentException('The given parameter is not a Champion item or does not appear in the champion comparison list');
		}
	}
	
	/**
	 * Permet de réinitialiser le champion de référence
	 */
	public function cleanReferenceChampion()
	{
		$this->session->set(self::REFERENCE_CHAMPION_KEY, null);
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
		return $this->session->has(self::COMPARISON_LIST_KEY) && is_array($this->session->get(self::COMPARISON_LIST_KEY));
	}
	
	/**
	 * Permet de vérifier si un champion de référence a été sélectionné
	 * 
	 * @return boolean renvoie true si un champion de référence est sétté et false sinon
	 */
	public function isReferenceChampionSet()
	{
		return $this->session->has(self::REFERENCE_CHAMPION_KEY) && $this->session->get(self::REFERENCE_CHAMPION_KEY) != null;
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
		return $champion && array_key_exists($champion->getSlug(), $this->getList());
	}
	
	/**
	 * Permet de déterminer si un champion est la référence de comparaison
	 * 
	 * @param Champion $champion le champion pour lequel on veut vérifier la référence
	 * 
	 * @return boolean renvoie true si le champion fourni en paramètre est le champion de référence et false sinon
	 */
	public function isReferenceChampion(Champion $champion)
	{
		$referenceChampion = $this->getReferenceChampion();
		return ( $referenceChampion != null ) && ( $referenceChampion->getSlug() == $champion->getSlug());
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
				
				//Si aucun champion de référence n'existe on déclare ce champion comme étant la référence
				if (! $this->isReferenceChampionSet())
				{
					$this->setReferenceChampion($champion);
				}
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
	}
	/**
	 * Permet d'ajouter plusieurs champions a la liste de comparaison
	 * 
	 * @param PropelCollection<MVNerds\CoreBundle\Model\Champion> $champions les champions à ajouter à la liste de comparaison
	 */
	public function addManyChampions($champions)
	{		
		//On vérifie que la taille du tableau ne soit pas dépassée
		if (! $this->isFull())
		{
			//On récupère la liste
			$comparisonList = $this->getList();
			$firstChamp = null;
			foreach($champions as $champion)
			{
				//Si le champion n'est pas déjà présent dans la liste
				if (!$this->championExists($champion) && !$this->isFull())
				{
					//On ajoute le nouveau champion
					$comparisonList[$champion->getSlug()] = $champion;	
				}
				if (! $this->isReferenceChampionSet() && $firstChamp == null)
				{
					$firstChamp = $champion;
				}
			}
			//On enregistre la nouvelle liste dans la session
			$this->setList($comparisonList);
			//Si aucun champion de référence n'existe on déclare ce champion comme étant la référence
			if ($firstChamp)
			{
				$this->setReferenceChampion($firstChamp);
			}
		}
		else
		{
			throw new Exception('Flash.error.max_reached.add_to_compare.champions');
		}
	}
	
	/**
	 * Permet de retirer un champion de la liste de comparaison
	 * 
	 * @param \MVNerds\CoreBundle\Model\Champion $champion le champion à retirer de la liste
	 * 
	 * @throws InvalidArgumentException si la champion n'est pas dans la liste de comparaison
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
			
			//Si le champion retiré était la référence de comparaison
			if ( $this->isReferenceChampion($champion) )
			{
				$this->cleanReferenceChampion();
				
				$comparisonList = $this->getList();
				$championNames = array_keys($comparisonList);
				
				if (count($championNames) >= 1)
				{
					$nextChampion = $comparisonList[$championNames[0]];
					$this->setReferenceChampion($nextChampion);
				}
			}
		}
		else
		{
			throw new InvalidArgumentException('Le champion ' . $champion->getSlug() . ' ne peut pas être retiré de la liste de comparaison');
		}
	}
	
	/**
	 * Permet de vider la liste de comparaison des champions
	 */
	public function cleanList()
	{
		//On vide la liste
		$this->setList(null);
		
		//On remet le champion de référence à zéro
		$this->cleanReferenceChampion();
		
		$this->flashManager->setSuccessMessage('Flash.success.clean_comparison.champions');
	}
	
	/**
	 * Permet de faire passer un champion présent dans la liste en tête de liste
	 * 
	 * @param Champion $champion le champion à faire passer en premier
	 * 
	 * @throw InvalidArgumentException si le champion fourni n'existe pas dans la liste de comparaison
	 */
	public function sortListByChampion(Champion $champion)
	{
		//Si le champion existe dans la liste
		if ($this->championExists($champion))
		{
			//On récupère la liste de comparaison courante
			$oldList = $this->getList();
			//On crée une nouvelle liste
			$newList = array();
			//On ajoute le champion à mettre en premier
			$newList[$champion->getSlug()] = $champion;
			//On parcourt tous les autres champions afin de les ajouter à la nouvelle liste
			foreach ($oldList as $champ)
			{
				if ($champ->getSlug() != $champion->getSlug())
				{
					$newList[$champ->getSlug()] = $champ;
				}
			}
			//On enregistre la nouvelle liste
			$this->setList($newList);
		}
		else
		{
			throw new InvalidArgumentException('Champion given does not exists in the comparison list');
		}
	}
}

?>
