<?php

namespace Emhar\SearchDoctrineBundle\Tests\Mapping;

use PHPUnit_Framework_TestCase;
use Emhar\SearchDoctrineBundle\Mapping\ItemMetaDataFactory;
use Emhar\SearchDoctrineBundle\Tests\Models\Item\Item1;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PhpFileCache;

/**
 * ItemMetaDataFactory.
 *
 * @author Emhar
 */
class ItemMetaDataFactoryTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Test if factory cache itemMetaData
	 *
	 * @dataProvider getItemMetaDataProvider
	 */
	public function testGetItemMetaData($itemName, $cache)
	{
		$factory = new ItemMetaDataFactory($cache);
		$factory->getItemMetaData($itemName);

		$factory2 = new ItemMetaDataFactory($cache);
		$factory2->getItemMetaData($itemName);

		if($cache)
		{
			$this->assertFalse($factory2->isInitialized());
		}
	}

	public function getItemMetaDataProvider()
	{
		$dir1 = sys_get_temp_dir() . '/doctrine_cache_' . uniqid('1');
		$dir2 = sys_get_temp_dir() . '/doctrine_cache_' . uniqid('2');
		return array(
			array(Item1::getClass(), null),
			array(Item1::getClass(), new ArrayCache()),
			array(Item1::getClass(), new FilesystemCache($dir1)),
			array(Item1::getClass(), new PhpFileCache($dir2)),
		);
	}
}
