<?php

namespace App\Manager;

use App\Entity\MagicLink;
use App\Entity\User;
use App\Repository\MagicLinkRepository;
use App\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class MagicLinkManager
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var MagicLinkRepository
     */
    private $magicLinkRepository;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(UserRepository $userRepository,
        MagicLinkRepository $magicLinkRepository,
        MailerInterface $mailer,
        RouterInterface $router,
        Environment $twig,
        ParameterBagInterface $parameterBag)
    {
        $this->userRepository      = $userRepository;
        $this->magicLinkRepository = $magicLinkRepository;
        $this->mailer              = $mailer;
        $this->router              = $router;
        $this->twig                = $twig;
        $this->parameterBag        = $parameterBag;
    }

    public function sendMagicLink(string $email)
    {
        $hash = User::createEmailHash($email, $this->parameterBag->get('secret'));
        $user = $this->userRepository->findOneByHash($hash);
        if (!$user) {
            $user = new User();
            $user->setUuid(Uuid::uuid4());
            $user->setHash($hash);
            $this->userRepository->save($user);
        }

        $magicLink = new MagicLink();
        $magicLink->setUser($user);
        $magicLink->setToken(Uuid::uuid4());
        $this->magicLinkRepository->save($magicLink);

        $message = (new NotificationEmail())
            ->from($this->parameterBag->get('email_sender'))
            ->to($email)
            ->subject('Your link to connect to the vote box')
            ->markdown(
                $this->twig->render('login/email_containing_link.md.twig', [
                    'token' => $magicLink,
                ])
            )
            ->action('Launch', $this->router->generate('token', [
                'token' => $magicLink->getToken(),
            ], RouterInterface::ABSOLUTE_URL))
            ->importance(NotificationEmail::IMPORTANCE_URGENT);

        $this->mailer->send($message);
    }
}