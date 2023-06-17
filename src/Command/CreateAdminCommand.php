<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur avec le rôle admin',
)]
class CreateAdminCommand extends Command
{
    private SymfonyStyle $inputOutput;

    public function __construct(private readonly EntityManagerInterface $manager, private readonly UserPasswordHasherInterface $hasher)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nickname', InputArgument::OPTIONAL, 'pseudo')
            ->addArgument('email', InputArgument::OPTIONAL, 'email')
            ->addArgument('password', InputArgument::OPTIONAL, 'mot de passe')
        ;
    }

    // sortie stylisée de la commande
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->inputOutput = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getArgument('email') && null !== $input->getArgument('password')) {
            return;
        }
        $this->inputOutput->text('Add admin wizard');
        $this->askArgument($input, 'nickname');
        $this->askArgument($input, 'email');
        $this->askArgument($input, 'password', hidden: true);
    }

    private function askArgument(InputInterface $input, string $name, bool $hidden = false) : void
    {
        $value = strval($input->getArgument($name));
        if ('' !== $value) {
            $this->inputOutput->text(sprintf('> <info>%s</info>: %s', $name, $value));
        } else {
            $value = match ($hidden) {
                false => $this->inputOutput->ask($name),
                default => $this->inputOutput->askHidden($name)
            };

            $input->setArgument($name, $value);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setNickname(strval($input->getArgument('nickname')))
             ->setEmail(strval($input->getArgument('email')))
             ->setPassword($this->hasher->hashPassword($user, strval($input->getArgument('password'))))
             ->setRoles(['ROLE_ADMIN'])
        ;

        $this->manager->persist($user);
        $this->manager->flush();
        $this->inputOutput->text('Utilisateur ajouté');

        return Command::SUCCESS;
    }
}
