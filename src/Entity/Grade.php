<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Grade
 *
 * @ORM\Table(name="grade")
 * @ORM\Entity(repositoryClass="App\Repository\GradeRepository")
 */
class Grade
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
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="project_id", type="integer")
     */
    private $projectId;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer")
     */
    private $categoryId;

    /**
     * @var int
     *
     * @ORM\Column(name="grade", type="integer")
     */
    private $grade;

    public function getId() : int
    {
        return $this->id;
    }

    public function getUserId() : int
    {
        return $this->userId;
    }

    public function setUserId(int $userId) : Grade
    {
        $this->userId = $userId;

        return $this;
    }

    public function getProjectId() : int
    {
        return $this->projectId;
    }

    public function setProjectId(int $projectId) : Grade
    {
        $this->projectId = $projectId;

        return $this;
    }

    public function getCategoryId() : int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId) : Grade
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getGrade() : int
    {
        return $this->grade;
    }

    public function setGrade(int $grade) : Grade
    {
        $this->grade = $grade;

        return $this;
    }
}
