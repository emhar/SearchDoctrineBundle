<?php

namespace Emhar\SearchDoctrineBundle\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\Cache;
use Emhar\SearchDoctrineBundle\Mapping\Driver\AnnotationDriver;
use Emhar\SearchDoctrineBundle\Mapping\Driver\ORMDriver;
use Doctrine\ORM\EntityManager;

/**
 * The ViewFactory is used to create View objects that contain all the
 * view information which describes how a class should be mapped.
 *
 * @author Emhar
 */
class ViewFactory
{

	/**
	 * Salt used by specific Object Manager implementation.
	 *
	 * @var string
	 */
	protected $cacheSalt = '$SEARCHVIEW';

	/**
	 * @var Cache|null
	 */
	private $cacheDriver;

	/**
	 * @var array
	 */
	private $loadedView = array();

	/**
	 * @var bool
	 */
	protected $initialized = false;

	/**
	 * @var AnnotationDriver
	 */
	protected $annotationDriver;

	/**
	 * @var ORMDriver
	 */
	protected $ormDriver;

	/**
	 * Constructor
	 *
	 * @param Cache $cache
	 * @param EntityManager $em
	 */
	public function __construct(Cache $cache, EntityManager $em)
	{
		$this->cacheDriver = $cache;
		$this->em = $em;
	}

	/**
	 *
	 * @param
	 */
	protected function initialize()
	{
		$this->annotationDriver = new AnnotationDriver(new AnnotationReader(), array());
		$this->ormDriver = new ORMDriver($this->em);
		$this->initialized = true;
	}

	/**
	 * Gets the class view descriptor for a class.
	 *
	 * @param string $viewName
	 *
	 * @return View
	 */
	public function getView($viewName)
	{
		if (isset($this->loadedView[$viewName]))
		{
			return $this->loadedView[$viewName];
		}

		if ($this->cacheDriver)
		{
			if (($cached = $this->cacheDriver->fetch($viewName . $this->cacheSalt)) !== false)
			{
				$this->loadedView[$viewName] = $cached;
			}
			else
			{
				$this->loadView($viewName);
				$this->cacheDriver->save(
						$viewName . $this->cacheSalt, $this->loadedView[$viewName], null
				);
			}
		}
		else
		{
			$this->loadView($viewName);
		}

		return $this->loadedView[$viewName];
	}

	/**
	 * Loads the view of the class in question
	 *
	 * @param string $viewName The name of the class for which the view should get loaded.
	 */
	protected function loadView($viewName)
	{
		if (!$this->initialized)
		{
			$this->initialize();
		}

		$view = $this->newViewInstance($viewName);
		$this->annotationDriver->loadView($view);
		$this->ormDriver->loadDatabaseMapping($view);
		$this->ormDriver->loadResultSetMapping($view);
		$this->loadedView[$viewName] = $view;
	}

	/**
	 * get new View Instance
	 *
	 * @param string $viewName
	 * @return View
	 */
	protected function newViewInstance($viewName)
	{
		return new View($viewName);
	}

}
