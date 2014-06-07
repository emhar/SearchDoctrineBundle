<?php

namespace Emhar\SearchDoctrineBundle\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Emhar\SearchDoctrineBundle\Mapping\View;
use Emhar\SearchDoctrineBundle\Mapping\MappingException;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\Hit;

/**
 * The AnnotationDriver load the View mapping from docblock Hit annotations.
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
	 * Loads the mapping for the specified view
	 *
	 * @param View $viewName
	 * @throws MappingException
	 */
	public function loadView(View &$view)
	{
		$hits = $this->loadHits($view->getViewName());
		foreach ($hits as $hitName => $hitDefinition)
		{
			$view->mapHit($hitName, $hitDefinition);
		}
	}

	/**
	 * Return the hit definitions hits
	 * merged with parent class for $viewName
	 *
	 * @param string $viewName
	 * @return array
	 */
	protected function loadHits($viewName)
	{
		$hits = array();
		$annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($viewName, '__construct'));
		foreach ($annotations as $annotation)
		{
			if ($annotation instanceof Hit)
			{
				/* @var $annotation \Emhar\SearchDoctrineBundle\Mapping\Annotation\Hit */
				$newHit = array();
				$newHit['rankFactor'] = $annotation->getRankFactor();
				$newHit['sortable'] = $annotation->getSortable();
				$newHit['mapping'] = $annotation->getMapping();
				if (!isset($hits[$annotation->getName()]))
				{
					$hits[$annotation->getName()] = $newHit;
				}
				else
				{
					$hits = array_replace_recursive($hits[$annotation->getName()], $newHit);
				}
			}
		}
		return $hits;
	}

}
