<?php

namespace App\Repository;

use App\Entity\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function all() : array
    {
        $settings = [];
        $entities = $this->findAll();
        foreach ($entities as $entity) {
            $settings[$entity->getProperty()] = $entity->getValue();
        }

        return $entity;
    }

    public function get(string $property, $default = null)
    {
        $entity = $this->findOneByProperty($property);
        if ($entity) {
            return $entity->getValue();
        }

        return $default;
    }

    public function set(string $property, $value)
    {
        $entity = $this->findOneByProperty($property);
        if (!$entity) {
            $entity = new Setting();
            $entity->setProperty($property);
        }
        $entity->setValue($value);
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    public function remove(string $property)
    {
        $entity = $this->findOneByProperty($property);
        $this->_em->remove($entity);
        $this->_em->flush($entity);
    }
}
