<?php

namespace App\Controller\Admin;

use App\Repository\SettingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints;

/**
 * @Route("/admin/state")
 * @IsGranted("ROLE_ADMIN")
 */
class StateController extends AbstractController
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
     * @Route(name="admin_state")
     * @Template()
     */
    public function index(Request $request)
    {
        $choices = ['STOP', 'LIVE', 'VOTE', 'RANK'];
        $setting = $this->settingRepository->get('state') ?? reset($choices);
        $input   = ['state' => $setting];

        $form = $this
            ->container
            ->get('form.factory')
            ->createBuilder(FormType::class, $input)
            ->add('state', ChoiceType::class, [
                'label'       => false,
                'choices'     => array_combine($choices, $choices),
                'expanded'    => true,
                'constraints' => [
                    new Constraints\Choice($choices),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->settingRepository->set('state', $form->get('state')->getData());

            return new RedirectResponse(
                $this->generateUrl('admin_state')
            );
        }

        return [
            'form' => $form->createView(),
        ];
    }
}