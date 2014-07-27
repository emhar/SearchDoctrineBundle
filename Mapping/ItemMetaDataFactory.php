<?php

namespace Emhar\SearchDoctrineBundle\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Emhar\SearchDoctrineBundle\Mapping\Driver\AnnotationDriver;
use Emhar\SearchDoctrineBundle\Factory\AbstractCachedFactory;

/**
 * The ItemMetaDataFactory is used to create ItemMetaData objects that contain all the
 * item information which describes how a class should be mapped.
 *
 * @author Emhar
 */
class ItemMetaDataFactory extends AbstractCachedFactory
{

	/**
	 * Salt used by specific Object Manager implementation.
	 *
	 * @var string
	 */
	protected $cacheSalt = '$SEARCHITEMMETADATA';

	/**
	 * @var AnnotationDriver
	 */
	protected $annotationDriver;

	/**
	 * Initialise factory
	 */
	protected function initialize()
	{
		$this->annotationDriver = new AnnotationDriver(new AnnotationReader());
	}

	/**
	 *
	 * @param string $itemName
	 * @return ItemMetaData
	 */
	public function getItemMetaData($itemName)
	{
		return $this->get($itemName);
	}

	/**
	 * Loads the itemMetaData of the item class
	 *
	 * @param string $itemClass The name of the item class for which the itemMetaData should get loaded.
	 * @param null $datas unused
	 * @return ItemMetaData
	 */
	protected function doLoad($itemClass)
	{
		$itemMetaData = $this->newItemMetaDataInstance($itemClass);
		$entityDefinitions = $this->annotationDriver->loadEntityDefinitions($itemClass);
		$itemMetaData->mapEntities($entityDefinitions);
		$hitDefinitions = $this->annotationDriver->loadHitDefinitions($itemClass);
		$itemMetaData->mapHits($hitDefinitions);
		return $itemMetaData;
	}

	/**
	 * Get new ItemMetaData Instance
	 *
	 * @param string $itemClass
	 * @return ItemMetaData
	 */
	protected function newItemMetaDataInstance($itemClass)
	{
		return ItemMetaData::initFromItemClass($itemClass);
	}

	protected function getCacheSalt()
	{
		return $this->cacheSalt;
	}
}
