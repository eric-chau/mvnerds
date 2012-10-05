<?php

namespace MVNerds\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class LocaleListener implements EventSubscriberInterface
{
	private $defaultLocale;
	private $session;
	
	public function __construct($defaultLocale) {
		$this->defaultLocale = $defaultLocale;
	}
	
	public function setLocale(GetResponseEvent $event)
	{
		
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}
		
		if (null == $event->getRequest()->get('_locale')) {
			$locale = $this->session->get('locale', null);
			$event->getRequest()->setLocale(null != $locale? $locale : $this->defaultLocale);
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
}