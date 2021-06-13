<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Setting
 *
 * @ORM\Table(name="setting")
 * @ORM\Entity(repositoryClass="App\Repository\SettingRepository")
 */
class Setting
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="property", type="string", length=255, unique=true)
     */
    private $property;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;

    public function getId() : int
    {
        return $this->id;
    }

    public function getProperty() : string
    {
        return $this->property;
    }

    public function setProperty(string $property) : Setting
    {
        $this->property = $property;

        return $this;
    }

    public function getValue() : ?string
    {
        return $this->value;
    }

    public function setValue(string $value) : Setting
    {
        $this->value = $value;

        return $this;
    }
}
