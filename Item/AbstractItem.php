<?php

namespace Emhar\SearchDoctrineBundle\Item;

/**
 * Abstract Item, all searchable item should extend this class
 *
 * @author emhar
 */
abstract class AbstractItem
{

	/**
	 * return Item class name
	 *
	 * @return string
	 */
	public static function getClass()
	{
		return get_called_class();
	}
}
