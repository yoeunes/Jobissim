<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Data\SearchData;
use App\Entity\Cvtheque;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Cvtheque|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cvtheque|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cvtheque[]    findAll()
 * @method Cvtheque[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CvthequeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Cvtheque::class);
        $this->paginator = $paginator;
    }

    public function findCvLikedByUser($user)
    {
        return $this->createQueryBuilder('e')
            ->join('e.likes', 'CvLike')
            ->andWhere('CvLike.user = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function pagination(): Query
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC')
            ->getQuery()
        ;
    }

    public function search($term)
    {
            return $this->createQueryBuilder('cvtheque')
            ->innerJoin('cvtheque.category', 'c')
            ->andWhere('c.name LIKE :lib')
            ->setParameter('lib', '%'.$term.'%')
            ->getQuery()
            ->execute();
    }

    
    public function search2($term)
    {
        return $this->createQueryBuilder('cvtheque')
            ->andWhere('cvtheque.lieu LIKE :lib')
            ->setParameter('lib', '%'.$term.'%')
            ->getQuery()
            ->execute();
    }




    /*filtres avancés*/

    /**
     * Récupère les produits en lien avec une recherche
     * @return PaginationInterface
     */
    public function findSearch(SearchData $search): PaginationInterface
    {
        $query = $this->getSearchQuery($search)->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            10
        );
    }

    /**
     * Récupère le prix minimum et maximum correspondant à une recherche
     * @return integer[]
     */
    public function findMinMax(SearchData $search): array
    {
        $results = $this->getSearchQuery($search, true)
            ->select('MIN(p.experience) as min', 'MAX(p.experience) as max')
            ->getQuery()
            ->getScalarResult();
        return [(int)$results[0]['min'], (int)$results[0]['max']];
    }

    private function getSearchQuery(SearchData $search, $ignorePrice = false): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('p')
            ->select('c', 'p')
            ->join('p.category', 'c');

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('p.nom LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }
        

        if (!empty($search->min) && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.experience >= :min')
                ->setParameter('min', $search->min);
        }

        if (!empty($search->max) && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.experience <= :max')
                ->setParameter('max', $search->max);
        }

        if (!empty($search->eligible)) {
            $query = $query
                ->andWhere('p.eligible = 1');
        }

        if (!empty($search->disponible)) {
            $query = $query
                ->andWhere('p.disponible = 1');
        }

        if (!empty($search->date)) {
            $query = $query
            ->andWhere('p.date = :date')
            ->setParameter('date', $search->date);
        }

        if (!empty($search->contrat)) {
            $query = $query
            ->andWhere('p.contrat = :contrat')
            ->setParameter('contrat', $search->contrat);
        }

        if (!empty($search->category)) {
            $query = $query
                ->andWhere('c.id IN (:category)')
                ->setParameter('category', $search->category);
        }

        return $query;
    }

 
}
