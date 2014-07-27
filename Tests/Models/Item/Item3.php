<?php

namespace Emhar\SearchDoctrineBundle\Tests\Models\Item;

use Emhar\SearchDoctrineBundle\Item\AbstractItem;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\Hit;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\ItemEntity;

/**
 * Item3
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
class Item3 extends AbstractItem
{

	public $unbinded;
	public $id;
	public $string;
	public $type;
	public $score;
	public $integer;
	public $integerConverted;
	public $smallint;
	public $smallintConverted;
	public $bigint;
	public $bigintConverted;
	public $boolean;
	public $booleanConverted;
	public $float;
	public $floatConverted;
	public $decimal;
	public $decimalConverted;
	public $date;
	public $dateConverted;
	public $time;
	public $timeConverted;
	public $datetime;
	public $datetimeConverted;
	public $datetimetz;
	public $datetimetzConverted;
	public $text;
	public $join;

	/**
	 * Construct Ressource
	 *
	 * @Hit(identifier="id", scoreFactor=1, sortable=true, label="1", mapping={
	 * 	"entity1"="id",
	 * 	"entity2"="id"
	 * })
	 * @Hit(identifier="string", scoreFactor=1, sortable=true, label="2", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="string"
	 * })
	 * @Hit(identifier="integer", scoreFactor=0, sortable=true, label="3", mapping={
	 * 	"entity2"="integer"
	 * })
	 * @Hit(identifier="integerConverted", scoreFactor=0, sortable=true, label="4", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="integer"
	 * })
	 * @Hit(identifier="smallint", scoreFactor=0, sortable=true, label="5", mapping={
	 * 	"entity2"="smallint"
	 * })
	 * @Hit(identifier="smallintConverted", scoreFactor=0, sortable=true, label="6", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="smallint"
	 * })
	 * @Hit(identifier="bigint", scoreFactor=0, sortable=true, label="7", mapping={
	 * 	"entity2"="bigint"
	 * })
	 * @Hit(identifier="bigintConverted", scoreFactor=0, sortable=true, label="8", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="bigint"
	 * })
	 * @Hit(identifier="boolean", scoreFactor=0, sortable=true, label="9", mapping={
	 * 	"entity2"="boolean"
	 * })
	 * @Hit(identifier="booleanConverted", scoreFactor=0, sortable=true, label="10", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="boolean"
	 * })
	 * @Hit(identifier="float", scoreFactor=0, sortable=true, label="11", mapping={
	 * 	"entity2"="float"
	 * })
	 * @Hit(identifier="floatConverted", scoreFactor=0, sortable=true, label="12", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="float"
	 * })
	 * @Hit(identifier="decimal", scoreFactor=0, sortable=true, label="13", mapping={
	 * 	"entity2"="decimal"
	 * })
	 * @Hit(identifier="decimalConverted", scoreFactor=0, sortable=true, label="14", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="decimal"
	 * })
	 * @Hit(identifier="text", scoreFactor=0, sortable=true, label="15", mapping={
	 * 	"entity2"="text"
	 * })
	 * @Hit(identifier="dateConverted", scoreFactor=0, sortable=true, label="16", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="date"
	 * })
	 * @Hit(identifier="timeConverted", scoreFactor=0, sortable=true, label="17", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="time"
	 * })
	 * @Hit(identifier="datetimeConverted", scoreFactor=0, sortable=true, label="18", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="datetime"
	 * })
	 * @Hit(identifier="datetimetzConverted", scoreFactor=0, sortable=true, label="19", mapping={
	 * 	"entity1"="string",
	 * 	"entity2"="datetimetz"
	 * })
	 * @Hit(identifier="datetime", scoreFactor=0, sortable=true, label="20", mapping={
	 * 	"entity2"="datetime"
	 * })
	 * @Hit(identifier="datetimetz", scoreFactor=0, sortable=true, label="21", mapping={
	 * 	"entity2"="datetimetz"
	 * })
	 * @Hit(identifier="time", scoreFactor=0, sortable=true, label="22", mapping={
	 * 	"entity2"="time"
	 * })
	 * @Hit(identifier="date", scoreFactor=0, sortable=true, label="23", mapping={
	 * 	"entity2"="date"
	 * })
	 * @Hit(identifier="join", scoreFactor=0, sortable=true, label="24", mapping={
	 * 	"entity1"="entity_2.id",
	 * 	"entity2"="entity_1.entity_2.id"
	 * })
	 */
	public function __construct($unbinded, $score, $id, $string, $type, $integer, $integerConverted, $smallint,
			$smallintConverted, $bigint, $bigintConverted, $boolean, $booleanConverted, $float, $floatConverted, $decimal,
			$decimalConverted, $date, $dateConverted, $time, $timeConverted, $datetime, $datetimeConverted, $datetimetz,
			$datetimetzConverted, $text, $join)
	{
		$this->unbinded = $unbinded;
		$this->id = $id;
		$this->string = $string;
		$this->type = $type;
		$this->score = $score;
		$this->integer = $integer;
		$this->integerConverted = $integerConverted;
		$this->smallint = $smallint;
		$this->smallintConverted = $smallintConverted;
		$this->bigint = $bigint;
		$this->bigintConverted = $bigintConverted;
		$this->boolean = $boolean;
		$this->booleanConverted = $booleanConverted;
		$this->float = $float;
		$this->floatConverted = $floatConverted;
		$this->decimal = $decimal;
		$this->decimalConverted = $decimalConverted;
		$this->date = $date;
		$this->dateConverted = $dateConverted;
		$this->time = $time;
		$this->timeConverted = $timeConverted;
		$this->datetime = $datetime;
		$this->datetimeConverted = $datetimeConverted;
		$this->datetimetz = $datetimetz;
		$this->datetimetzConverted = $datetimetzConverted;
		$this->text = $text;
		$this->join = $join;
	}
}
