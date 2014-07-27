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
	 * @param string $itemClass
	 * @param string $attributeName
	 * @param string $type
	 * @param string $ownerName
	 * @return MappingException
	 */
	public static function invalidMappingType($itemClass, $attributeName, $type, $ownerName = null)
	{
		return new self('The mapping attribute "' . $attributeName
				. (isset($ownerName) ? ' of "' . $ownerName . '"' : '')
				. ' is not a valid "' . $type . '" in "' . $itemClass . '".'
		);
	}

	/**
	 * Return MappingException with required mapping message
	 *
	 * @param string $itemClass
	 * @param string $attributeName
	 * @param string $ownerName
	 * @return MappingException
	 */
	public static function requiredMapping($itemClass, $attributeName, $ownerName = null)
	{
		return new self('The mapping attribute "' . $attributeName . '"'
				. (isset($ownerName) ? ' of "' . $ownerName . '"' : '')
				. ' is required in "' . $itemClass . '".'
		);
	}

	/**
	 * Return MappingException with invalid hit identifier message
	 *
	 * @param string $itemClass
	 * @param string $hitIdentifier
	 * @return MappingException
	 */
	public static function invalidHit($itemClass, $hitIdentifier)
	{
		return new self('The hit identifier "' . $hitIdentifier . '" is not'
				. ' a parameter of "' . $itemClass . '" constructor.');
	}

	/**
	 * Return MappingException with invalid entity identifier message
	 *
	 * @param string $itemClass
	 * @param string $entityIdentifier
	 * @return MappingException
	 */
	public static function invalidEntity($itemClass, $entityIdentifier)
	{
		return new self('The entity identifier "' . $entityIdentifier
				. '" is not describe by an ItemEntity mapping in "' . $itemClass . '"');
	}
}
