<?php

namespace Emhar\SearchDoctrineBundle\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\Hit;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\ItemEntity;

/**
 * The AnnotationDriver load the ItemMetaData from docblock Hit annotations.
 *
 * @author Emhar
 */
class AnnotationDriver
{

	/**
	 * @var AnnotationReader
	 */
	protected $reader;

	/**
	 * Initializes a new AnnotationDriver that uses the given AnnotationReader for reading
	 * docblock annotations.
	 *
	 * @param AnnotationReader  $reader The AnnotationReader to use, duck-typed.
	 */
	public function __construct(AnnotationReader $reader)
	{
		$this->reader = $reader;
	}

	/**
	 * Return the hit definitions for $itemClass
	 * <pre>
	 * array(
	 * 		<string|Entity Identifier> => array(
	 * 			'entityClass'	=>	<string|Entity Class>,
	 * 			'label'		=>	<string|Label>
	 * 		)
	 * )
	 * </pre>
	 *
	 * @param string $itemClass
	 * @return array
	 */
	public function loadEntityDefinitions($itemClass)
	{
		$entities = array();
		$annotations = $this->reader->getClassAnnotations(new \ReflectionClass($itemClass));
		foreach($annotations as $annotation)
		{
			if($annotation instanceof ItemEntity)
			{
				/* @var $annotation \Emhar\SearchDoctrineBundle\Mapping\Annotation\ItemEntity */
				$entity = array();
				$entity['entityClass'] = $annotation->getEntityClass();
				$entity['label'] = $annotation->getLabel();
				$entities[$annotation->getIdentifier()] = $entity;
			}
		}
		return $entities;
	}

	/**
	 * Return the hit definitions for $itemClass
	 * <pre>
	 * array(
	 * 		<string|Hit identifier> => array(
	 * 			'scoreFactor'	=>	<int, optional|Hit score factor. Defaults to 1.>,
	 * 			'sortable'		=>	<boolean, optional|Whether hit is searchable. Defaults to FALSE.>,
	 * 			'label'		=>	<string|Label>,
	 * 			'mapping' => array(
	 * 				<string|Entity Identifier>	=>	<string|Attribute name>
	 * 			)
	 * 		)
	 * )
	 * </pre>
	 *
	 * @param string $itemClass
	 * @return array
	 */
	public function loadHitDefinitions($itemClass)
	{
		$hits = array();
		$annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($itemClass, '__construct'));
		foreach($annotations as $annotation)
		{
			if($annotation instanceof Hit)
			{
				/* @var $annotation \Emhar\SearchDoctrineBundle\Mapping\Annotation\Hit */
				$hit = array();
				$hit['scoreFactor'] = $annotation->getScoreFactor();
				$hit['sortable'] = $annotation->getSortable();
				$hit['mapping'] = $annotation->getMapping();
				$hit['label'] = $annotation->getLabel();
				$hits[$annotation->getIdentifier()] = $hit;
			}
		}
		return $hits;
	}
}
