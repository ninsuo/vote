<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $category)
    {
        $this->_em->persist($category);
        $this->_em->flush();
    }

    public function remove(Category $category)
    {
        $this->_em->remove($category);
        $this->_em->flush();
    }

    /**
     * @return Category[]
     */
    public function findAll()
    {
        $categories = [];
        foreach ($this->findBy([], ['position' => 'ASC']) as $category) {
            $categories[$category->getId()] = $category;
        }

        return $categories;
    }
}
