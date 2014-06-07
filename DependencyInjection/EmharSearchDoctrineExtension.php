<?php

namespace Emhar\SearchDoctrineBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * EmharSearchDoctrineExtension.
 *
 * @author Emhar
 */
class EmharSearchDoctrineExtension extends Extension
{

	/**
	 * Loads service for EmharSearchDoctrineBundle.
	 *
	 * @param array $configs
	 * @param ContainerBuilder $container
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$loader = new YamlFileLoader(
				$container, new FileLocator(__DIR__ . '/../Resources/config')
		);
		$loader->load('services.yml');
	}
}
