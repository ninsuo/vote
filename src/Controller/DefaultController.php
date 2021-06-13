<?php

namespace App\Controller;

use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @var SettingRepository
     */
    private $settingRepository;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        $state = $this->settingRepository->get('state', 'NONE');

        switch ($state) {
            case 'LIVE':
                return $this->forward(sprintf('%s::live', LiveController::class));
            case 'VOTE':
                return $this->forward(sprintf('%s::vote', VoteController::class));
            case 'RANK':
                return $this->forward(sprintf('%s::rank', RankController::class));
            default:
                return $this->forward(sprintf('%s::stop', StopController::class));
        }
    }
}
