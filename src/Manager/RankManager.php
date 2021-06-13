<?php

namespace App\Manager;

use App\Entity\Grade;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;

class RankManager
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    public function __construct(CategoryRepository $categoryRepository, ProjectRepository $projectRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->projectRepository  = $projectRepository;
    }

    /**
     * @param Grade[] $grades
     */
    public function computeKing(array $grades)
    {
        if (count($grades) === 0) {
            return null;
        }

        $scores = [];
        foreach ($grades as $grade) {
            if (!isset($scores[$grade->getProjectId()])) {
                $scores[$grade->getProjectId()] = 0;
            }
            $scores[$grade->getProjectId()] += $grade->getGrade();
        }

        arsort($scores);

        return [
            'id'     => key($scores),
            'score'  => reset($scores),
            'scores' => $scores,
        ];
    }

    /**
     * @param Grade[] $grades
     */
    public function computeWinners(array $grades)
    {
        $how = [];

        $byCategories = $this->computeByCategories($grades);
        $flat         = [];
        foreach ($byCategories as $categoryId => $category) {
            foreach ($category as $projectId => $data) {
                $flat[sprintf("%d/%d", $categoryId, $projectId)] = $data;
            }
        }

        uasort($flat, function ($a, $b) {
            return $a['score'] < $b['score'] ? 1 : -1;
        });

        $categories = $this->categoryRepository->findAll();
        $winners    = [];
        foreach ($categories as $category) {
            $winners[$category->getId()] = null;
        }

        $winnersCount = 0;
        $multi        = [];
        $king         = $this->computeKing($grades);
        foreach ($flat as $key => $data) {
            [$categoryId, $projectId] = explode('/', $key);

            if ($winnersCount == count($categories)) {
                $how[] = [
                    'projectId'  => $projectId,
                    'categoryId' => $categoryId,
                    'score'      => $data['score'],
                    'status'     => ['danger', 'All categories have a winner.'],
                ];

                continue;
            }

            if ($winners[$categoryId]) {
                $how[] = [
                    'projectId'  => $projectId,
                    'categoryId' => $categoryId,
                    'score'      => $data['score'],
                    'status'     => ['danger', 'There is already a winner for this category'],
                ];

                continue;
            }

            // ------------------------------------------
            // A project can't win twice, it only wins on its best category

            if ($king && $projectId == $king['id']) {
                if (!in_array($data['project']->getTitle(), $multi)) {
                    $multi[] = $data['project']->getTitle();
                }

                $how[] = [
                    'projectId'  => $projectId,
                    'categoryId' => $categoryId,
                    'score'      => $data['score'],
                    'status'     => ['warning', 'This project is the king'],
                ];

                continue;
            }

            $canWin = true;
            foreach ($winners as $winner) {
                if ($winner && $winner['project']->getId() == $projectId) {
                    $canWin = false;

                    $how[] = [
                        'projectId'  => $projectId,
                        'categoryId' => $categoryId,
                        'score'      => $data['score'],
                        'status'     => ['warning', 'This project already won in a category'],
                    ];

                    if (!in_array($winner['project']->getTitle(), $multi)) {
                        $multi[] = $winner['project']->getTitle();
                    }

                    break;
                }
            }
            if (!$canWin) {
                continue;
            }

            // ------------------------------------------

            $winners[$categoryId] = $data;
            $winnersCount++;

            $how[] = [
                'projectId'  => $projectId,
                'categoryId' => $categoryId,
                'score'      => $data['score'],
                'status'     => ['success', 'This project wins!'],
            ];
        }

        return [
            'winners' => $winners,
            'multi'   => $multi,
            'how'     => $how,
        ];
    }

    /**
     * @param Grade[] $grades
     *
     * @return array
     */
    public function computeByCategories(array $grades)
    {
        // Initialize all fields
        $empty      = [];
        $details    = [];
        $categories = $this->categoryRepository->findAll();
        foreach ($categories as $category) {
            $empty[$category->getId()] = [];
        }
        $projects = $this->projectRepository->findAll();
        foreach ($projects as $project) {
            foreach (array_keys($empty) as $categoryId) {
                $empty[$categoryId][$project->getId()]   = 0;
                $details[$categoryId][$project->getId()] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            }
        }

        // Calculate sums of voters and scores
        $scores = $empty;
        $votes  = $empty;
        foreach ($grades as $grade) {
            $votes[$grade->getCategoryId()][$grade->getProjectId()]++;
            $scores[$grade->getCategoryId()][$grade->getProjectId()] += $grade->getGrade();
            $details[$grade->getCategoryId()][$grade->getProjectId()][$grade->getGrade()]++;
        }

        // Calculate average score for each project in each category
        $averages = [];
        foreach ($scores as $categoryId => $category) {
            foreach ($category as $projectId => $score) {
                $averages[$categoryId][$projectId] = $votes[$categoryId][$projectId] ? $score / $votes[$categoryId][$projectId] : 0;
            }
        }

        // Sort results by best score for each category
        foreach ($averages as $categoryId => $category) {
            arsort($category);
            $averages[$categoryId] = $category;
        }

        // Rebuild an array containing everything
        $summary = [];
        foreach ($averages as $categoryId => $category) {
            foreach ($category as $projectId => $score) {
                $percent = [];
                foreach ($details[$categoryId][$projectId] as $stars => $detail) {
                    $percent[$stars] = $votes[$categoryId][$projectId] ? $detail * 100 / $votes[$categoryId][$projectId] : 0;
                }

                $summary[$categoryId][$projectId] = [
                    'project'  => $projects[$projectId],
                    'category' => $categories[$categoryId],
                    'score'    => $score,
                    'votes'    => $votes[$categoryId][$projectId],
                    'detail'   => $details[$categoryId][$projectId],
                    'percent'  => $percent,
                ];
            }
        }

        return $summary;
    }
}