<?php

namespace App\Repository;

use App\Entity\MagicLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MagicLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method MagicLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method MagicLink[]    findAll()
 * @method MagicLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagicLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagicLink::class);
    }

    public function save(MagicLink $magicLink)
    {
        $this->_em->persist($magicLink);
        $this->_em->flush();
    }

    public function remove(MagicLink $magicLink)
    {
        $this->_em->remove($magicLink);
        $this->_em->flush();
    }

    public function findOneByToken(string $token) : ?MagicLink
    {
        return $this
            ->createQueryBuilder('l')
            ->where('l.token = :token')
            ->setParameter('token', $token)
            ->andWhere('l.expiresAt > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return MagicLink[] Returns an array of MagicLink objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MagicLink
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
