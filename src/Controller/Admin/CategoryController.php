<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\GradeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @Route("/admin/categories")
 * @IsGranted("ROLE_ADMIN")
 */
class CategoryController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var GradeRepository
     */
    private $gradeRepository;

    public function __construct(CategoryRepository $categoryRepository, GradeRepository $gradeRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->gradeRepository    = $gradeRepository;
    }

    /**
     * @Route(name="admin_category")
     * @Template()
     */
    public function list(Request $request)
    {
        $form = $this->getCreateForm($request);
        if (is_null($form)) {
            return $this->redirectToRoute('admin_category');
        }

        return [
            'categories' => $this->categoryRepository->findAll(),
            'create'     => $form,
        ];
    }

    /**
     * @Route("/edit/name/{id}", name="admin_category_edit_name")
     * @Template("editor.html.twig")
     */
    public function editName(Request $request, int $id)
    {
        $entity = $this->categoryRepository->findOneById($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $endpoint = $this->generateUrl('admin_category_edit_name', ['id' => $id]);

        $form = $this
            ->container
            ->get('form.factory')
            ->createNamedBuilder("edit-category-name-{$id}", FormType::class, $entity, [
                'action' => $endpoint,
                'attr'   => [
                    'class' => 'editor-form',
                ],
            ])
            ->add('name', TextType::class, [
                'label'       => 'New name',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Change',
            ])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryRepository->save($entity);

            return [
                'text'     => $entity->getName(),
                'endpoint' => $endpoint,
            ];
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/edit/position/{id}", name="admin_category_edit_position")
     * @Template("editor.html.twig")
     */
    public function editPosition(Request $request, int $id)
    {
        $entity = $this->categoryRepository->findOneById($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $endpoint = $this->generateUrl('admin_category_edit_position', ['id' => $id]);

        $form = $this
            ->container
            ->get('form.factory')
            ->createNamedBuilder("edit-category-position-{$id}", FormType::class, $entity, [
                'action' => $endpoint,
                'attr'   => [
                    'class' => 'editor-form',
                ],
            ])
            ->add('position', TextType::class, [
                'label'       => 'New position',
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => -1000, 'max' => 1000]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Change',
            ])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryRepository->save($entity);

            return [
                'text'     => $entity->getPosition(),
                'endpoint' => $endpoint,
            ];
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/delete/{id}/{token}", name="admin_category_delete")
     * @Template()
     */
    public function deleteAction(int $id, string $token)
    {
        if (!$this->isCsrfTokenValid('admin_category', $token)) {
            throw $this->createNotFoundException();
        }

        $entity = $this->categoryRepository->findOneById($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $this->gradeRepository->removeByCategory($entity);

        $this->categoryRepository->remove($entity);

        return $this->redirect($this->generateUrl('admin_category'));
    }

    protected function getCreateForm(Request $request)
    {
        $entity = new Category();

        $formBuilder = $this
            ->container
            ->get('form.factory')
            ->createNamedBuilder('create-category', FormType::class, $entity)
            ->add('name', TextType::class, [
                'label'       => 'Category name',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                ],
            ])
            ->add('position', NumberType::class, [
                'label'       => 'Category position (will change the overall rendering)',
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => -1000, 'max' => 1000]),
                ],
            ])
            ->add('image', FileType::class, [
                'label'       => 'Image',
                'constraints' => [
                    new NotBlank(),
                ],
                'required'    => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create',
            ]);

        $formBuilder
            ->get('image')
            ->addModelTransformer(new CallbackTransformer(
                function ($stringAsFile) {
                },
                function ($fileAsString) {
                    return $this->treatUpload($fileAsString);
                }
            ));

        $form = $formBuilder
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryRepository->save($entity);

            return null;
        }

        return $form->createView();
    }

    protected function treatUpload(UploadedFile $file = null)
    {
        if (null === $file) {
            return null;
        }

        $source = $file->getPathname();

        $size = getimagesize($source);
        if ($size === false) {
            return null;
        }

        if ($size[0] > 2000 || $size[1] > 2000) {
            return null;
        }

        $sourceImg = @imagecreatefromstring(@file_get_contents($source));
        if ($sourceImg === false) {
            return null;
        }

        $width       = imagesx($sourceImg);
        $height      = imagesy($sourceImg);
        $targetImg   = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocate($targetImg, 255, 0, 255);
        imagefill($targetImg, 0, 0, $transparent);
        imagecolortransparent($targetImg, $transparent);
        imagecopy($targetImg, $sourceImg, 0, 0, 0, 0, $width, $height);
        imagedestroy($sourceImg);

        ob_start();
        imagepng($targetImg);
        imagedestroy($targetImg);

        return base64_encode(ob_get_clean());
    }
}
