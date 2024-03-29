<?php

namespace MVNerds\CoreBundle\User;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Swift_Message;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Exception\DisabledUserException;
use MVNerds\CoreBundle\Exception\UnknowUserException;
use MVNerds\CoreBundle\Exception\UserAlreadyEnabledException;
use MVNerds\CoreBundle\Exception\WrongActivationCodeException;
use MVNerds\CoreBundle\Model\PioneerUserPeer;
use MVNerds\CoreBundle\Model\PioneerUserQuery;
use MVNerds\CoreBundle\Model\Profile;
use MVNerds\CoreBundle\Role\RoleManager;
use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Model\UserPeer;
use MVNerds\CoreBundle\Model\UserQuery;
use MVNerds\CoreBundle\Model\ProfilePeer;
use MVNerds\CoreBundle\Model\GameAccountPeer;

class UserManager 
{	
	private $encoderFactory;
	private $mailer;
	private $templating;
	private $roleManager;
	private $userLocale;
	
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
		$profile = new Profile();
		$profile->setAvatarId(10);
		$user->setProfile($profile);
		
		// Finally
		$user->save();
		
		// Assign role process
		$this->roleManager->assignRoleToUser($user, $this->roleManager->findByUniqueName('ROLE_USER')->getId());
		
		// Check if is pioneer user or not
		if (null != PioneerUserQuery::create()->add(PioneerUserPeer::EMAIL, $user->getEmail())->findOne()) {
			$this->roleManager->assignRoleToUser($user, $this->roleManager->findByUniqueName('ROLE_PIONEER')->getId());
		}
		
		if ($this->userLocale == 'en') {
			$mailSubject  = 'Summoner, you are now registered on MVNerds.com';
			$mailTemplate = 'MVNerdsSiteBundle:MailTemplate:confirmation_mail_en.txt.twig';
		} else {
			$mailSubject = 'Invocateur, vous êtes inscrit sur MVNerds.com !';
			$mailTemplate = 'MVNerdsSiteBundle:MailTemplate:confirmation_mail.txt.twig';
		}
		// Send confirmation mail to user
		$message = Swift_Message::newInstance($mailSubject)
			->setSubject($mailSubject)
			->setFrom('registration@mvnerds.com')
			->setTo($user->getEmail())
			->setBody($this->templating->render($mailTemplate, array(
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
	
	public function findByUsername($username)
	{
		$user = UserQuery::create()
			->add(UserPeer::USERNAME, $username)
		->findOne();
		
		if (null === $user)
		{
			throw new InvalidArgumentException('No user with username:'.$username.'!');
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
	 * Récupère tous les mails des utilisateurs actifs de la base de données
	 */
	public function getActiveUsersMail()
	{
		return UserQuery::create()
			->add(UserPeer::IS_ACTIVE, true)
			->select(array('email'))
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
		$user = null;
		// On vérifie d'abord si un utilisateur est associé au slug fourni
		try {
			$user = $this->findBySlug($slug);
		}
		catch (InvalidArgumentException $e) {
			throw new UnknowUserException();
		}
		
		// On vérifie si le compte utilisateur est déjà actif ou non
		if ($user->isEnabled()) {
			throw new UserAlreadyEnabledException();
		}
		
		// On vérifie si le code d'activation est correct ou non
		if (0 != strcmp($user->getActivationCode(), $activationCode)) {
			throw new WrongActivationCodeException();
		}
		
		// Finally, account activation process
		$user->activateAccount();
		$user->setActivationCode('');
		$user->save();
	}
	
	public function initForgotPasswordProcess(User $user)
	{
		$user->setActivationCode(md5(uniqid(rand(), true)));
		$user->save();
		
		if ($this->userLocale == 'en') {
			$mailSubject  = 'Reset your MVNerds password!';
			$mailTemplate = 'MVNerdsSiteBundle:MailTemplate:forgot_password_mail_en.txt.twig';
		} else {
			$mailSubject = 'Réinitialiser votre mot de passe MVNerds !';
			$mailTemplate = 'MVNerdsSiteBundle:MailTemplate:forgot_password_mail.txt.twig';
		}
		
		$message = Swift_Message::newInstance()
			->setSubject($mailSubject)
			->setFrom('noreply@mvnerds.com')
			->setTo($user->getEmail())
			->setBody($this->templating->render($mailTemplate, array(
				'user' => $user
			)), 'text/plain');
		$this->mailer->send($message);
	}
	
	public function isValidResetPasswordAction($slug, $activationCode)
	{
		$user = null;
		// On vérifie d'abord si un utilisateur est associé au slug fourni
		try {
			$user = $this->findBySlug($slug);
		}
		catch (InvalidArgumentException $e) {
			throw new UnknowUserException();
		}
		
		// On vérifie si le compte utilisateur est déjà actif ou non
		if (!$user->isEnabled()) {
			throw new DisabledUserException();
		}
		
		// On vérifie si le code d'activation est correct ou non
		if (0 != strcmp($user->getActivationCode(), $activationCode)) {
			throw new WrongActivationCodeException();
		}
	}
	
	public function changeUserPassword(User $user, $newPassword) {
		$user->setSalt(base64_encode(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)));
		// Generate crypted password
		$encoder = $this->encoderFactory->getEncoder($user);
		$password = $encoder->encodePassword($newPassword, $user->getSalt());
		$user->setPassword($password);
		$user->setPlainPassword($newPassword);
		
		// Finally
		$user->setActivationCode('');
		$user->save();
		
		return $user;
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
	
	public function setRoleManager(RoleManager $roleManager)
	{
		$this->roleManager = $roleManager;
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
	
	public function findAllActiveAjax($limitStart = 0, $limitLength = 2, $orderArr = array('created_at' => 'desc'), $whereArr = array(), $gameAccount = false)
	{
		$userQuery = UserQuery::create()
			->offset($limitStart)
			->limit($limitLength)
			->add(UserPeer::IS_ACTIVE, true)
			->joinWith('Profile')
			->joinWith('Profile.Avatar')
			->joinWith('Profile.GameAccount', \Criteria::LEFT_JOIN);
		
		foreach($orderArr as $orderCol => $orderDir) {
			switch ($orderDir) {
				case 'asc':
					$userQuery->addAscendingOrderByColumn($orderCol);
					break;
				case 'desc':
					$userQuery->addDescendingOrderByColumn($orderCol);
					break;
				default:
					throw new PropelException('ModelCriteria::orderBy() only accepts Criteria::ASC or Criteria::DESC as argument');
			}
		}
		
		foreach($whereArr as $whereCol => $whereVal) {
			$userQuery->add($whereCol, '%' . $whereVal . '%', \Criteria::LIKE);
		}
		
		if ($gameAccount) {
			$userQuery->add(ProfilePeer::GAME_ACCOUNT_ID, null, \Criteria::NOT_EQUAL);
			$userQuery->add(GameAccountPeer::IS_ACTIVE, true);
		}
		
		$users = $userQuery->find();
		
		if (null === $users) {
			throw new InvalidArgumentException('No video found !');
		}
		
		return $users;
	}
	
	public function countAllActive()
	{
		$usersCount = UserQuery::create()
			->add(UserPeer::IS_ACTIVE, true)
		->count();
		
		return $usersCount;
	}
	
	public function countAllActiveAjax($whereArr = array(), $gameAccount = false)
	{
		$userQuery = UserQuery::create()
			->add(UserPeer::IS_ACTIVE, true)
			->joinWith('Profile')
			->joinWith('Profile.GameAccount', \Criteria::LEFT_JOIN);
	
		foreach($whereArr as $whereCol => $whereVal) {
			$userQuery->add($whereCol, '%' . $whereVal . '%', \Criteria::LIKE);
		}
		
		if ($gameAccount) {
			$userQuery->add(ProfilePeer::GAME_ACCOUNT_ID, null, \Criteria::NOT_EQUAL);
			$userQuery->add(GameAccountPeer::IS_ACTIVE, true);
		}
		
		return $userQuery->count();
	}
}
