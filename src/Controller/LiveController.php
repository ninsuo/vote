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

class LiveController extends AbstractController
{
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

    public function __construct(SettingRepository $settingRepository,
        ProjectRepository $projectRepository,
        CategoryRepository $categoryRepository,
        GradeRepository $gradeRepository)
    {
        $this->settingRepository  = $settingRepository;
        $this->projectRepository  = $projectRepository;
        $this->categoryRepository = $categoryRepository;
        $this->gradeRepository    = $gradeRepository;
    }

    /**
     * @Template()
     */
    public function live()
    {
        return [];
    }

    /**
     * @Route("/live", name="live")
     * @Template("live/xhr.html.twig")
     */
    public function liveBlock(Request $request)
    {
        $clientCurrent = $request->request->get('current');

        $state = $this->settingRepository->get('state', 'NONE');
        if ($state !== 'LIVE') {
            $current = 0;
        } else {
            $current = $this->settingRepository->get('live');
        }

        if ($clientCurrent == $current) {
            return new Response();
        }

        $project = null;
        $grades  = null;
        if ($current) {
            $project = $this->projectRepository->findOneById($current);
            if (is_null($project)) {
                throw $this->createNotFoundException();
            }

            $grades = $this->gradeRepository->forUserAndProject($this->getUser(), $project);
        }

        $message = $current ? 'message_live' : 'message_none';

        return [
            'categories' => $current ? $this->categoryRepository->findAll() : null,
            'project'    => $project,
            'grades'     => $grades,
            'current'    => $current,
            'message'    => $this->settingRepository->get($message),
        ];
    }
}
