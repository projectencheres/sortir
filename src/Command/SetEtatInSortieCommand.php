<?php

namespace App\Command;

use Doctrine\ORM\Mapping\Entity;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:set-etat-inSortie',
    description: 'commande pour changer l\'état d\'une sortie encours a cloturée',
)]
class SetEtatInSortieCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SortieRepository $sortieRepository;
    
    public function __construct( EntityManagerInterface $entityManager, SortieRepository $sortieRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->sortieRepository = $sortieRepository;
    }
    protected function configure(): void
    {
        $this
            ->setDescription('Verifier les sorties dejà passées afin de changer leur Etat')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // recuperer les sorties passées
        $sorties = $this->sortieRepository->findPastSorties();
        $sortiesArchives = $this->sortieRepository->findSortiesArchives();

        foreach ($sorties as $sortie) {
            $dateDuJour = new \DateTimeImmutable();
            $dateDebut = $sortie->getDateHeureDebut();
            if ($dateDuJour > $dateDebut) {
                $sortie->setEtat('Cloturée');
                $this->entityManager->persist($sortie);
            }
           
        }

        foreach ($sortiesArchives as $sortie) {
            $dateArchive = $sortie->getDateHeureDebut()->modify('+1 month');
            $dateDebut = $sortie->getDateHeureDebut();
            if ($dateArchive > $dateDebut) {
                $sortie->setEtat('Archivée');
                $sortie->setArchives(true);
                $this->entityManager->persist($sortie);
            }
        }

        $this->entityManager->flush();

        $io->success('Les sorties passées ont été cloturées et/ou archivées');

        return Command::SUCCESS;
    }
}
