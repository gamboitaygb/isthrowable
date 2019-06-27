<?php

namespace App\Repository;

use App\Entity\LikePost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LikePost|null find($id, $lockMode = null, $lockVersion = null)
 * @method LikePost|null findOneBy(array $criteria, array $orderBy = null)
 * @method LikePost[]    findAll()
 * @method LikePost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikePostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LikePost::class);
    }

//    /**
//     * @return LikePost[] Returns an array of LikePost objects
//     */

    public function findAllPostLike($post)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.post = :val')
            ->andWhere('l.likep = 1')
            ->setParameter('val', $post)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByLikePostByUser($post,$user)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.post = :post')
            ->andWhere('l.user = :user')
            ->setParameter('post', $post)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?LikePost
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
