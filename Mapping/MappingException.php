<?php

namespace Emhar\SearchDoctrineBundle\Mapping;

/**
 * A MappingException indicates that something is wrong with the mapping setup.
 *
 * @author Emhar
 */
class MappingException extends \Exception
{

	/**
	 * Return MappingException with invalid type mapping message
	 *
	 * @param string $viewName
	 * @param string $ownerName
	 * @param string $attibuteName
	 * @param string $type
	 * @return \self
	 */
	public static function invalidMappingType($viewName, $ownerName, $attibuteName, $type)
	{
		return new self('The search mapping attribute "' . $attibuteName . '"'
				. ' of "' . $ownerName . '"'
				. ' is not a valid "' . $type . '"'
				. ' in view "' . $viewName . '".'
		);
	}

	/**
	 * Return MappingException with required mapping message
	 *
	 * @param string $viewName
	 * @param string $ownerName
	 * @param string $attibuteName
	 * @return \self
	 */
	public static function requiredMapping($viewName, $ownerName, $attibuteName)
	{
		return new self('The search mapping attribute "' . $attibuteName . '"'
				. (isset($ownerName) ? ' of "' . $ownerName . '"' : '')
				. ' is required in view "' . $viewName . '".'
		);
	}

	/**
	 * Return MappingException with invalid hit name message
	 *
	 * @param string $viewName
	 * @param string $hitName
	 * @return \self
	 */
	public function invalidHit($viewName, $hitName)
	{
		return new self('The search hit "' . $hitName . '"'
				. 'is not a parameter of "' . $viewName . '" constructor.');
	}

}
