<?php

namespace MVNerds\CoreBundle\Utils;

/**
 * Classe qui contient des fonctions static pratique en rapport avec la validation des objets
 * 
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class ObjectValidation
{
	/**
	 * --> Méthode static <--
	 * Cette méthode permet de vérifier si tous les élements du tableau $objects passé
	 * en paramètre sont tous bien du type $classNamepace
	 * 
	 * @param array $objects tableau contenant des objets dont vous voulez vérifier le type
	 * @param string $classNamespace namespace de la classe avec laquelle vous voulez confronter
	 * le type des objets du tableau $objetcs (donné le namespace complet de la classe ! (Exemple :
	 * 'MVNerds\CoreBundle\Model\Champion')
	 * @return boolean la valeur vaut true si tous les objets du paramètre $objects ont pour type d'objet
	 * $classNamespace, false sinon
	 */
	public static function isObjectsInstanceof(array $objects, $classNamespace)
	{
		$isObjectsInstanceof = true;		
		// On boucle sur chaque objet pour tester un à un leur type
		foreach ($objects as $object)
		{
			// Tant que le type correspond, on continue
			if ($object instanceof $classNamespace)
			{
				continue;
			}
			
			// Dès que l'on trouve un objet qui n'est pas du bon type, on arrête la boucle et on renvoi false
			$isObjectsInstanceof = false;
			break;
		}
		
		return $isObjectsInstanceof;
	}
}