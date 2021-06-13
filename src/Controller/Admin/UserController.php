<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/users")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route(name="admin_user")
     * @Template()
     */
    public function list()
    {
        return [
            'users' => $this->userRepository->findAll(),
            'me'    => $this->getUser(),
        ];
    }

    /**
     * @Route("/toggle/admin/{uuid}/{token}", name="admin_users_toggle_admin")
     */
    public function toggleAdmin(Request $request, User $user, string $token)
    {
        if (!$this->isCsrfTokenValid('user', $token)) {
            throw $this->createNotFoundException();
        }

        if ($user->getUserIdentifier() === $this->getUser()->getUserIdentifier()) {
            throw $this->createNotFoundException();
        }

        $user->setIsAdmin(1 - intval($user->isAdmin()));

        $this->userRepository->save($user);

        return $this->redirectToRoute('admin_user');
    }
}
