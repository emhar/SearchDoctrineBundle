<?php

namespace Emhar\SearchDoctrineBundle\Mapping\Annotation;

/**
 * SubItem is an annotation that link Item and entity
 *
 * @author Emhar
 * @Annotation
 */
class ItemEntity
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
	private $entityClass;

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
	 * Get hit label
	 *
	 * @return string
	 */
	public function getEntityClass()
	{
		return $this->entityClass;
	}
}
