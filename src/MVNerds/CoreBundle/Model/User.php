<?php

namespace MVNerds\CoreBundle\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

use MVNerds\CoreBundle\Model\om\BaseUser;


/**
 * Skeleton subclass for representing a row from the 'user' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class User extends BaseUser  implements AdvancedUserInterface
{
	private $plainPassword;
	
	/**
	 * Permet de vérifier si l'utilisateur courant possède une adresse mail unique ou non
	 * 
	 * @return boolean renvoie true si l'email est déjà utilisé, false sinon; renvoie également false
	 * si l'utilisateur a entré la même adresse mail que celui qu'il a actuellement
	 */
	public function isEmailAlreadyInUse()
	{
		return UserPeer::isEmailAlreadyInUse($this->getEmail(), ($this->getId() == null? null : $this));
	}
	
	/**
	 * Permet de vérifier si l'utilisateur courant possède une adresse mail avec un domaine valide ou non
	 * 
	 * @return boolean renvoie true si le domaine de l'email est valide, false sinon
	 */
	public function isValidDomain()
	{
		$domain = explode('@', $this->getEmail()); 
		if (checkdnsrr($domain[1])) {
			return false;
		}
		
		return true;
	}
	
	public function setPlainPassword($plainPassword)
    {
       $this->plainPassword = $plainPassword;
    }
	
	public function getPlainPassword()
    {
         return $this->plainPassword;
    }
	
	public function activateAccount()
	{
		$this->setIsActive(true);
	}
	
	/**
     * @inheritDoc
     */
    public function getRoles()
    {
		$roles = array();
		foreach($this->getUserRoles() as $userRole) {
			$roles[] = $userRole->getRole()->getUniqueName();
		}
		
        return $roles;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }
	
	public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->is_active;
    }
	
	public function getUserRoles($criteria = null, PropelPDO $con = null)
	{
		if(null === $this->collUserRoles || null !== $criteria) {
			if ($this->isNew() && null === $this->collUserRoles) {
				// return empty collection
				$this->initUserRoles();
			} else {
				$collUserRoles = UserRoleQuery::create(null, $criteria)
					->joinWith('Role')
					->filterByUser($this)
					->find($con);
				if (null !== $criteria) {
					return $collUserRoles;
				}
				$this->collUserRoles = $collUserRoles;
			}
		}
		return $this->collUserRoles;
	}
} // User
