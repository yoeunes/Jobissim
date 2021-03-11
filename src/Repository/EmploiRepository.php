<?php

namespace App\Repository;

use App\Entity\Emploi;
use Doctrine\ORM\Query;
use App\Data\SearchData;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Emploi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Emploi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Emploi[]    findAll()
 * @method Emploi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmploiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Emploi::class);
        $this->paginator = $paginator;
    }

    public function search($term)
    {
        return $this->createQueryBuilder('emploi')
            ->andWhere('emploi.nom LIKE :lib')
            ->setParameter('lib', '%'.$term.'%')
            ->getQuery()
            ->execute();
    }

    public function search2($term)
    {
        return $this->createQueryBuilder('emploi')
            ->andWhere('emploi.lieu LIKE :lib')
            ->setParameter('lib', '%'.$term.'%')
            ->getQuery()
            ->execute();
    }

    
    public function actu()
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->execute()
            ->setMaxResults(4);
    }


    public function findEmploisLikedByUser($user)
    {
        return $this->createQueryBuilder('e')
            ->join('e.likes', 'emploiLike')
            ->andWhere('emploiLike.user = :id')
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
            ->select('MIN(p.salaire) as min', 'MAX(p.salaire) as max')
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
                ->andWhere('p.salaire >= :min')
                ->setParameter('min', $search->min);
        }

        if (!empty($search->max) && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.salaire <= :max')
                ->setParameter('max', $search->max);
        }

        if (!empty($search->eligible)) {
            $query = $query
                ->andWhere('p.eligible = 1');
        }

        if (!empty($search->date)) {
            $query = $query
            ->andWhere('p.date = :date')
            ->setParameter('date', $search->date);
        }

        if (!empty($search->contrat)) {
            $query = $query
            ->andWhere('p.typedecontrat = :contrat')
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
