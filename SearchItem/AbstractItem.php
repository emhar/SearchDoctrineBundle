<?php

namespace Emhar\SearchDoctrineBundle\SearchItem;

/**
 * Abstract Search View, all view should extend this class
 *
 * @author emhar
 */
abstract class AbstractItem
{

	/**
	 * Name of entity class which provide this item
	 *
	 * @var string
	 */
	private $entityName;

	/**
	 * Search score of this result
	 *
	 * @var int
	 */
	private $score;

	/**
	 * Construct AbstractItem
	 *
	 * @param string $entityName
	 * @param int $score
	 */
	public function __construct($entityName, $score)
	{
		$this->entityName = $entityName;
		$this->score = $score;
	}

	/**
	 * Get name of entity class which provide this item
	 *
	 * @return string
	 */
	final public function getEntityName()
	{
		return $this->entityName;
	}

	/**
	 * Get search score of this result
	 *
	 * @return int
	 */
	final public function getScore()
	{
		return $this->score;
	}

	final public static function getViewName()
	{
		return get_called_class();
	}

}
