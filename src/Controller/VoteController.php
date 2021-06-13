<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\GradeRepository;
use App\Repository\ProjectRepository;
use App\Repository\SettingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoteController extends AbstractController
{
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

    /**
     * @var SettingRepository
     */
    private $settingRepository;

    public function __construct(ProjectRepository $projectRepository,
        CategoryRepository $categoryRepository,
        GradeRepository $gradeRepository,
        SettingRepository $settingRepository)
    {
        $this->projectRepository  = $projectRepository;
        $this->categoryRepository = $categoryRepository;
        $this->gradeRepository    = $gradeRepository;
        $this->settingRepository  = $settingRepository;
    }

    /**
     * @Template()
     */
    public function vote()
    {
        return [
            'projects'   => $this->projectRepository->findAll(),
            'categories' => $this->categoryRepository->findAll(),
            'grades'     => $this->gradeRepository->forUser($this->getUser()),
        ];
    }

    /**
     * @Route("/rate/{csrf}", name="rate")
     */
    public function rate(Request $request, $csrf)
    {
        if (!$this->isCsrfTokenValid('rate', $csrf)) {
            throw $this->createNotFoundException();
        }

        $state = $this->settingRepository->get('state', 'NONE');

        if ($state !== 'LIVE' && $state !== 'VOTE') {
            throw $this->createNotFoundException();
        }

        $project = $this->projectRepository->findOneById(
            $request->get('project')
        );

        if (null === $project) {
            throw $this->createNotFoundException();
        }

        $category = $this->categoryRepository->findOneById(
            $request->get('category')
        );

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        $grade = intval($request->get('grade'));
        if ($grade < 0 || $grade > 5) {
            throw $this->createNotFoundException();
        }

        $this->gradeRepository->save($this->getUser(), $project, $category, $grade);

        return new Response();
    }
}
