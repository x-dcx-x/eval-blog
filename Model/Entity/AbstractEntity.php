<?php

namespace App\Model\Entity;

class AbstractEntity
{
    private ?int $id = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return AbstractEntity
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }
}