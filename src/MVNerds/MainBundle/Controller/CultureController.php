<?php

namespace MVNerds\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CultureController extends Controller
{
    /**
     * @Route("/switch-language/{locale}", name="culture_switch_language")
     */
    public function switchLanguageAction($locale)
    {
		$request = $this->getRequest();
		$request->setLocale($locale);
		
		$allowedLocales = $this->container->getParameter('available_languages');
		if (array_search($locale, $allowedLocales) === false) {
    		throw $this->createNotFoundException('La langue ' . $locale . ' n\'existe pas !');
    	}
		
		$host = $request->headers->get('host');
		$referer = $request->headers->get('referer');
		$route = str_split($referer, strpos($referer, $host) + strlen($host));
		$route[0] .= '/' . $locale;
		
		if (strlen($route[1]) ==1 && $route[1] == '/') {
			$route[1] = '';
		}
		else {
			foreach ($allowedLocales as $culture) {
				$route[1] = str_replace('/'.$culture, '', $route[1]);
			}
		}
				
        return $this->redirect($route[0].$route[1]);
    }
}