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
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),

            new FOS\RestBundle\FOSRestBundle(),
            new FOS\FacebookBundle\FOSFacebookBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new FOS\TwitterBundle\FOSTwitterBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),

            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),

            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),

            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),

            new AdEntify\UserBundle\AdEntifyUserBundle(),
            new AdEntify\CoreBundle\AdEntifyCoreBundle(),
            new AdEntify\CommonBundle\AdEntifyCommonBundle(),
            new AdEntify\InstagramBundle\AdEntifyInstagramBundle(),
            new AdEntify\FlickrBundle\AdEntifyFlickrBundle(),
            new AdEntify\EmbedJsBundle\AdEntifyEmbedJsBundle(),
            new AdEntify\BackofficeBundle\AdEntifyBackofficeBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new JMS\DebuggingBundle\JMSDebuggingBundle($this);
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    protected function getContainerBaseClass()
    {
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            return '\JMS\DebuggingBundle\DependencyInjection\TraceableContainer';
        }

        return parent::getContainerBaseClass();
    }
}
