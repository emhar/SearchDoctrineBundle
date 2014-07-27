<?php

namespace Emhar\SearchDoctrineBundle\Tests\Models\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity
 *
 * @author emhar
 * @ORM\Entity
 * @ORM\Table(name="entity1")
 */
class Entity1
{

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var Entity2
	 * @ORM\ManyToOne(targetEntity="Entity2", inversedBy="entities")
	 */
	protected $entity_2;

	/**
	 * @var string
	 * @ORM\Column(name="string", type="string")
	 */
	protected $string;

	/**
	 * @return integer
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
	 * @return Entity2
	 */
	public function getEntity2()
	{
		return $this->entity_2;
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
	 * @param \Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2 $entity_2
	 * @return Entity
	 */
	public function setEntity2(Entity2 $entity_2)
	{
		$this->entity_2 = $entity_2;
		return $this;
	}

}
