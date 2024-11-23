<?php

namespace App\Command;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Cette commande permet de créer un utilisateur administrateur.',
)]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Créer un utilisateur administrateur')
            ->setHelp('Cette commande permet de créer un utilisateur admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // Demande du pseudo
        $pseudoQuestion = new Question('Entrez le pseudo: ');
        $pseudo = $helper->ask($input, $output, $pseudoQuestion);

        // Demande de l'email
        $emailQuestion = new Question('Entrez l\'email: ');
        $email = $helper->ask($input, $output, $emailQuestion);

        // Demande du mot de passe (masqué)
        $passwordQuestion = new Question('Entrez le mot de passe: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $passwordQuestion);

        //demande de nom
        $nomQuestion = new Question('Entrez le nom: ');
        $nom = $helper->ask($input, $output, $nomQuestion);

        //demande de prénom
        $prenomQuestion = new Question('Entrez le prénom: ');
        $prenom = $helper->ask($input, $output, $prenomQuestion);

        // demande de numéro de téléphone
        $telephoneQuestion = new Question('Entrez le numéro de téléphone: ');
        $telephone = $helper->ask($input, $output, $telephoneQuestion);

        // Création de l'utilisateur
        $admin = new Participant();
        $admin->setPseudo($pseudo);
        $admin->setEmail($email);
        $admin->setNom($nom);
        $admin->setPrenom($prenom);
        $admin->setTelephone($telephone);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $password));

        // Sauvegarde dans la base de données
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $output->writeln('<info>Utilisateur administrateur créé avec succès.</info>');

        return Command::SUCCESS;
    }
}
