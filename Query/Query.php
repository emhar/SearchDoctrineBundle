<?php

namespace Emhar\SearchDoctrineBundle\Query;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\Types\Type;
use Emhar\SearchDoctrineBundle\Mapping\ItemMetaData;
use Emhar\SearchDoctrineBundle\Request\Request;

/**
 * Query build sql and return result
 *
 * @author Emhar
 */
class Query
{

	/**
	 * @var string[]
	 */
	public $finalTypes;

	/**
	 * @var ResultSetMapping
	 */
	public $rsm;

	/**
	 * The database mapping
	 *
	 * <pre>
	 * array(
	 * 	array(
	 * 		'name'	=>	<stringl|alias of table (t1)>,
	 * 		'entityIdentifier'		=>	<string|table name (customer)>
	 * 		'hits' => 'array(
	 * 			<int|contructor position>	=>	<string|column (t1.name AS c1)>
	 * 		)
	 * 		'scoreMappings' => 'array(
	 * 			<int|index>	=>	<string|string converted column (CAST(t1.name AS CHAR) AS c1)>
	 * 		)
	 * 		'joins' => 'array(
	 * 			<int|index>	=>	<string|join clause (LEFT JOIN order j1 on t1.id=j1.customer_id)>
	 * 		)
	 * 	)
	 * )
	 * </pre>
	 *
	 * @var array
	 */
	public $databaseMapping = array();

	/**
	 * Score Position in selected columns
	 * @var int
	 */
	public $scorePos;

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
			'finalTypes',
			'rsm',
			'databaseMapping'
		);

		return $serialized;
	}

	/**
	 * Create query from array
	 *
	 * @param array $array
	 * @return \Emhar\SearchDoctrineBundle\Query\Query
	 */
	public static function __set_state(array $array)
	{
		$query = new Query();
		foreach($array as $attribute => $value)
		{
			$query->$attribute = $value;
		}
		return $query;
	}

	/**
	 * Get results for request
	 *
	 * @param EntityManager $em
	 * @param Request $request
	 * @param int $page
	 * @return array
	 */
	public function getResults(EntityManager $em, Request $request, $page)
	{
		$query = $this->buildResultQuery($em, $request, $page);
		return $query->getResult();
	}

	/**
	 * Get result count for request
	 *
	 * @param EntityManager $em
	 * @param Request $request
	 * @return int
	 */
	public function getCount(EntityManager $em, Request $request)
	{
		$stmt = $this->buildCountQuery($em, $request);
		$stmt->execute();
		return (int) $stmt->fetchColumn();
	}

	/**
	 * Build result query
	 *
	 * @param EntityManager $em
	 * @param Request $request
	 * @param int $page
	 * @return \Doctrine\ORM\NativeQuery
	 */
	protected function buildResultQuery(EntityManager $em, Request $request, $page)
	{
		$searchWords = preg_split('/[^[:alnum:]]+/', $request->getSearchText());
		$offset = $request->getLimit() * ($page - 1);
		$limit = $request->getLimit();

		$selects = array();
		foreach($this->databaseMapping as $tableMapping)
		{
			$tableMapping['columns'][$this->scorePos] = $this->buildScoreColumn($tableMapping['columns'], count($searchWords));
			//For constructor parameter order of searchitem
			ksort($tableMapping['columns']);
			$hitExpressions = $this->buildHitExpressions($tableMapping['columns']);
			$joinsExpressions = $this->buildJoinExpressions($tableMapping['joins']);


			$selects[] = 'SELECT * FROM (SELECT ' . implode($hitExpressions, ', ') . ' '
					. 'FROM ' . $tableMapping['table'] . ' ' . $tableMapping['tableAlias'] . ' '
					. implode(' ', $joinsExpressions) . ' '
					. 'ORDER BY c' . $this->scorePos . ' DESC '
					. 'LIMIT ' . ($offset + $limit) . ')'
					. 'WHERE c' . $this->scorePos . '<>0 '
			;
		}

		$bigSelect = '' . implode($selects, ' UNION ') . ' '
				. 'ORDER BY c' . $this->scorePos . ' DESC '
				. 'LIMIT ' . $limit . ' '
				. 'OFFSET ' . $offset
		;

		$query = $em->createNativeQuery($bigSelect, $this->rsm);
		foreach($searchWords as $key => $searchWord)
		{
			$query->setParameter('p' . $key, $searchWord);
		}

		return $query;
	}

	/**
	 * Build count query
	 *
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \Emhar\SearchDoctrineBundle\Request\Request $request
	 * @return \Doctrine\DBAL\Statement;
	 */
	protected function buildCountQuery(EntityManager $em, Request $request)
	{
		$searchWords = preg_split('/[^[:alnum:]]+/', $request->getSearchText());
		$selects = array();
		foreach($this->databaseMapping as $tableMapping)
		{
			$score = $this->buildScoreColumn($tableMapping['columns'], count($searchWords));
			$joinsExpressions = $this->buildJoinExpressions($tableMapping['joins']);

			$selects[] = 'SELECT COUNT(*) '
					. 'FROM ' . $tableMapping['table'] . ' ' . $tableMapping['tableAlias'] . ' '
					. implode(' ', $joinsExpressions) . ' '
					. 'WHERE ' . $score['expression'] . '<>0 '
			;
		}
		$bigSelect = 'SELECT (' . implode(')+(', $selects) . ')';
		$stmt = $em->getConnection()->prepare($bigSelect);
		foreach($searchWords as $key => $searchWord)
		{
			$stmt->bindValue('p' . $key, $searchWord);
		}
		return $stmt;
	}

	/**
	 * Build score column, with same structure as hit
	 *
	 * @param array $columns
	 * @param type $searchWordsCount
	 * @return array
	 */
	protected function buildScoreColumn(array $columns, $searchWordsCount)
	{
		$scoreExpression = $this->buildScore($columns, $searchWordsCount);
		return array(
			'expression' => $scoreExpression,
			'type' => Type::INTEGER
		);
	}

	/**
	 * Build score expression
	 *
	 * @param array $columns
	 * @param type $searchWordsCount
	 * @return string
	 */
	protected function buildScore(array $columns, $searchWordsCount)
	{
		$scores = array();
		foreach($columns as $column)
		{
			if($column['scoreFactor'] != 0 && isset($column['expression']))
			{
				$stringConvertedExpression = $this->convertToString($column['type'], $column['expression']);
				$score =  $column['scoreFactor'] . '*('
						. $searchWordsCount . '*LENGTH(IFNULL(' . $stringConvertedExpression . ', \'\'))';
				for($i = 0; $i < $searchWordsCount; $i++)
				{
					$score .= ' - LENGTH(REPLACE(LOWER(IFNULL(' . $stringConvertedExpression . ', \'\')), LOWER(:p' . $i . '), \'\'))';
				}
				$score .= ')';
				$scores[] = $score;
			}
		}
		return '' . implode($scores, '+') . '';
	}

	protected function buildHitExpressions(array $columns)
	{
		$hitExpressions = array();
		foreach($columns as $key => $column)
		{
			if(isset($column['expression']))
			{
				if($column['type'] == $this->finalTypes[$key])
				{
					$hitExpressions[] = $column['expression'] . ' AS c' . $key;
				}
				else
				{
					$hitExpressions[] = $this->convertToString($column['type'], $column['expression']) . ' AS c' . $key;
				}
			}
			else
			{
				$hitExpressions[] = 'NULL AS c' . $key;
			}
		}
		return $hitExpressions;
	}

	/**
	 * Build joins expression
	 *
	 * @param array $joins
	 * @return string
	 */
	protected function buildJoinExpressions(array $joins)
	{
		$joinExpressions = array();
		foreach($joins as $joins)
		{
			$joinExpressions[] = 'LEFT JOIN ' . $joins['table'] . ' ' . $joins['tableAlias']
					. ' ON ' . $joins['onClause'];
		}
		return $joinExpressions;
	}

	/**
	 * Return string string converted column
	 *
	 * @param string $type
	 * @param string $expression
	 * @return string
	 */
	protected function convertToString($type, $expression)
	{
		switch($type)
		{
			case Type::STRING:
			case Type::TEXT:
				return $expression;
			case Type::BIGINT:
			case Type::DECIMAL:
			case Type::FLOAT:
			case Type::INTEGER:
			case Type::SMALLINT:
			case Type::DATE:
			case Type::DATETIME:
			case Type::DATETIMETZ:
			case Type::TIME:
				return 'CAST(' . $expression . ' AS CHAR)';
			default :
				return '\'\'';
		}
	}

	/**
	 * Loads and complete databaseMapping
	 *
	 * @param array $databaseMapping
	 * @param string $itemClass
	 * @param array $hitPositions
	 */
	public function mapDatabase(array $databaseMapping, $itemClass, array $hitPositions)
	{
		$this->databaseMapping = $databaseMapping;
		$this->loadFinalType($hitPositions);
		$this->loadResultSetMappingAndScorePosition($itemClass, $hitPositions);
		$this->finalTypes[$this->scorePos] = Type::INTEGER;
	}

	/**
	 * Load final type from $this->databaseMapping.
	 */
	protected function loadFinalType()
	{
		foreach($this->databaseMapping as $entityMapping)
		{
			foreach($entityMapping['columns'] as $pos => $column)
			{
				if(!isset($this->finalTypes[$pos]))
				{
					if(isset($column['type']))
					{
						$this->finalTypes[$pos] = $column['type'];
					}
					else
					{
						$this->finalTypes[$pos] = null;
					}
				}
				elseif(isset($column['type']) && $this->finalTypes[$pos] != $column['type'])
				{
					$this->finalTypes[$pos] = Type::STRING;
				}
			}
		}
	}

	/**
	 * Loads the ResultSetMapping from $this->finalTypes
	 * and the score position
	 *
	 * @param string $itemClass
	 * @param array $hitPositions
	 */
	public function loadResultSetMappingAndScorePosition($itemClass, array $hitPositions)
	{
		$rsm = new ResultSetMapping();
		foreach($this->finalTypes as $pos => $finalType)
		{
			$rsm->addScalarResult('c' . $pos, 'c' . $pos, $finalType !== null ? $finalType : Type::STRING);
			$rsm->newObjectMappings['c' . $pos] = array(
				'className' => $itemClass,
				'objIndex' => 0,
				'argIndex' => $pos
			);
		}
		if(array_key_exists(ItemMetaData::SCORE, $hitPositions))
		{
			$this->scorePos = $hitPositions[ItemMetaData::SCORE];
			$rsm->addScalarResult('c' . $this->scorePos, 'c' . $this->scorePos, Type::INTEGER);
			$rsm->newObjectMappings['c' . $this->scorePos] = array(
				'className' => $itemClass,
				'objIndex' => 0,
				'argIndex' => $this->scorePos
			);
		}
		else
		{
			$this->scorePos = count($hitPositions);
		}
		$this->rsm = $rsm;
	}
}
