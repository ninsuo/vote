<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @Route("/admin/projects")
 * @IsGranted("ROLE_ADMIN")
 */
class ProjectController extends AbstractController
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
     * @Route(name="admin_project")
     * @Template()
     */
    public function list(Request $request)
    {
        $form = $this->getCreateForm($request);

        if (is_null($form)) {
            return $this->redirectToRoute('admin_project');
        }

        return [
            'projects' => $this->projectRepository->findAll(),
            'create'   => $form,
        ];
    }

    /**
     * @Route("/delete/{id}/{token}", name="admin_project_delete")
     */
    public function delete($id, $token)
    {
        if (!$this->isCsrfTokenValid('admin_project', $token)) {
            throw $this->createNotFoundException();
        }

        $entity = $this->projectRepository->findOneById($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $this->projectRepository->remove($entity);

        return $this->redirect($this->generateUrl('admin_project'));
    }

    /**
     * @Route("/edit/title/{id}", name="admin_project_edit_title")
     * @Template("editor.html.twig")
     */
    public function editTitle(Request $request, $id)
    {
        $entity = $this->projectRepository->findOneById($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $endpoint = $this->generateUrl('admin_project_edit_title', ['id' => $id]);

        $form = $this
            ->container
            ->get('form.factory')
            ->createNamedBuilder("edit-project-title-{$id}", FormType::class, $entity, [
                'action' => $endpoint,
                'attr'   => [
                    'class' => 'editor-form',
                ],
            ])
            ->add('title', TextType::class, [
                'label'       => 'New project name',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Update',
            ])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->projectRepository->save($entity);

            return [
                'text'     => $entity->getTitle(),
                'endpoint' => $endpoint,
            ];
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/edit/position/{id}", name="admin_project_edit_position")
     * @Template("editor.html.twig")
     */
    public function editPosition(Request $request, $id)
    {
        $entity = $this->projectRepository->findOneById($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $endpoint = $this->generateUrl('admin_project_edit_position', ['id' => $id]);

        $form = $this
            ->container
            ->get('form.factory')
            ->createNamedBuilder("edit-project-position-{$id}", FormType::class, $entity, [
                'action' => $endpoint,
                'attr'   => [
                    'class' => 'editor-form',
                ],
            ])
            ->add('position', TextType::class, [
                'label'       => 'New project position',
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => -1000, 'max' => 1000]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Update',
            ])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->projectRepository->save($entity);

            return [
                'text'     => $entity->getPosition(),
                'endpoint' => $endpoint,
            ];
        }

        return [
            'form' => $form->createView(),
        ];
    }

    protected function getCreateForm(Request $request)
    {
        $entity = new Project();

        $form = $this
            ->container
            ->get('form.factory')
            ->createNamedBuilder('create-project', FormType::class, $entity)
            ->add('title', TextType::class, [
                'label'       => 'New project title',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                ],
            ])
            ->add('position', TextType::class, [
                'label'       => 'New project position',
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => -1000, 'max' => 1000]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create',
            ])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->projectRepository->save($entity);

            return null;
        }

        return $form->createView();
    }
}
