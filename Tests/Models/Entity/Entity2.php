<?php

namespace Emhar\SearchDoctrineBundle\Tests\Models\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity2.
 *
 * @author emhar
 * @ORM\Entity
 * @ORM\Table(name="entity2")
 */
class Entity2
{

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="string", type="string")
	 */
	protected $string;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection|Entity[]
	 * @ORM\OneToMany(targetEntity="Entity1", mappedBy="entity2")
	 */
	protected $entities;

	/**
	 * @var Entity1
	 * @ORM\OneToOne(targetEntity="Entity1")
	 * @ORM\JoinColumn(name="entity_1_id", referencedColumnName="id")
	 */
	protected $entity_1;

	/**
	 * @var int
	 * @ORM\Column(name="integer", type="integer")
	 */
	protected $integer;

	/**
	 * @var int
	 * @ORM\Column(name="smallint", type="smallint")
	 */
	protected $smallint;

	/**
	 * @var int
	 * @ORM\Column(name="bigint", type="bigint")
	 */
	protected $bigint;

	/**
	 * @var boolean
	 * @ORM\Column(name="boolean", type="boolean")
	 */
	protected $boolean;

	/**
	 * @var float
	 * @ORM\Column(name="float", type="float")
	 */
	protected $float;

	/**
	 * @var string
	 * @ORM\Column(name="decimal", type="decimal", precision=2, scale=1)
	 */
	protected $decimal;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="date", type="date")
	 */
	protected $date;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="time", type="time")
	 */
	protected $time;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime", type="datetime")
	 */
	protected $datetime;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetimetz", type="datetimetz")
	 */
	protected $datetimetz;

	/**
	 * @var string
	 * @ORM\Column(name="text", type="text")
	 */
	protected $text;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getString()
	{
		return $this->string;
	}

	/**
	 * @return array|Entity[]
	 */
	public function getEntities()
	{
		return $this->entities;
	}

	/**
	 * @return Entity1
	 */
	public function getEntity1()
	{
		return $this->entity_1;
	}

	/**
	 * @return int
	 */
	public function getInteger()
	{
		return $this->integer;
	}

	/**
	 * @return int
	 */
	public function getSmallint()
	{
		return $this->smallint;
	}

	/**
	 * @return int
	 */
	public function getBigint()
	{
		return $this->bigint;
	}

	/**
	 * @return boolean
	 */
	public function getBoolean()
	{
		return $this->boolean;
	}

	/**
	 * @return float
	 */
	public function getDecimal()
	{
		return $this->decimal;
	}

	/**
	 * @return float
	 */
	public function getFloat()
	{
		return $this->float;
	}

	/**
	 * @return \Datetime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return \Datetime
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * @return \Datetime
	 */
	public function getDatetime()
	{
		return $this->datetime;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimetz()
	{
		return $this->datetimetz;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param int $id
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @param string $string
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setString($string)
	{
		$this->string = $string;
		return $this;
	}

	/**
	 * @param \Doctrine\Common\Collections\ArrayCollection $entities
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setEntities(\Doctrine\Common\Collections\ArrayCollection $entities)
	{
		$this->entities = $entities;
		return $this;
	}

	/**
	 * @param \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity1 $entity_1
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setEntity1(Entity1 $entity_1)
	{
		$this->entity_1 = $entity_1;
		return $this;
	}

	/**
	 * @param int $integer
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setInteger($integer)
	{
		$this->integer = $integer;
		return $this;
	}

	/**
	 * @param int $smallint
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setSmallint($smallint)
	{
		$this->smallint = $smallint;
		return $this;
	}

	/**
	 * @param int $bigint
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setBigint($bigint)
	{
		$this->bigint = $bigint;
		return $this;
	}

	/**
	 * @param boolen $boolean
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setBoolean($boolean)
	{
		$this->boolean = $boolean;
		return $this;
	}

	/**
	 * @param float $float
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setFloat($float)
	{
		$this->float = $float;
		return $this;
	}

	/**
	 * @param float $decimal
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setDecimal($decimal)
	{
		$this->decimal = $decimal;
		return $this;
	}

	/**
	 * @param \DateTime $date
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setDate(\DateTime $date)
	{
		$this->date = $date;
		return $this;
	}

	/**
	 * @param \DateTime $time
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setTime(\DateTime $time)
	{
		$this->time = $time;
		return $this;
	}

	/**
	 * @param \DateTime $datetime
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setDatetime(\DateTime $datetime)
	{
		$this->datetime = $datetime;
		return $this;
	}

	/**
	 * @param \DateTime $datetimetz
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setDatetimetz(\DateTime $datetimetz)
	{
		$this->datetimetz = $datetimetz;
		return $this;
	}

	/**
	 * @param string $text
	 * @return \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}
}
