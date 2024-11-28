<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFiltres($critere, $participant)
    {
        $qb = $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC');

           if (!empty($critere['nom'])){
                $qb->andWhere('s.nom LIKE :nom')
                     ->setParameter('nom', '%'.$critere['nom'].'%');
           }
           if(!empty($critere['site'])){
                $qb->andWhere('s.site = :site')
                     ->setParameter('site', $critere['site']);
           }
           if(!empty($critere['dateDebut'])){
                $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                     ->setParameter('dateDebut', $critere['dateDebut']);
           }
              if(!empty($critere['dateFin'])){
                 $qb->andWhere('s.dateHeureDebut <= :dateFin')
                        ->setParameter('dateFin', $critere['dateFin']);
              }
                if(!empty($critere['organisateur'])){
                     $qb->andWhere('s.organisateur = :organisateur')
                            ->setParameter('organisateur', $participant);
                }
                if(!empty($critere['inscrit'])){
                     $qb->join('s.participants', 'p')
                            ->andWhere('p = :inscrit')
                            ->setParameter('inscrit', $participant);
                }
                if(!empty($critere['nonInscrit'])){
                     $qb->join('s.participants', 'p')
                            ->andWhere('p != :nonInscrit')
                            ->setParameter('nonInscrit', $participant);
                }
                if(!empty($critere['passees'])){
                     $qb->andWhere('s.dateHeureDebut < :dateDuJour')
                            ->setParameter('dateDuJour', new \DateTimeImmutable());
                }else{
                     $qb->andWhere('s.dateHeureDebut >= :dateDuJour')
                            ->setParameter('dateDuJour', new \DateTimeImmutable());
                }


        return $qb->getQuery()->getResult();
    }

    public function findAllOrderByDate()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

     public function findPastSorties(): array
     {
          return $this->createQueryBuilder('s')
               ->andWhere('s.dateHeureDebut < :today')
               ->setParameter('today', new \DateTime())
               ->orderBy('s.dateHeureDebut', 'ASC') // Trier par date croissante
               ->getQuery()
               ->getResult();
     }
    
    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
