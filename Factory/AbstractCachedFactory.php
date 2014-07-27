<?php

namespace Emhar\SearchDoctrineBundle\Factory;

use Doctrine\Common\Cache\Cache;

/**
 * AbstractCachedFactory.
 *
 * @author Emhar
 */
abstract class AbstractCachedFactory
{

	/**
	 * @var Cache|null
	 */
	private $cacheDriver;

	/**
	 * @var array
	 */
	private $bag = array();

	/**
	 * @var bool
	 */
	private $initialized = false;

	/**
	 * Constructor
	 *
	 * @param Cache|null $cache
	 */
	public function __construct(Cache &$cache = null)
	{
		$this->cacheDriver = $cache;
	}

	/**
	 * Salt used by specific Object Manager implementation.
	 *
	 * @var string
	 */
	abstract protected function getCacheSalt();

	/**
	 * Implement this method to load object construct by your factory
	 *
	 * @param mixed $datas
	 * @return mixed
	 */
	abstract protected function doLoad($datas);

	/**
	 * Override this method for factory lazy load
	 * Here, load members that are only required
	 * in load function
	 */
	protected function initialize()
	{

	}

	/**
	 * Call this method to get the object
	 *
	 * @param string $objectName
	 * @param mixed $datas
	 * @return mixed
	 */
	protected function get($objectName, $datas = null)
	{
		if(isset($this->bag[$objectName]))
		{
			return $this->bag[$objectName];
		}

		if($this->cacheDriver)
		{
			if(($cached = $this->cacheDriver->fetch($objectName . $this->cacheSalt)) !== false)
			{
				$this->bag[$objectName] = $cached;
			}
			else
			{
				$this->load($objectName, $datas);
				$this->cacheDriver->save(
						$objectName . $this->getCacheSalt(), $this->bag[$objectName]
				);
			}
		}
		else
		{
			$this->load($objectName, $datas);
		}

		return $this->bag[$objectName];
	}

	/**
	 * Initialise factory
	 * construct new object instance and store it in bag
	 *
	 * @param string $objectName
	 * @param mixed $datas
	 */
	private function load($objectName, $datas)
	{
		if(!$this->initialized)
		{
			$this->initialize();
			$this->initialized = true;
		}
		$this->bag[$objectName] = $this->doLoad(isset($datas) ? $datas : $objectName);
	}

	/**
	 * Return wheter CachedFactory is initialized
	 *
	 * @return boolean
	 */
	public function isInitialized()
	{
		return $this->initialized;
	}
}
