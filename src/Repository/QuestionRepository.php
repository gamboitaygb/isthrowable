<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }

//    /**
//     * @return Question[] Returns an array of Question objects
//     */

    public function findQuestions()
    {
        return $this->createQueryBuilder('q')
            ->orderBy('q.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySlug($value,$time): ?Question
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :val')
            ->andWhere('p.time = :time')
            ->setParameter('val', $value)
            ->setParameter('time', $time)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?Question
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllQuestions()
    {
        return $this->createQueryBuilder('q')
            ->orderBy('q.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }


    public function findAllQuestionsByUser($user)
    {
        return $this->createQueryBuilder('q')
            ->where('q.user=:val')
            ->orderBy('q.id', 'DESC')
            ->setParameter('val',$user)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findComments($question)
    {
        $em = $this->getEntityManager();
        $result = $em->createQuery('
                  SELECT u FROM App:Comments u
                  WHERE u.question = :question');
        $result->setParameter('question', $question);
        try{
            return $result->getResult();
        }catch (\Exception $error){
            return false;
        }
    }
}
