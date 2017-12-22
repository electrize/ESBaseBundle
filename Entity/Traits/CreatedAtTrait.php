<?php

namespace ES\Bundle\BaseBundle\Entity\Traits;

use Gedmo\Mapping\Annotation as Gedmo;

trait CreatedAtTrait
{
	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created_at", type="datetime")
	 * @Gedmo\Timestampable(on="create")
	 */
	protected $createdAt;

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}
}