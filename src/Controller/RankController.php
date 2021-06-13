<?php

namespace App\Controller;

use App\Manager\RankManager;
use App\Repository\CategoryRepository;
use App\Repository\GradeRepository;
use App\Repository\ProjectRepository;
use App\Repository\SettingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RankController extends AbstractController
{
    /**
     * @var RankManager
     */
    private $rankManager;

    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var GradeRepository
     */
    private $gradeRepository;

    public function __construct(RankManager $rankManager,
        SettingRepository $settingRepository,
        ProjectRepository $projectRepository,
        CategoryRepository $categoryRepository,
        GradeRepository $gradeRepository)
    {
        $this->rankManager        = $rankManager;
        $this->settingRepository  = $settingRepository;
        $this->projectRepository  = $projectRepository;
        $this->categoryRepository = $categoryRepository;
        $this->gradeRepository    = $gradeRepository;
    }

    /**
     * @Template()
     */
    public function rank()
    {
        $state = $this->settingRepository->get('state', 'NONE');

        if (!$this->getUser()->isAdmin() && $state !== 'RANK') {
            throw $this->createNotFoundException();
        }

        $grades = $this->gradeRepository->findAll();

        return [
            'projects'     => $this->projectRepository->findAll(),
            'categories'   => $this->categoryRepository->findAll(),
            'king'         => $this->rankManager->computeKing($grades),
            'winners'      => $this->rankManager->computeWinners($grades),
            'byCategories' => $this->rankManager->computeByCategories($grades),
        ];
    }
}
