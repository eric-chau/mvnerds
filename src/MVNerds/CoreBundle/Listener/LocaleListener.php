<?php

namespace MVNerds\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class LocaleListener implements EventSubscriberInterface
{
	private $defaultLocale;
	private $session;
	private $router;
	private $isMaintenanceTime;
	private $allowedIP = array(
		'127.0.0.1',
		'79.95.143.27',
		'213.245.227.123',
		'79.86.168.84'		
	);
	
	public function __construct($defaultLocale, $isMaintenanceTime) {
		$this->defaultLocale = $defaultLocale;
		$this->isMaintenanceTime = $isMaintenanceTime;
	}
	
	public function setLocale(GetResponseEvent $event)
	{
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}
		
		$request = $event->getRequest();
		// Test de savoir si le serveur doit être mis en maintenance ou non
		if ($this->isMaintenanceTime && !in_array($request->server->get('REMOTE_ADDR'), $this->allowedIP) && $request->get('_route') != 'site_maintenance') {
			$redirectResponse = new RedirectResponse($this->router->generate('site_maintenance'));

			$event->setResponse($redirectResponse);
		}
		else {
			$routeParams = $request->get('_route_params');
			if (isset($routeParams['_locale'])) {
				$locale = $this->session->get('locale', null);
				$userLocale = strtolower(substr($request->server->get('HTTP_ACCEPT_LANGUAGE'), 0, 2));
				if (null == $locale) {
					if ('fr' != $userLocale) {
						$userLocale = 'en';
					}

					$this->session->set('locale', $userLocale);
					$routeParams['_locale'] = $userLocale;
					$redirectResponse = new RedirectResponse($this->router->generate($request->get('_route'), $routeParams));

					$event->setResponse($redirectResponse);
				}
				else {
					if ($routeParams['_locale'] != $locale) {
						$routeParams['_locale'] = $locale;
						$redirectResponse = new RedirectResponse($this->router->generate($request->get('_route'), $routeParams));

						$event->setResponse($redirectResponse);
					}
				}
			}
		}
	}
		
	static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 128),
        );
    }
	
	public function setSession(Session $session)
	{
		$this->session = $session;
	}
	
	public function setRouter(Router $router)
	{
		$this->router = $router;
	}
}