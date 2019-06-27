<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Comments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findPostHome()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return Post[] Returns an array of Post objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    public function findOneBySlug($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByComments($post)
    {
        $em = $this->getEntityManager();
        $result = $em->createQuery('
                  SELECT u FROM App:Comments u
                  WHERE u.post = :post');
        $result->setParameter('post', $post);
        try{
            return $result->getResult();
        }catch (\Exception $error){
            return false;
        }
    }

    public function findByCountComment()
    {
        $em = $this->getEntityManager();
        $q = $em->createQueryBuilder();
        $q->select(array('p.id'))
            ->from('App:Post', 'p')
            ->leftJoin('App:Comments','c',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'p = c.post')
            ->where('c is not null');

        $query = $q->getQuery();


       // $result->setParameter('post', $post);
        try{
            return $query->getResult();
        }catch (\Exception $error){
            return false;
        }
    }


}
