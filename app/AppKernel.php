<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{

	public function registerBundles()
	{
		$bundles = array(
			new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new Symfony\Bundle\SecurityBundle\SecurityBundle(),
			new Symfony\Bundle\TwigBundle\TwigBundle(),
			new Symfony\Bundle\MonologBundle\MonologBundle(),
			new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
			new Symfony\Bundle\AsseticBundle\AsseticBundle(),
			new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
			new JMS\AopBundle\JMSAopBundle(),
			new JMS\DiExtraBundle\JMSDiExtraBundle($this),
			new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
			new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
			new Propel\PropelBundle\PropelBundle(),
			new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
			new MVNerds\CoreBundle\MVNerdsCoreBundle(),
			new MVNerds\AdminBundle\MVNerdsAdminBundle(),
            new MVNerds\MainBundle\MVNerdsMainBundle(),
            new MVNerds\SiteBundle\MVNerdsSiteBundle(),
            new MVNerds\ProfileBundle\MVNerdsProfileBundle(),
            new MVNerds\CommentBundle\MVNerdsCommentBundle(),
			new MVNerds\PropelBundle\MVNerdsPropelBundle(),
            new MVNerds\VoteBundle\MVNerdsVoteBundle(),
            new MVNerds\ViewBundle\MVNerdsViewBundle(),
            new MVNerds\ReportBundle\MVNerdsReportBundle(),
            new MVNerds\VideoBundle\MVNerdsVideoBundle(),
            new MVNerds\NewsBundle\MVNerdsNewsBundle(),
            new MVNerds\LaunchSiteBundle\MVNerdsLaunchSiteBundle(),
            new MVNerds\ItemHandlerBundle\MVNerdsItemHandlerBundle(),
            new MVNerds\ChampionHandlerBundle\MVNerdsChampionHandlerBundle(),
            new MVNerds\TeamSeekerBundle\MVNerdsTeamSeekerBundle(),
            new MVNerds\SkeletonBundle\MVNerdsSkeletonBundle(),
		);

		if (in_array($this->getEnvironment(), array('dev', 'test')))
		{
			$bundles[] = new MVNerds\DataGrabberBundle\MVNerdsDataGrabberBundle();
			$bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
			$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
		}

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
	}

}
