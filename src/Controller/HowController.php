<?php

namespace App\Controller;

use App\Manager\RankManager;
use App\Repository\CategoryRepository;
use App\Repository\GradeRepository;
use App\Repository\ProjectRepository;
use App\Repository\SettingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HowController extends AbstractController
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
     * @var GradeRepository
     */
    private $gradeRepository;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(RankManager $rankManager,
        SettingRepository $settingRepository,
        GradeRepository $gradeRepository,
        ProjectRepository $projectRepository,
        CategoryRepository $categoryRepository)
    {
        $this->rankManager        = $rankManager;
        $this->settingRepository  = $settingRepository;
        $this->gradeRepository    = $gradeRepository;
        $this->projectRepository  = $projectRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Template()
     * @Route("/how", name="how")
     */
    public function howAction()
    {
        $status = $this->settingRepository->get('state', 'NONE');

        if (!$this->getUser()->isAdmin() && $status !== 'RANK') {
            throw $this->createNotFoundException();
        }

        $grades = $this->gradeRepository->findAll();

        return [
            'projects'   => $this->projectRepository->findAll(),
            'categories' => $this->categoryRepository->findAll(),
            'king'       => $this->rankManager->computeKing($grades),
            'hows'       => $this->rankManager->computeWinners($grades)['how'],
        ];
    }
}
