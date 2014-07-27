<?php

namespace Emhar\SearchDoctrineBundle\Tests\Models\Item;

use Emhar\SearchDoctrineBundle\Item\AbstractItem;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\Hit;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\ItemEntity;

/**
 * Item1
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
class Item1 extends AbstractItem
{

	protected $id;
	protected $score;
	protected $type;

	/**
	 * Construct Ressource
	 *
	 * @Hit(identifier="id", scoreFactor=2, sortable=true, label="ID", mapping={
	 * 	"entity1"="id",
	 * 	"entity2"="id"
	 * })
	 */
	public function __construct($score, $type, $id)
	{
		$this->score = $score;
		$this->type = $type;
		$this->id = $id;
	}
}
