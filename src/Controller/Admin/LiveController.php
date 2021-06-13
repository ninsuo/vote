<?php

namespace App\Controller\Admin;

use App\Repository\ProjectRepository;
use App\Repository\SettingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/live")
 * @IsGranted("ROLE_ADMIN")
 */
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

    public function __construct(SettingRepository $settingRepository, ProjectRepository $projectRepository)
    {
        $this->settingRepository = $settingRepository;
        $this->projectRepository = $projectRepository;
    }

    /**
     * @Route(name="admin_live")
     * @Template()
     */
    public function index(Request $request)
    {
        $none = $this->settingRepository->get('message_none');
        $live = $this->settingRepository->get('message_live');

        $form = $this
            ->createFormBuilder(['none' => $none, 'live' => $live])
            ->add('none', TextType::class, [
                'label' => 'Message when (none) is selected',
            ])
            ->add('live', TextType::class, [
                'label' => 'Message when a project is selected',
            ])
            ->add('set', SubmitType::class)
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->settingRepository->set('message_none', $form->get('none')->getData());
            $this->settingRepository->set('message_live', $form->get('live')->getData());

            return $this->redirectToRoute('admin_live');
        }

        return [
            'projects' => $this->projectRepository->findAll(),
            'status'   => $this->settingRepository->get('live') ?: 0,
            'message'  => $form->createView(),
        ];
    }

    /**
     * @Route("/change/{csrf}/{id}", name="admin_live_change")
     */
    public function change(Request $request, $csrf, $id)
    {
        if (!$this->isCsrfTokenValid('live', $csrf)) {
            throw $this->createNotFoundException();
        }

        $project = $this->projectRepository->findOneById($id);
        if ($id == 0 || $project) {
            $this->settingRepository->set('live', $project ? $project->getId() : $id);
        }

        return $this->redirectToRoute('admin_live');
    }
}