<?php

namespace AdEntify\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AdEntifyCoreExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('foursquare.client_id', $config['foursquare']['client_id']);
        $container->setParameter('foursquare.client_secret', $config['foursquare']['client_secret']);

        if (!isset($config['amazon_s3']['base_url'])) {
            throw new \InvalidArgumentException('The option "acme_storage.amazon_s3.base_url" must be set.');
        }
        $container->setParameter('adentify_storage.amazon_s3.base_url', $config['amazon_s3']['base_url']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
