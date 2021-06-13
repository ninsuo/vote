<?php

namespace App\Command;

use App\Repository\MagicLinkRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MagicLinkCommand extends Command
{
    protected static $defaultName = 'app:magic-link';

    /**
     * @var MagicLinkRepository
     */
    private $magicLinkRepository;

    public function __construct(MagicLinkRepository $magicLinkRepository)
    {
        parent::__construct();

        $this->magicLinkRepository = $magicLinkRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->magicLinkRepository->clearExpired();

        return 0;
    }
}