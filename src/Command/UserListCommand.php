<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserListCommand extends Command
{
    protected static $defaultName = 'app:user-list';

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        (new Table($output))
            ->setHeaders(['UUID', 'Admin'])
            ->setRows(array_map(function (User $user) {
                return [
                    $user->getUuid(),
                    var_export($user->isAdmin(), true),
                ];
            }, $this->userRepository->findAll()))->render();

        return 0;
    }
}