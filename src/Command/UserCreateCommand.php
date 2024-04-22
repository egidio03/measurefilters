<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * Class UserCreateCommand
 * This command is used to create the first user in the system
 *
 * @package App\Command
 * @author Egidio Langellotti
 * @version 1.0
 *
 */
class UserCreateCommand extends Command
{
    private $entityManager;
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    public function __construct(EntityManagerInterface $entityManager, PasswordHasherFactoryInterface $passwordHasherFactory)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasherFactory = $passwordHasherFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:user:create')
            ->setDescription('Creates a new user with the provided username and password.')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the new user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the new user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Check if there are already users in the system
        $users = $this->entityManager->getRepository(User::class)->count();

        // If there are no users, create the first user
        if ($users == 0) {
            $username = $input->getArgument('username');
            $password = $input->getArgument('password');

            $user = new User();
            $user->setUsername($username);
            $user->setPassword($this->passwordHasherFactory->getPasswordHasher($user)->hash($password));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $output->writeln('User created with username: ' . $username);
        } else {
            $output->writeln('No need to create user');
        }

        return Command::SUCCESS;
    }
}
