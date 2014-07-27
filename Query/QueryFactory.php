<?php

namespace Emhar\SearchDoctrineBundle\Query;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Emhar\SearchDoctrineBundle\Mapping\ItemMetaData;
use Emhar\SearchDoctrineBundle\Query\Driver\ORMDriver;
use Emhar\SearchDoctrineBundle\Factory\AbstractCachedFactory;

/**
 * The QueryFactory is used to create Query objects that contain all the
 * information which describes how a entity are mapped.
 *
 * @author Emhar
 */
class QueryFactory extends AbstractCachedFactory
{

	/**
	 * Salt used by specific Object Manager implementation.
	 *
	 * @var string
	 */
	protected $cacheSalt = '$SEARCHQUERY';

	/**
	 * @var EntityManager;
	 */
	protected $em;

	/**
	 * @var ORMDriver
	 */
	protected $ormDriver;

	/**
	 * Constructor
	 *
	 * @param EntityManager $em
	 * @param Cache|null $cache
	 */
	public function __construct(EntityManager $em, Cache &$cache = null)
	{
		parent::__construct($cache);
		$this->em = $em;
	}

	/**
	 * Get Query
	 *
	 * @param ItemMetaData $itemMetaData
	 * @return Query
	 */
	public function getQuery(ItemMetaData $itemMetaData)
	{
		return $this->get($itemMetaData->getItemClass(), $itemMetaData);
	}

	/**
	 * Initialise factory
	 */
	protected function initialize()
	{
		$this->ormDriver = new ORMDriver($this->em);
	}

	/**
	 * Loads Query for itemMetaData
	 *
	 * @param ItemMetaData $itemMetaData
	 */
	protected function doLoad($itemMetaData)
	{
		$query = $this->newQueryInstance($itemMetaData);
		$databaseMapping = $this->ormDriver->loadDatabaseMapping($itemMetaData);
		$query->mapDatabase($databaseMapping, $itemMetaData->getItemClass(), $itemMetaData->getHitPositions());
		return $query;
	}

	/**
	 * get new Query Instance
	 *
	 * @return Query
	 */
	protected function newQueryInstance()
	{
		return new Query();
	}

	protected function getCacheSalt()
	{
		return $this->cacheSalt;
	}
}
