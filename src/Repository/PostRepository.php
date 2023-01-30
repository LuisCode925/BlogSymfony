<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllPostsDashboard(){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT post.id, post.title, post.thumb, post.content, post.created_at, user.names, user.last_names
            FROM App\Entity\Post post
            JOIN post.User user'
        ); # ->getResult(); no se necesita el resultado en el paginador
        return $query;
    }


    public function searchPostsLike(string $search){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            "SELECT post.id, post.title, post.thumb, post.content, post.created_at, user.names, user.last_names
            FROM App\Entity\Post post 
            JOIN post.User user
            WHERE post.title LIKE CONCAT('%', :search, '%')"
        )->setParameter('search', $search); # ->getResult(); no se necesita el resultado en el paginador
        return $query;
    }

    public function findPost(int $id){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT post.title, post.thumb, post.created_at, post.content, user.names, user.last_names
            FROM App\Entity\Post post
            JOIN post.User user
            WHERE post.id = :id'
        )->setParameter('id', $id)->getResult(); 
        return $query;
    }

    public function findAllPostsDashboardByUser(int $user):array {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT post.id, post.title, post.thumb, post.created_at
            FROM App\Entity\Post post
            WHERE post.user_id > :user
            ORDER BY post.user_id ASC'
        )->setParameter('user', $user);

        // returns an array of Posts objects
        return $query->getResult();
    }


//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
