<?php

namespace MVNerds\SkeletonBundle\SuperTag;

use \Criteria;

use MVNerds\CoreBundle\Exception\ObjectNotFoundException;
use MVNerds\CoreBundle\Model\SuperTag;
use MVNerds\CoreBundle\Model\SuperTagQuery;
use MVNerds\CoreBundle\Model\SuperTagPeer;

class SuperTagManager
{
	/**
	 * @param string $uniqueName le unique_name du super_tag à récupérer
	 * @throws ObjectNotFoundException si aucun superTag n'est associé au unique_name $uniqueName
	 * 
	 * @return SuperTag l'objet SuperTag qui correspond au unique_name $uniqueName
	 */
	public function findByUniqueName($uniqueName)
	{
		$superTag = SuperTagQuery::create()->findPk($uniqueName);

		if (!$superTag instanceof SuperTag) {
			throw new ObjectNotFoundException('Le SuperTag ayant pour UniqueName :' . $uniqueName . ' est introuvable');
		}

		return $superTag;
	}
	
	/**
	 * @param string $label le label du super_tag à récupérer
	 * @throws ObjectNotFoundException si aucun superTag n'est associé au label $label
	 * 
	 * @return SuperTag l'objet SuperTag qui correspond au label $label
	 */
	public function findByLabel($label)
	{
		$superTag = SuperTagQuery::create()
			->add(SuperTagPeer::LABEL, $label)
		->findOne();

		if (!$superTag instanceof SuperTag) {
			throw new ObjectNotFoundException('Le SuperTag ayant pour Label :' . $label . ' est introuvable');
		}

		return $superTag;
	}
	
	/**
	 * @param string $uniqueName le unique_name du super_tag à supprimer
	 * @throws ObjectNotFoundException si aucun super_tag n'est associé au unique_name $uniqueName
	 */
	public function deleteByUniqueName($uniqueName)
	{
		$superTag = SuperTagQuery::create()->findPk($uniqueName);

		if (!$superTag instanceof SuperTag) {
			throw new ObjectNotFoundException('Le SuperTag ayant pour UniqueName :' . $uniqueName . ' est introuvable');
		}

		$superTag->delete();
	}
	
	/**
	 * @param boolean $onlyRealTags true si on ne veut récupérer que les super_tags qui ne sont pas des alias
	 * false si on veut récupérer tous les super_tags confondus
	 * 
	 * @return \PropelCollection<MVNerds\CoreBundle\Model\SuperTag> retourne un objet PropelCollection qui contient
	 * tous les super_tag de la base de données
	 */
	public function findAll($onlyRealTags = false)
	{
		$superTags = SuperTagQuery::create()
			->OrderBy(SuperTagPeer::LABEL, Criteria::ASC);
		 
		if($onlyRealTags) {
			$superTags->add(SuperTagPeer::ALIAS_UNIQUE_NAME, null);
		}
		 
		return $superTags->find();
	}
	
	/**
	 * @param array $labels un tableau de labels correspondants à des super_tags ou à des alias
	 * @return \PropelCollection<MVNerds\CoreBundle\Model\SuperTag> retourne un objet PropelCollection qui contient
	 * tous les super_tag de la base de données ayants pour label un des labels fournit en paramètre
	 */
	public function findAllByLabels(array $labels)
	{
		return SuperTagQuery::create()
			->add(SuperTagPeer::LABEL, $labels, Criteria::IN)
			->OrderBy(SuperTagPeer::LABEL ,Criteria::ASC)
		->find();
	}
	
	/**
	 * Permet de récupérer, à partir d'une chaine de caractères composée de tags saisis par l'utilisateur,
	 * un tableau de UNIQUE_NAME de SuperTags avec les alias qui ont été remplacés par leurs SuperTag associé
	 * 
	 * @param string $labelsString une chaine de labels de "véritables" super_tags ou d'alias
	 * 
	 * @return array un tableau de chaines de caractères qui ne contient que les UNIQUE_NAMES de "véritables" 
	 * objets SuperTag
	 */
	public function getUniqueNamesFromString($labelsString)
	{
		//Récupération des labels sous forme de tableau
		$labelsArray = $this->forgeLabelsFromString($labelsString);
		//Récupération des objets SuperTag associés à ces labels
		$mixedSuperTags = $this->findAllByLabels($labelsArray);
		//On ne garde que les "véritables" SuperTags et on remplace les Alias par leur SuperTags associés
		$realSuperTagsUniqueNames = $this->getRealSuperTagsUniqueNames($mixedSuperTags);
		
		return $realSuperTagsUniqueNames;
	}
	
	
	/**
	 * Permet d'éditer un super tag en changeant sa primary key 'unique_name'
	 * 
	 * @param \MVNerds\CoreBundle\Model\SuperTag $newSuperTag Les nouvelles données à affecter à l'ancien super tag
	 * @param \MVNerds\CoreBundle\Model\SuperTag $oldSuperTag Le super tag que l'on veut éditer
	 */
	public function customSave(SuperTag $newSuperTag, SuperTag $oldSuperTag)
	{
		$con = \Propel::getConnection(SuperTagPeer::DATABASE_NAME);
		
		$sql = "UPDATE `super_tag` 
				SET `unique_name`=:new_unique_name, 
					`label`=:new_label,
					`alias_unique_name`=:new_alias_unique_name,
					`linked_object_id`=:new_linked_object_id,
					`linked_object_namespace`=:new_linked_object_namespace
				WHERE super_tag.unique_name=:old_unique_name";

		$stmt = $con->prepare($sql);
		$stmt->execute(array(
			':new_unique_name' => $newSuperTag->getUniqueName(),
			':new_label' => $newSuperTag->getLabel(),
			':new_alias_unique_name' => $newSuperTag->getAliasUniqueName(),
			':new_linked_object_id' => $newSuperTag->getLinkedObjectId(),
			':new_linked_object_namespace' => $newSuperTag->getLinkedObjectNamespace(),
			':old_unique_name' => $oldSuperTag->getUniqueName()
		));
	}
	
	/**
	 * Permet de créer, à partir d'une chaine de tags fournie par l'utilisateur, un tableau de labels
	 * 
	 * @param string $labelsString une chaine de labels de super_tags
	 * 
	 * @return array un tableau de chaines de caractères correspondant à des labels de super_tags
	 */
	private function forgeLabelsFromString($labelsString)
	{
		//On explose la chaine sur le caractère ','
		$labelsArray = explode(',', $labelsString);
		//On trim chaque élément obtenu
		$trimedLabelsArray = array_map('trim', $labelsArray);
		
		return $trimedLabelsArray;
	}
	
	/**
	 * Permet de ne conserver que les vrais super tags parmi une collection de super_tags en remplaçant
	 * les Alias par leur tag associé.
	 * 
	 * @param \PropelCollection<MVNerds\CoreBundle\Model\SuperTag> $mixedSuperTags un objet PropelCollection qui
	 * contient des objets SuperTag "véritables" ainsi que des alias de SuperTag
	 * 
	 * @return array un tableau de chaines de caractères qui ne contient plus
	 * que les UNIQUE_NAME de "véritables" objets SuperTag
	 */
	private function getRealSuperTagsUniqueNames($mixedSuperTags)
	{
		//tableau qui va contenir les "véritables" super_tags
		$realSuperTagsUniqueNames = array();
		
		//Parcours de tous les supers_tags passés en paramètres
		foreach ($mixedSuperTags as $mixedSuperTag) {
			//Si le super_tag à un alias_unique_name c'est que c'est un alias
			if ( ($aliasUniqueName = $mixedSuperTag->getAliasUniqueName()) && $aliasUniqueName != '') {
				//On récupère alors son "véritable" super_tag et on ajoute son unique_name au tableau
				try {
					$realSuperTagsUniqueNames[] = $this->findByUniqueName($aliasUniqueName)->getUniqueName();
				} catch(\Exception $e){}
			} else {
				//sinon on ajoute simplement au tableau des "véritables" super_tags son unique_name
				$realSuperTagsUniqueNames[] = $mixedSuperTag->getUniqueName();
			}
		}
		
		return $realSuperTagsUniqueNames;
	}
}