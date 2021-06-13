<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $project)
    {
        $this->_em->persist($project);
        $this->_em->flush();
    }

    public function remove(Project $project)
    {
        $this->_em->remove($project);
        $this->_em->flush();
    }

    /**
     * @return Project[]
     */
    public function findAll()
    {
        $projects = [];
        foreach ($this->findBy([], ['position' => 'ASC']) as $project) {
            $projects[$project->getId()] = $project;
        }

        return $projects;
    }
}
