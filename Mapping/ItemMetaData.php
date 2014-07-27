<?php

namespace Emhar\SearchDoctrineBundle\Mapping;

use ReflectionMethod;

/**
 * A ItemMetaData instance holds all mapping between searchItem and entities
 * Once populated, ItemMetaData instances are usually cached in a serialized form.
 *
 * @author Emhar
 */
class ItemMetaData
{

	/**
	 * Score attribute name in search item constructor
	 */
	const SCORE = 'score';

	/**
	 * Type attribute name in search item constructor
	 */
	const TYPE = 'type';

	/**
	 * Item class
	 *
	 * @var string
	 */
	public $itemClass;

	/**
	 * Entity Classes, indexed by entity identifier
	 *
	 * @var string[]
	 */
	public $entityClasses = array();

	/**
	 * Entity Labels, indexed by entity identifier
	 *
	 * @var string[]
	 */
	public $entityLabels = array();

	/**
	 * Hit position in item constructor, indexed by hit identifier
	 *
	 * @var int[]
	 */
	public $hitPositions = array();

	/**
	 * Hit Label, indexed by hit identifier
	 *
	 * @var string[]
	 */
	public $hitLabels;

	/**
	 * Required hit identifiers
	 *
	 * @var string[]
	 */
	public $requiredHits = array();

	/**
	 * Hit score factors, indexed by hit identifier
	 *
	 * @var int[]
	 */
	public $hitScoreFactors;

	/**
	 * Sortable hit identifiers
	 *
	 * @var string[]
	 */
	public $sortableHits = array();

	/**
	 * Mapping between entities and hits
	 * 
	 * <pre>
	 * array(
	 * 		<string|Entity identifier> => array(
	 * 			<string|hit identifier>	=>	<string|Entity attribute name>
	 * 		)
	 * )
	 * </pre>
	 * 
	 * @var array
	 */
	public $entityHitMapping;

	/**
	 * Initializes a new View instance that will hold the object-relational
	 * mapping of the class with the given name.
	 *
	 * @param string $itemClass The name of the item class the new instance is used for.
	 * return ItemMetaData
	 */
	public static function initFromItemClass($itemClass)
	{
		$itemMetaData = new ItemMetaData();
		$itemMetaData->setItemClass($itemClass);
		$contruct = new ReflectionMethod($itemClass, '__construct');
		/* @var $param \ReflectionParameter */
		foreach($contruct->getParameters() as $key => $param)
		{
			$itemMetaData->setHitPosition($param->getName(), $key);
			if(!$param->isOptional())
			{
				$itemMetaData->addRequiredHit($param->getName());
			}
		}
		return $itemMetaData;
	}

	/**
	 * Determines which attributes get serialized.
	 *
	 * @return string[] The names of all the attributes that should be serialized.
	 */
	public function __sleep()
	{
		// This metadata is always serialized/cached.
		$serialized = array(
			'itemClass',
			'entityClasses',
			'entityLabels',
			'hitPositions',
			'hitLabels',
			'requiredHits',
			'hitScoreFactors',
			'sortableHits',
			'entityHitMapping'
		);

		return $serialized;
	}

	/**
	 * Create ItemMetaData from array
	 *
	 * @param array $array
	 * @return \Emhar\SearchDoctrineBundle\Mapping\ItemMetaData
	 */
	public static function __set_state(array $array)
	{
		$itemMetaData = new ItemMetaData();
		foreach($array as $attribute => $value)
		{
			$itemMetaData->$attribute = $value;
		}
		return $itemMetaData;
	}

	/**
	 * Adds mapped hits
	 * <pre>
	 * array(
	 * 		<string|Entity Identifier> => array(
	 * 			'entityClass'	=>	<string|Entity Class>,
	 * 			'label'		=>	<string|Label>
	 * 		)
	 * )
	 * </pre>
	 *
	 * @param array $entityDefinitions
	 * @throws MappingException
	 */
	public function mapEntities(array $entityDefinitions)
	{
		foreach($entityDefinitions as $entityIdentifier => $entityDefinition)
		{
			$this->validateAndCompleteEntity($entityIdentifier, $entityDefinition);
		}
	}

	/**
	 * Validates & completes the given entity definition.
	 *
	 * @param string $entityIdentifier
	 * @param array $entityDefinition
	 * @throws MappingException
	 */
	protected function validateAndCompleteEntity($entityIdentifier, $entityDefinition)
	{
		if(!isset($entityDefinition['label']))
		{
			throw MappingException::requiredMapping($this->itemClass, 'label', $entityIdentifier);
		}
		elseif(!settype($entityDefinition['label'], 'string'))
		{
			throw MappingException::invalidMappingType($this->itemClass, 'label', 'string', $entityIdentifier);
		}
		else
		{
			$this->entityLabels[$entityIdentifier] = $entityDefinition['label'];
		}

		if(!isset($entityDefinition['entityClass']))
		{
			throw MappingException::requiredMapping($this->itemClass, 'entityClass', $entityIdentifier);
		}
		elseif(!settype($entityDefinition['entityClass'], 'string'))
		{
			throw MappingException::invalidMappingType($this->itemClass, 'entityClass', 'string', $entityIdentifier);
		}
		else
		{
			$this->entityClasses[$entityIdentifier] = $entityDefinition['entityClass'];
		}
	}

	/**
	 * Adds mapped hits
	 * <pre>
	 * array(
	 * 		<string|Hit identifier> => array(
	 * 			'scoreFactor'	=>	<int, optional|Hit score factor. Defaults to 1.>,
	 * 			'sortable'		=>	<boolean, optional|Whether hit is searchable. Defaults to FALSE.>,
	 *			'label'		=>	<string|Hit label.>,
	 * 			'mapping' => array(
	 * 				<string|Entity class name>	=>	<string|Attribute name>
	 * 			)
	 * 		)
	 * )
	 * </pre>
	 *
	 * @param array $hitDefinitions
	 * @throws MappingException
	 */
	public function mapHits(array $hitDefinitions)
	{
		foreach($hitDefinitions as $hitIdentifier => $hitDefinition)
		{
			if(array_key_exists($hitIdentifier, $this->hitPositions))
			{
				$this->validateAndCompleteHit($hitIdentifier, $hitDefinition);
			}
			else
			{
				throw MappingException::invalidHit($this->itemClass, $hitIdentifier);
			}
		}
	}

	/**
	 * Validates & completes the given hit definition.
	 *
	 * @param string $hitIdentifier the hit identifier
	 * @param array $hitDefinition  The attribute mapping to validated & complete.
	 * @throws MappingException
	 */
	protected function validateAndCompleteHit($hitIdentifier, array &$hitDefinition)
	{
		$this->addRequiredHit($hitIdentifier);

		if(!isset($hitDefinition['scoreFactor']))
		{
			$this->setHitScoreFactor($hitIdentifier, 1);
		}
		elseif(!settype($hitDefinition['scoreFactor'], 'integer'))
		{
			throw MappingException::invalidMappingType($this->itemClass, 'scoreFactor', 'integer', $hitIdentifier);
		}
		else
		{
			$this->setHitScoreFactor($hitIdentifier, $hitDefinition['scoreFactor']);
		}

		//Defaut non sortable, non added to $this->sortableHits
		if(isset($hitDefinition['sortable']) && !settype($hitDefinition['sortable'], 'boolean'))
		{
			throw MappingException::invalidMappingType($this->itemClass, 'sortable', 'boolean', $hitIdentifier);
		}
		elseif(isset($hitDefinition['sortable']) && $hitDefinition['sortable'] === true)
		{
			$this->addSortableHit($hitIdentifier);
		}

		if(!isset($hitDefinition['label']))
		{
			throw MappingException::requiredMapping($this->itemClass, 'label', $hitIdentifier);
		}
		elseif(!settype($hitDefinition['label'], 'string'))
		{
			throw MappingException::invalidMappingType($this->itemClass, 'label', 'string', $hitIdentifier);
		}
		else
		{
			$this->hitLabels[$hitIdentifier] = $hitDefinition['label'];
		}

		foreach($hitDefinition['mapping'] as $entityIdentifier => $attribute)
		{
			$this->setHitEntityAttribute($hitIdentifier, $entityIdentifier, $attribute);
		}
	}

	/**
	 * Get item class name
	 * 
	 * @return string
	 */
	public function getItemClass()
	{
		return $this->itemClass;
	}

	/**
	 * Set item class name
	 *
	 * @param string $itemClass
	 * @return ItemMetaData
	 */
	public function setItemClass($itemClass)
	{
		$this->itemClass = $itemClass;
	}

	/**
	 * Get entity identifiers
	 *
	 * @return string[]
	 */
	public function getEntityIdentifiers()
	{
		return array_keys($this->entityHitMapping);
	}

	/**
	 * Get entity class for $entityIdentifier
	 *
	 * @param string $entityIdentifier
	 * @return string
	 */
	public function getEntityClass($entityIdentifier)
	{
		return $this->entityClasses[$entityIdentifier];
	}

	/**
	 * Get entity label for $entityIdentifier
	 *
	 * @param string $entityIdentifier
	 * @return string
	 */
	public function getEntityLabel($entityIdentifier)
	{
		return $this->entityLabels[$entityIdentifier];
	}

	/**
	 * Get hit positions indexed by hitIdentifier
	 *
	 * @return int[]
	 */
	public function getHitPositions()
	{
		return $this->hitPositions;
	}

	/**
	 * Set hit position in item constructor
	 *
	 * @param string $hitIdentifier
	 * @param int $hitPosition
	 * @return ItemMetaData
	 */
	public function setHitPosition($hitIdentifier, $hitPosition)
	{
		$this->hitPositions[$hitIdentifier] = $hitPosition;
		return $this;
	}

	/**
	 * Get required hits names,
	 * ordered by item constructor positions
	 *
	 * @return string[]
	 */
	public function getOrderedRequiredHits()
	{
		$hitIdentifiers = array();
		foreach($this->requiredHits as $hitIdentifier)
		{
			$hitIdentifiers[$this->hitPositions[$hitIdentifier]] = $hitIdentifier;
		}
		krsort($hitIdentifiers);
		return $hitIdentifiers;
	}

	/**
	 * Add Required Hit
	 *
	 * @param type $hitIdentifier
	 * @return \Emhar\SearchDoctrineBundle\Mapping\ItemMetaData
	 */
	public function addRequiredHit($hitIdentifier)
	{
		if(!in_array($hitIdentifier, $this->requiredHits))
		{
			$this->requiredHits[] = $hitIdentifier;
		}
		return $this;
	}

	/**
	 * Get hit score factors
	 *
	 * @return int[]
	 */
	public function getHitScoreFactors()
	{
		return $this->hitScoreFactors;
	}

	/**
	 * Set hit score factor
	 *
	 * @param string $hitIdentifier
	 * @param int $hitScoreFactor
	 * @return ItemMetaData
	 */
	public function setHitScoreFactor($hitIdentifier, $hitScoreFactor)
	{
		$this->hitScoreFactors[$hitIdentifier] = $hitScoreFactor;
		return $this;
	}

	/**
	 * Get sortable hits names
	 *
	 * @return string[]
	 */
	public function getSortableHits()
	{
		return $this->sortableHits;
	}

	/**
	 * Add sortable hit.
	 *
	 * @param string $hitIdentifier
	 * @return ItemMetaData
	 */
	public function addSortableHit($hitIdentifier)
	{
		if(!in_array($hitIdentifier, $this->sortableHits))
		{
			$this->sortableHits[] = $hitIdentifier;
		}
		return $this;
	}

	/**
	 * Get hit entity attribute
	 * return null if there is no mapping for this hit
	 *
	 * @param string $hitIdentifier
	 * @param string $entityIdentifier
	 * @return string|null
	 */
	public function getHitEntityAttribute($hitIdentifier, $entityIdentifier)
	{
		return isset($this->entityHitMapping[$entityIdentifier][$hitIdentifier]) ?
				$this->entityHitMapping[$entityIdentifier][$hitIdentifier] : null;
	}

	/**
	 * Set hit entity attribute
	 *
	 * @param string $hitIdentifier
	 * @param string $entityIdentifier
	 * @param string $attribute
	 * @return ItemMetaData
	 */
	public function setHitEntityAttribute($hitIdentifier, $entityIdentifier, $attribute)
	{
		if(!array_key_exists($entityIdentifier, $this->entityClasses))
		{
			throw MappingException::invalidEntity($this->itemClass, $entityIdentifier);
		}
		$this->entityHitMapping[$entityIdentifier][$hitIdentifier] = $attribute;
		return $this;
	}
}
