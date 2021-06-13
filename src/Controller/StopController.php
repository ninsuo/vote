<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StopController extends AbstractController
{
    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * @Template()
     */
    public function stop()
    {
        return [
            'projects' => $this->projectRepository->findAll(),
        ];
    }
}
