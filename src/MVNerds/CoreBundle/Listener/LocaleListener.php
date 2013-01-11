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
	
	public function __construct($defaultLocale) {
		$this->defaultLocale = $defaultLocale;
	}
	
	public function setLocale(GetResponseEvent $event)
	{
		
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}
		
		$request = $event->getRequest();
		$routeParams = $request->get('_route_params');
		if (isset($routeParams['_locale'])) {
			$locale = $this->session->get('locale', null);
			$userLocale = strtolower(substr($request->server->get('HTTP_ACCEPT_LANGUAGE'), 0, 2));
			if (null == $locale) {
				if ('en' != $userLocale) {
					$userLocale = $this->defaultLocale;
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