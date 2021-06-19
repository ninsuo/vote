<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Grade;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    public function remove(Grade $grade)
    {
        $this->_em->remove($grade);
        $this->_em->flush();
    }

    public function removeByProject(Project $project)
    {
        $this->createQueryBuilder('g')
             ->delete()
             ->where('g.projectId = :projectId')
             ->setParameter('projectId', $project->getId())
             ->getQuery()
             ->execute();
    }

    public function removeByCategory(Category $category)
    {
        $this->createQueryBuilder('g')
             ->delete()
             ->where('g.categoryId = :categoryId')
             ->setParameter('categoryId', $category->getId())
             ->getQuery()
             ->execute();
    }

    /**
     * @return Grade[]
     */
    public function forUser(User $user)
    {
        $grades = [];
        foreach ($this->findByUserId($user->getId()) as $grade) {
            $grades[$grade->getProjectId()][$grade->getCategoryId()] = $grade->getGrade();
        }

        return $grades;
    }

    /**
     * @return Grade[]
     */
    public function forUserAndProject(User $user, Project $project)
    {
        $data = $this->findBy([
            'userId'    => $user->getId(),
            'projectId' => $project->getId(),
        ]);

        $grades = [];
        foreach ($data as $grade) {
            $grades[$grade->getCategoryId()] = $grade->getGrade();
        }

        return $grades;
    }

    public function save(User $user, Project $project, Category $category, $grade)
    {
        $entity = $this->findOneBy([
            'userId'     => $user->getId(),
            'projectId'  => $project->getId(),
            'categoryId' => $category->getId(),
        ]);

        if (!$entity && !$grade) {
            return;
        }

        if ($entity && !$grade) {
            $this->_em->remove($entity);
            $this->_em->flush();

            return;
        }

        if (!$entity) {
            $entity = new Grade();

            $entity->setUserId($user->getId());
            $entity->setProjectId($project->getId());
            $entity->setCategoryId($category->getId());
        }

        $entity->setGrade($grade);

        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }
}
