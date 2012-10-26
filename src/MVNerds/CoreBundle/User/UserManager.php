<?php

namespace MVNerds\CoreBundle\User;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Swift_Message;

use MVNerds\CoreBundle\Model\Profile;
use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Model\UserQuery;
use MVNerds\CoreBundle\Model\UserPeer;

class UserManager 
{	
	private $encoderFactory;
	private $mailer;
	private $templating;
	
	public function createUser(array $userParams)
	{
		// User creation process
		$user = new User();
		$user->setUsername($userParams['username']);
		$user->setEmail($userParams['email']);
		$user->setSalt(base64_encode(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)));
		// Generate crypted password
		$encoder = $this->encoderFactory->getEncoder($user);
		$password = $encoder->encodePassword($userParams['password'], $user->getSalt());
		$user->setPassword($password);
		// Generate unique activation code
		$user->setActivationCode(md5(uniqid(rand(), true)));
		$user->setPlainPassword($userParams['password']);
		$user->setProfile(new Profile());
		
		// Finally
		$user->save();
		
		// Send confirmation mail to user
		$message = Swift_Message::newInstance()
			->setSubject('Invocateur, vous êtes inscrit sur MVNerds.com !') // Utiliser le service de traduction
			->setFrom('registration@mvnerds.com')
			->setTo($user->getEmail())
			->setBody($this->templating->render('MVNerdsLaunchSiteBundle:Login:confirmation_mail.txt.twig', array(
				'user' => $user
			)), 'text/plain');
		$this->mailer->send($message);
						
		return $user;
	}
	
	/**
	 * Vérifies les informations contenu dans l'objet $user passé en paramètre; si tout se passe bien,
	 * l'utilisateur est créé en base de données
	 * 
	 * @param \MVNerds\CoreBundle\Model\User $user l'utilisateur a créé s'il contient des données valides
	 * @throws InvalidArgumentException exception levée lorsque :
	 * - ce n'est pas un nouvel utilisateur
	 * - l'adresse mail est déjà utilisée
	 */
	public function createUserIfValid(User $user)
	{
		// On vérifie que c'est bien un nouvel utilisateur en vérifiant qu'il n'a pas d'id
		if (null != $user->getId())
		{
			throw new InvalidArgumentException('Seems that\'s given user already exists!');
		}
		
		// On vérifie qu'il n'y a pas d'utilisateur dans la base de données avec la même adresse mail
		if (null != UserQuery::create()->add(UserPeer::EMAIL, $user->getEmail())->findOne())
		{
			throw new InvalidArgumentException('User with email:\''.$user->getEmail().'\' already exists!');
		}
		
		// Finally
		$this->save($user);
	}
	
	public function isEmailAvailable($email)
	{
		$isAvailable = true;
		try {
			$isAvailable = $this->findOneByEmail($email) != null;
		}
		catch (InvalidArgumentException $e) {
			$isAvailable = false;
		}
		
		return $isAvailable;
	}
	
	public function deleteById($id)
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
	}
	
	
	/**
	 * Récupère un objet User à partir de son identifiant $id 
	 * 
	 * @param integer $id l'id de l'utilisateur dont on souhaite récupérer l'objet User associé 
	 * @return MVNerds\CoreBundle\Model\User lobjet User qui correspond à l'id $id 
	 * @throws InvalidArgumentException exception levé si aucun utilisateur est associé à l'id $id
	 */
	public function findOneById($id)
	{
		$user = UserQuery::create()
			->add(UserPeer::ID, $id)
		->findOne();
		
		if (null === $user)
		{
			throw new InvalidArgumentException('No user with id:'.$id.'!');
		}
		
		return $user;
	}
	
	public function findOneByEmail($email)
	{
		$user = UserQuery::create()
			->add(UserPeer::EMAIL, $email)
		->findOne();
		
		if (null === $user)
		{
			throw new InvalidArgumentException('No user with email:'.$email.'!');
		}
		
		return $user;
	}
	
	public function findBySlug($slug)
	{
		$user = UserQuery::create()
			->add(UserPeer::SLUG, $slug)
		->findOne();
		
		if (null === $user)
		{
			throw new InvalidArgumentException('No user with slug:'.$slug.'!');
		}
		
		return $user;
	}
	
	/**
	 * Récupère tous les utilisateurs de la base de données
	 * 
	 * @return PropelCollection<MVNerds\CoreBundle\Model\User> retourne un objet PropelCollection qui contient
	 * tous les utilisateurs de la base de données
	 */
	public function findAll()
	{
		return UserQuery::create()
			->OrderBy(UserPeer::ID)
		->find();
	}
	
	
	/**
	 * Permet de persister en base de données l'utilisateur $user
	 * 
	 * @param \MVNerds\CoreBundle\Model\User $user l'objet utilisateur a persisté en base de données
	 */
	public function save(User $user)
	{
		$user->save();
	}
	
	public function activateAccount($slug, $activationCode)
	{
		$user = $this->findBySlug($slug);
		
		if (null == $user) {
			return false;
		}
		
		if (0 != strcmp($user->getActivationCode(), $activationCode)) {
			return false;
		}
		
		// Account activation process
		$user->activateAccount();
		$user->save();
		
		return true;
	}
	
	public function setEncoderFactory(EncoderFactory $encoderFactory)
	{
		$this->encoderFactory = $encoderFactory;
	}
	
	public function setMailer($mailer)
	{
		$this->mailer = $mailer;
	}
	
	public function setTemplating($templating)
	{
		$this->templating = $templating;
	}
}
