<?php

namespace Emhar\SearchDoctrineBundle\Mapping;

use ReflectionMethod;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * A View instance holds all mapping between searchItem and entities
 * Once populated, View instances are usually cached in a serialized form.
 *
 * @author Emhar
 */
class View
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
	 * @string
	 */
	protected $viewName;

	/**
	 * @var int
	 */
	protected $scoreConstructPos;

	/**
	 * @var int
	 */
	protected $typeConstructPos;

	/**
	 * The hit definitions ordered like Item constructor
	 * 
	 * <pre>
	 * array(
	 * 		<string|hit name>	=>	array(
	 * 			'rankFactor'	=>	<int|Hit rank factor. Defaults to 1.>,
	 * 			'sortable'		=>	<boolean|Whether hit is searchable. Defaults to FALSE.>,
	 * 			'finalType'		=>	<string|If all attributes types are not similar string else attributes type>
	 * 			'mappings' => 'array(
	 * 				<string|Entity name>	=>	array(
	 * 					'attributeName'		=>	<string|Attribute name>,
	 * 					'type'		=>	<string|Attribute type. Must be one of Doctrine's mapping types.>
	 * 				)
	 * 			)
	 * 		)
	 * )
	 * </pre>
	 * 
	 * @var array
	 */
	protected $hitDefinitions = array();

	/**
	 * The database mapping
	 *
	 * <pre>
	 * array(
	 *	array(
	 * 		'name'	=>	<stringl|alias of table (t1)>,
	 * 		'entityName'		=>	<string|table name (customer)>
	 * 		'hits' => 'array(
	 * 			<int|contructor position>	=>	<string|column (t1.name AS c1)>
	 * 		)
	 *		'scoreMappings' => 'array(
	 * 			<int|index>	=>	<string|string converted column (CAST(t1.name AS CHAR) AS c1)>
	 * 		)
	 *		'joins' => 'array(
	 * 			<int|index>	=>	<string|join clause (LEFT JOIN order j1 on t1.id=j1.customer_id)>
	 * 		)
	 *	)
	 * )
	 * </pre>
	 *
	 * @var array
	 */
	protected $databaseMapping = array();

	/**
	 * @var ResultSetMapping
	 */
	protected $rsm;

	/**
	 * The prototype from which new instances of the mapped class are created.
	 *
	 * @var object
	 */
	private $_prototype;

	/**
	 * Initializes a new View instance that will hold the object-relational
	 * mapping of the class with the given name.
	 *
	 * @param string $viewName The name of the entity class the new instance is used for.
	 */
	public function __construct($viewName)
	{
		$this->viewName = $viewName;
		$contruct = new ReflectionMethod($viewName, '__construct');
		/* @var $param \ReflectionParameter */
		foreach ($contruct->getParameters() as $key => $param)
		{
			if ($param->getName() === self::SCORE)
			{
				$this->scoreConstructPos = $key;
			}
			elseif ($param->getName() == self::TYPE)
			{
				$this->typeConstructPos = $key;
			}
			else
			{
				$this->hitDefinitions[$param->getName()] = array(
					'constructPos' => $key,
					'isRequired' => !$param->isOptional()
				);
			}
		}
	}

	/**
	 * Creates a string representation of this instance.
	 *
	 * @return string The string representation of this instance.
	 * @todo Construct meaningful string representation.
	 */
	public function __toString()
	{
		return __CLASS__ . '@' . spl_object_hash($this);
	}

	/**
	 * Determines which attributes get serialized.
	 *
	 * @return array The names of all the attributes that should be serialized.
	 */
	public function __sleep()
	{
		// This metadata is always serialized/cached.
		$serialized = array(
			'databaseMapping',
			'rsm',
			'viewName',
			'scoreConstructPos',
			'typeConstructPos'
		);

		return $serialized;
	}

	/**
	 * Creates a new instance of the mapped class, without invoking the constructor.
	 *
	 * @return object
	 */
	public function newInstance()
	{
		if ($this->_prototype === null)
		{
			$this->_prototype = unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->viewName), $this->viewName));
		}

		return clone $this->_prototype;
	}

	/**
	 * Adds a mapped hit
	 *
	 * @param array $mapping The attribute mapping.
	 * @throws MappingException
	 * @return void
	 */
	public function mapHit($hitName, array &$hitDefinition)
	{
		$this->validateAndCompleteHitMapping($hitName, $hitDefinition);
		if (isset($this->hitDefinitions[$hitName]))
		{
			$this->hitDefinitions[$hitName] = array_merge($this->hitDefinitions[$hitName], $hitDefinition);
		}
		else
		{
			throw MappingException::invalidHit($this->viewName, $hitName);
		}
	}

	/**
	 * Validates & completes the given hit definition.
	 * $hitDefinition must have this form
	 * <pre>
	 * array(
	 * 		'rankFactor'	=>	<int, optional|Hit rank factor. Defaults to 1.>,
	 * 		'sortable'		=>	<boolean, optional|Whether hit is searchable. Defaults to FALSE.>
	 * 		'mappings' => 'array(
	 * 			<string|Entity name>	=>	array(
	 * 				'attributeName'		=>	<string|Attribute name>,
	 * 				'type'		=>	<string|Attribute type. Must be one of Doctrine's mapping types.>
	 * 			)
	 * 		)
	 * )
	 * </pre>
	 * 
	 * @param string $hitName the hit name
	 * @param array $hitDefinition  The attribute mapping to validated & complete.
	 * @throws MappingException
	 * @return array The validated and completed attribute mapping.
	 */
	protected function validateAndCompleteHitMapping($hitName, array &$hitDefinition)
	{
		$hitDefinition['isRequired'] = true;
		if (!isset($hitDefinition['rankFactor']))
		{
			$hitDefinition['rankFactor'] = 1;
		}
		elseif (!settype($hitDefinition['rankFactor'], 'integer'))
		{
			throw MappingException::invalidMappingType($this->viewName, $hitName, 'rankFactor', 'integer');
		}

		if (!isset($hitDefinition['sortable']))
		{
			$hitDefinition['sortable'] = false;
		}
		elseif (!settype($hitDefinition['sortable'], 'boolean'))
		{
			throw MappingException::invalidMappingType($this->viewName, $hitName, 'sortable', 'boolean');
		}

		foreach ($hitDefinition['mapping'] as $entityName => &$mapping)
		{
			// Check mandatory attributes
			if (!isset($mapping['attributeName']))
			{
				throw MappingException::requiredMapping($this->viewName, $hitName . "::mapping::" . $entityName, 'attributeName');
			}

			if (!isset($mapping['type']))
			{
				throw MappingException::requiredMapping($this->viewName, $hitName . "::mapping::" . $entityName, 'type');
			}

			//Calculate hit final type
			if (!isset($hitDefinition['finalType']))
			{
				$hitDefinition['finalType'] = $mapping['type'];
			}
			elseif ($hitDefinition['finalType'] !== $mapping['type'])
			{
				$hitDefinition['finalType'] = 'string';
			}
		}
	}

	/**
	 * Set database mapping
	 *
	 * <pre>
	 * array(
	 *	array(
	 * 		'name'	=>	<stringl|alias of table (t1)>,
	 * 		'entityName'		=>	<string|table name (customer)>
	 * 		'hits' => 'array(
	 * 			<int|contructor position>	=>	<string|column (t1.name AS c1)>
	 * 		)
	 *		'scoreMappings' => 'array(
	 * 			<int|index>	=>	<string|string converted column (CAST(t1.name AS CHAR) AS c1)>
	 * 		)
	 *		'joins' => 'array(
	 * 			<int|index>	=>	<string|join clause (LEFT JOIN order j1 on t1.id=j1.customer_id)>
	 * 		)
	 *	)
	 * )
	 * </pre>
	 *
	 * @param \Emhar\SearchDoctrineBundle\Mapping\ResultSetMapping $rsm
	 */
	public function setDatabaseMapping(array $databaseMapping)
	{
		$this->databaseMapping = $databaseMapping;
	}

	/**
	 * Set resultSetMapping
	 *
	 * @param ResultSetMapping $rsm
	 */
	public function setRsm(ResultSetMapping $rsm)
	{
		$this->rsm = $rsm;
	}

	/**
	 * Get entities names include in view
	 * 
	 * @return array
	 */
	public function getEntitiesNames()
	{
		$entityNames = array();
		foreach ($this->hitDefinitions as $hitDefinition)
		{
			$entityNames = array_merge($entityNames, array_keys($hitDefinition['mapping']));
		}
		return array_unique($entityNames);
	}

	/**
	 * Get hits names in same order than item construct
	 * 
	 * @return array
	 */
	public function getOrderedRequiredHitNames()
	{
		$hitNames = array();
		foreach ($this->hitDefinitions as $hitName => $hitDefinition)
		{
			if ($hitDefinition['isRequired'])
			{
				$hitNames[] = $hitName;
			}
		}
		return $hitNames;
	}

	/**
	 * Get view name
	 * @return string
	 */
	public function getViewName()
	{
		return $this->viewName;
	}

	/**
	 * Get hit definition
	 * <pre>
	 * array(
	 * 		'rankFactor'	=>	<int|Hit rank factor. Defaults to 1.>,
	 * 		'sortable'		=>	<boolean|Whether hit is searchable. Defaults to FALSE.>,
	 * 		'finalType'		=>	<string|If all attributes types are not similar string else attributes type>
	 * 		'mappings' => 'array(
	 * 			<string|Entity name>	=>	array(
	 * 				'attributeName'	=>	<string|Attribute name>,
	 * 				'type'		=>	<string|Attribute type. Must be one of Doctrine's mapping types.>
	 * 			)
	 * 		)
	 * )
	 * </pre>
	 * 
	 * @return array
	 */
	public function getHitDefinition($hitName)
	{
		return $this->hitDefinitions[$hitName];
	}

	/**
	 * Get score position in searchitem constructor
	 * return null if score isn't present in constructor
	 *
	 * @return int|null
	 */
	public function getScoreConstructPos()
	{
		return $this->scoreConstructPos;
	}

	/**
	 * Get type position in searchitem constructor
	 * return null if type isn't present in constructor
	 *
	 * @return int|null
	 */
	public function getTypeConstructPos()
	{
		return $this->typeConstructPos;
	}

	/**
	 * Get database mapping
	 *
	 * <pre>
	 * array(
	 *	array(
	 * 		'name'	=>	<stringl|alias of table (t1)>,
	 * 		'entityName'		=>	<string|table name (customer)>
	 * 		'hits' => 'array(
	 * 			<int|contructor position>	=>	<string|column (t1.name AS c1)>
	 * 		)
	 *		'scoreMappings' => 'array(
	 * 			<int|index>	=>	<string|string converted column (CAST(t1.name AS CHAR) AS c1)>
	 * 		)
	 *		'joins' => 'array(
	 * 			<int|index>	=>	<string|join clause (LEFT JOIN order j1 on t1.id=j1.customer_id)>
	 * 		)
	 *	)
	 * )
	 * </pre>
	 * 
	 * @return array
	 */
	public function getDatabaseMapping()
	{
		return $this->databaseMapping;
	}

	/**
	 * Get ResultSetMapping
	 *
	 * @return ResultSetMapping
	 */
	public function getRsm()
	{
		return $this->rsm;
	}

}
