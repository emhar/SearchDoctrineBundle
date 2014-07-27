<?php

namespace Emhar\SearchDoctrineBundle\Tests\Models\Item;

use Emhar\SearchDoctrineBundle\Item\AbstractItem;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\Hit;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\ItemEntity;

/**
 * Item2
 * 
 * @author harleaux
 *
 * @ItemEntity(
 *	identifier="entity1",
 *  label="Entity 1",
 *  entityClass="Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity1"
 * )
 * @ItemEntity(
 *	identifier="entity2",
 *  label="Entity 2",
 *  entityClass="Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2"
 * )
 */
class Item2 extends AbstractItem
{

	protected $id;
	protected $param;

	/**
	 * Construct Ressource
	 *
	 * @Hit(identifier="id", scoreFactor=2, sortable=true, label="ID", mapping={
	 * 	"entity1"="id",
	 * 	"entity2"="id"
	 * })
	 * @Hit(identifier="param", label="Param", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="integer"
	 * })
	 */
	public function __construct($id, $param = 0)
	{
		$this->id = $id;
		$this->param = $param;
	}

}
