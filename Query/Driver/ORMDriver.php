<?php

namespace Emhar\SearchDoctrineBundle\Query\Driver;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Types\Type;
use Emhar\SearchDoctrineBundle\Mapping\ItemMetaData;

/**
 * The ORMDriver load the ItemMetaData database mapping from doctrine ORM Mapping.
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
	 * Loads the database mapping for the specified item
	 *
	 * <pre>
	 * array(
	 * 		<string|Entity identifier> => array(
	 * 			'table'		=>	<string|Table name>,
	 * 			'Alias'		=>	<string|Table alias>,
	 * 			'columns'	=> array(
	 * 				<int|hit constructor position> => array(
	 * 					'expression'	=>	<string|Attribute name>,
	 * 					'type'			=>	<string|Attribute type>,
	 * 					'scoreFactor'	=>	<int|Score factor>
	 * 				)
	 * 			),
	 * 			'joins'		=> array(
	 * 				array(
	 * 					'table'			=>	<string|Table name>,
	 * 					'tableAlias'	=>	<string|Table alias>,
	 * 					'onClause'		=>	<string|ON clause>
	 * 				)
	 * 			)
	 * 		)
	 * )
	 * </pre>
	 *
	 * @param ItemMetaData $itemMetaData
	 */
	public function loadDatabaseMapping(ItemMetaData $itemMetaData)
	{
		$databaseMapping = array();
		foreach($itemMetaData->getEntityIdentifiers() as $entityKey => $entityIdentifier)
		{
			$tableAlias = 't' . $entityKey;
			$databaseMapping[$entityIdentifier] = $this->loadEntity($entityIdentifier, $tableAlias, $itemMetaData);
		}
		return $databaseMapping;
	}

	/**
	 * Loads the database mapping for the specified entity
	 *
	 * @param ItemMetaData $itemMetaData
	 */
	protected function loadEntity($entityIdentifier, $tableAlias, $itemMetaData)
	{
		//For FROM clause
		$entityMetaData = $this->em->getMetadataFactory()
				->getMetadataFor($itemMetaData->getEntityClass($entityIdentifier));
		$table = $entityMetaData->getTableName();

		//Joins and selected columns
		$joins = array();
		$columns = array();
		foreach($itemMetaData->getOrderedRequiredHits() as $hitPos => $hitIdentifier)
		{
			if($hitIdentifier == ItemMetaData::TYPE)
			{
				$columns[$hitPos] = array(
					'expression' => '\'' . $entityIdentifier . '\'',
					'type' => Type::STRING,
					'scoreFactor' => 0
				);
			}
			//Score is added in query with search request
			elseif($hitIdentifier != ItemMetaData::SCORE)
			{
				$attribute = $itemMetaData->getHitEntityAttribute($hitIdentifier, $entityIdentifier);
				$columns[$hitPos] = array();
				$this->loadEntityHit($columns[$hitPos], $joins, $tableAlias, $itemMetaData->getEntityClass($entityIdentifier),
						$attribute);
				$hitScoreFactors = $itemMetaData->getHitScoreFactors();
				$columns[$hitPos]['scoreFactor'] = isset($hitScoreFactors[$hitIdentifier]) ?
						$hitScoreFactors[$hitIdentifier] : 0;
			}
		}
		return array(
			'table' => $table,
			'tableAlias' => $tableAlias,
			'joins' => $joins,
			'columns' => $columns
		);
	}

	protected function loadEntityHit(array &$column, array &$joins, $tableAlias, $entityClass, $chainedAttributesString)
	{
		$entityMetaData = $this->em->getMetadataFactory()->getMetadataFor($entityClass);
		$chainedAttributes = explode('.', $chainedAttributesString);
		$partialAttributesString = '';
		for($i = 0; $i < count($chainedAttributes) - 1; $i++)
		{
			$associationMapping = $entityMetaData->getAssociationMapping($chainedAttributes[$i]);
			$nextEntityMetaData = $this->em->getMetadataFactory()->getMetadataFor($associationMapping['targetEntity']);
			$partialAttributesString .= $chainedAttributes[$i];
			if(!isset($joins[$partialAttributesString]))
			{
				$joinColumn = $associationMapping['joinColumns'][0]['name'];
				$nextJoinColumn = $associationMapping['joinColumns'][0]['referencedColumnName'];
				$nextTableName = $nextEntityMetaData->getTableName();
				$nextTableAlias = 'j' . count($joins);
				$joins[$partialAttributesString] = array(
					'table' => $nextTableName,
					'tableAlias' => $nextTableAlias,
					'onClause' => $tableAlias . '.' . $joinColumn . '=' . $nextTableAlias . '.' . $nextJoinColumn
				);
			}
			else
			{
				$nextTableAlias = $joins[$partialAttributesString]['tableAlias'];
			}
			$tableAlias = $nextTableAlias;
			$entityMetaData = $nextEntityMetaData;
		}
		$columnName = $entityMetaData->getColumnName($chainedAttributes[$i]);
		if(!empty($columnName))
		{
			$column['expression'] = $tableAlias . '.' . $columnName;
			$column['type'] = $entityMetaData->getTypeOfField($chainedAttributes[$i]);
		}
	}
}
