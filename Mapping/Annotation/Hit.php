<?php

namespace Emhar\SearchDoctrineBundle\Mapping\Annotation;

/**
 * Hits is an annotation that describe mapping
 * between constructor argument and entity attributes
 *
 * @author Emhar
 * @Annotation
 */
class Hit
{

	/**
	 * @var string
	 */
	private $identifier;

	/**
	 * @var string
	 */
	private $label;

	/**
	 * @var string
	 */
	private $scoreFactor;

	/**
	 * @var boolean
	 */
	private $sortable;

	/**
	 * @var array
	 */
	private $mapping;

	/**
	 * Construct Hit annotation
	 *
	 * @param array $options
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $options)
	{
		foreach($options as $key => $value)
		{
			if(!property_exists($this, $key))
			{
				throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
			}

			$this->$key = $value;
		}
	}

	/**
	 * Get hit identifier
	 * 
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}

	/**
	 * Get hit label
	 *
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Get hit score factor
	 * 
	 * @return string
	 */
	public function getScoreFactor()
	{
		return $this->scoreFactor;
	}

	/**
	 * Return true if the hit is sortable
	 *
	 * @return string
	 */
	public function getSortable()
	{
		return $this->sortable;
	}

	/**
	 * Get hit mapping
	 *
	 * @return string
	 */
	public function getMapping()
	{
		return $this->mapping;
	}
}
