<?php

namespace App\Controller\Admin;

use App\Controller\RankController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/results")
 * @IsGranted("ROLE_ADMIN")
 */
class ResultsController extends AbstractController
{
    /**
     * @Route(name="admin_results")
     */
    public function results()
    {
        return $this->forward(sprintf('%s::rank', RankController::class));
    }
}