<?php

namespace App\Controller;

use App\Form\MagicLinkType;
use App\Manager\MagicLinkManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class LoginController extends AbstractController
{
    /**
     * @var MagicLinkManager
     */
    private $magicLinkManager;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(MagicLinkManager $magicLinkManager, SessionInterface $session)
    {
        $this->magicLinkManager = $magicLinkManager;
        $this->session          = $session;
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $form = $this
            ->createForm(MagicLinkType::class)
            ->handleRequest($request);

        if ($this->session->has(Security::AUTHENTICATION_ERROR)) {
            $form->addError(
                new FormError($this->session->get(Security::AUTHENTICATION_ERROR))
            );

            $this->session->remove(Security::AUTHENTICATION_ERROR);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magicLinkManager->sendMagicLink(
                $form->get('email')->getData()
            );

            $this->addFlash('success', '**Email sent!** You can now check your mailbox.');

            return $this->redirectToRoute('login');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/token/{token}", name="token")
     */
    public function token(string $token)
    {
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
    }
}