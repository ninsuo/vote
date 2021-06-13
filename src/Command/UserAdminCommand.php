<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserAdminCommand extends Command
{
    protected static $defaultName = 'app:user-admin';

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
        $uuid = $input->getArgument('uuid');
        $user = $this->userRepository->findOneByUuid($uuid);

        if (is_null($user)) {
            $output->writeln("<error>User {$uuid} not found.</error>");

            return 1;
        }

        $user->setIsAdmin(1 - $user->isAdmin());
        $this->userRepository->save($user);

        $status = $user->isAdmin() ? '<question>admin</question>' : '<error>simple user</error>';
        $output->writeln("User <info>{$uuid}</info> is now: {$status}.");

        return 0;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Set/Unset user as admin')
            ->addArgument('uuid', InputArgument::REQUIRED, 'User UUID');
    }
}