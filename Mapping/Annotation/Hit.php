<?php

namespace Emhar\SearchDoctrineBundle\Mapping\Annotation;

/**
 * Hit is an annotation that describe mapping
 * between constructor argurment and entity attributes
 *
 * @author Emhar
 * @Annotation
 */
class Hit
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $rankFactor;

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
		if (isset($options['value']))
		{
			$this->name = $options['value'];
		}

		foreach ($options as $key => $value)
		{
			if (!property_exists($this, $key))
			{
				throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
			}

			$this->$key = $value;
		}
	}

	/**
	 * Get hit name
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get hit rank factor
	 * 
	 * @return string
	 */
	public function getRankFactor()
	{
		return $this->rankFactor;
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
