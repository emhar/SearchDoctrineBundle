<?php

namespace Emhar\SearchDoctrineBundle\Mapping\Driver;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Mapping\ClassMetadata;
use Emhar\SearchDoctrineBundle\Mapping\View;


/**
 * The ORMDriver load the View database mapping from doctrine ORM Mapping.
 *
 * @author Emhar
 */
class ORMDriver
{

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * Initializes a new ORMDriver 
	 *
	 * @param EntityManager  $em The EntityManager
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Loads the database mapping for the specified view
	 *
	 * @param \Emhar\SearchDoctrineBundle\Mapping\View $view
	 */
	public function loadDatabaseMapping(View &$view)
	{
		$joinKey = 0;
		$databaseMapping = array();
		foreach ($view->getEntitiesNames() as $entityKey => $entityName)
		{
			$entityMetaData = $this->em->getMetadataFactory()->getMetadataFor($entityName);
			$tableMapping = array();
			$tableMapping['name'] = $entityMetaData->getTableName() . ' t' . $entityKey;
			$tableMapping['entityName'] = $entityName;
			$tableMapping['hits'] = array();
			$tableMapping['scoreMappings'] = array();
			$tableMapping['joins'] = array();
			foreach ($view->getOrderedRequiredHitNames() as $hitKey => $hitName)
			{
				$hitDefinition = $view->getHitDefinition($hitName);
				if (isset($hitDefinition['mapping'][$entityName]))
				{
					$chainGetterResult = $this->chainGetters($entityMetaData, $hitDefinition['mapping'][$entityName]['attributeName'], $entityKey, $joinKey);
					$tableMapping['joins'] = array_merge($tableMapping['joins'], $chainGetterResult['joins']);
					$tableMapping['hits'][$hitDefinition['constructPos']] = $this->loadHit($chainGetterResult['tableColumn'], $entityName, $hitDefinition, $hitKey);
					if ($scoreMapping = $this->loadScore($chainGetterResult['tableColumn'], $entityName, $hitDefinition, $hitKey))
					{
						$tableMapping['scoreMappings'][] = $scoreMapping;
					}
				}
				else
				{
					$tableMapping['hits'][$hitDefinition['constructPos']] = 'NULL' . ' AS c' . $hitKey;
				}
			}
			$databaseMapping[] = $tableMapping;
		}
		$view->setDatabaseMapping($databaseMapping);
	}

	/**
	 * Return column (t1.name AS c1)
	 *
	 * @param string $tableColumn
	 * @param string $entityName
	 * @param array $hitDefinition
	 * @param int $hitKey
	 * @return string
	 */
	protected function loadHit($tableColumn, $entityName, array $hitDefinition, $hitKey)
	{
		if ($hitDefinition['mapping'][$entityName]['type'] == $hitDefinition['finalType'])
		{
			return $tableColumn . ' AS c' . $hitKey;
		}
		else
		{
			return $this->convertToString($hitDefinition['mapping'][$entityName]['type'], $tableColumn) . ' AS c' . $hitKey;
		}
	}

	/**
	 * Return string converted column (CAST(t1.name AS CHAR) AS c1)
	 *
	 * @param string $tableColumn
	 * @param string $entityName
	 * @param array $hitDefinition
	 * @return string
	 */
	protected function loadScore($tableColumn, $entityName, array $hitDefinition)
	{
		if (isset($hitDefinition['mapping'][$entityName]))
		{
			if ($hitDefinition['rankFactor'] !== 0)
			{
				return array(
					'string' => $this->convertToString($hitDefinition['mapping'][$entityName]['type'], $tableColumn),
					'rankFactor' => $hitDefinition['rankFactor']
				);
			}
		}
	}

	/**
	 * Return chained getter mapping
	 *
	 * <pre>
	 * array(
	 * 	'tableColumn' => <string|selected column table alias (j1)>
	 * 	'joins' => 'array(
	 * 		<int|index>	=>	<string|join clause (LEFT JOIN order j1 on t1.id=j1.customer_id)>
	 * 	)
	 * )
	 * </pre>
	 *
	 *
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $entityMetaData
	 * @param string $attributeName
	 * @param string $entityKey
	 * @param string $joinKey
	 * @return array
	 */
	protected function chainGetters(ClassMetadata $entityMetaData, $attributeName, $entityKey, &$joinKey)
	{
		$chainedGetters = explode('.', $attributeName);
		if (count($chainedGetters) == 1)
		{
			$columnName = $entityMetaData->getColumnName($attributeName);
			return array('tableColumn' => 't' . $entityKey . '.' . $columnName, 'joins' => array());
		}
		else
		{
			$joins = array();
			$previousTableAlias = 't' . $entityKey;
			$previousMetaData = $entityMetaData;
			for ($i = 0; $i < count($chainedGetters) - 1; $i++)
			{
				$associationMapping = $previousMetaData->getAssociationMapping($chainedGetters[$i]);
				$metaData = $this->em->getMetadataFactory()->getMetadataFor($associationMapping['targetEntity']);
				$previousJoinColumn = $associationMapping['joinColumns'][0]['name'];
				$joinColumn = $associationMapping['joinColumns'][0]['referencedColumnName'];
				$tableName = $metaData->getTableName();
				$tableAlias = ' j' . $joinKey;

				$joins[] = 'LEFT JOIN ' . $tableName . ' ' . $tableAlias
						. ' on ' . $previousTableAlias . '.' . $previousJoinColumn . "=" . $tableAlias . '.' . $joinColumn;

				$previousTableAlias = $tableAlias;
				$previousMetaData = $metaData;
				$joinKey++;
			}
			$columnName = $metaData->getColumnName($chainedGetters[count($chainedGetters) - 1]);
			return array('tableColumn' => $tableAlias . '.' . $columnName, 'joins' => $joins);
		}
	}

	/**
	 * Loads the ResultSetMapping for the specified view
	 *
	 * @param \Emhar\SearchDoctrineBundle\Mapping\View $view
	 */
	public function loadResultSetMapping(View &$view)
	{
		$rsm = new ResultSetMapping();
		$hitCount = count($view->getOrderedRequiredHitNames());
		$scoreConstructPos = $view->getScoreConstructPos() !== null ? $view->getScoreConstructPos() : $hitCount;
		$typeConstructPos = $view->getTypeConstructPos() !== null ? $view->getTypeConstructPos() : $hitCount + 1;
		foreach ($view->getOrderedRequiredHitNames() as $hitKey => $hitName)
		{
			$hitDefinition = $view->getHitDefinition($hitName);
			$rsm->addScalarResult('c' . $hitKey, $hitKey, $hitDefinition['finalType']);
			$rsm->newObjectMappings['c' . $hitKey] = array(
				'className' => $view->getViewName(),
				'objIndex' => 0,
				'argIndex' => $hitDefinition['constructPos']
			);
		}
		//$view->getScoreConstructPos() is null if score isn't in constructor, while $scoreConstructPos isn't null
		if ($view->getScoreConstructPos() !== null)
		{
			$rsm->addScalarResult('c' . $scoreConstructPos, $scoreConstructPos, Type::INTEGER);
			$rsm->newObjectMappings['c' . $scoreConstructPos] = array(
				'className' => $view->getViewName(),
				'objIndex' => 0,
				'argIndex' => $view->getScoreConstructPos()
			);
		}
		//$view->getTypeConstructPos() is null if score isn't in constructor, while $this->typeConstructPos isn't null
		if ($view->getTypeConstructPos() !== null)
		{
			$rsm->addScalarResult('c' . ($typeConstructPos), $typeConstructPos, Type::STRING);
			$rsm->newObjectMappings['c' . ($typeConstructPos)] = array(
				'className' => $view->getViewName(),
				'objIndex' => 0,
				'argIndex' => $view->getTypeConstructPos()
			);
		}
		$view->setRsm($rsm);
	}

	/**
	 * Return string string converted column
	 *
	 * @param string $type
	 * @param string $attribute
	 * @return string
	 */
	protected function convertToString($type, $attribute)
	{
		switch ($type)
		{
			case Type::STRING:
			case Type::TEXT:
				return $attribute;
			case Type::BIGINT:
			case Type::DECIMAL:
			case Type::FLOAT:
			case Type::INTEGER:
			case Type::SMALLINT:
				return 'CAST(' . $attribute . ' AS CHAR)';
			case Type::DATE:
				return 'DATE_FORMAT(' . $attribute . ', \'%Y %m %d\')';
			case Type::DATETIME:
				return 'DATE_FORMAT(' . $attribute . ', \'%Y %m %d - %H : %i\')';
			case Type::TIME:
				return 'DATE_FORMAT(' . $attribute . ', \'%H : %i\')';
			default :
				return "''";
		}
	}

}
