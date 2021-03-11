<?php

namespace App\Repository;

use App\Classe\Search;
use Doctrine\ORM\Query;
use App\Data\SearchData;
use App\Entity\Formation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


/**
 * @method Formation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Formation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Formation[]    findAll()
 * @method Formation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Formation::class);
        $this->paginator = $paginator;
    }

    public function search($term)
    {
        return $this->createQueryBuilder('formation')
            ->andWhere('formation.nom LIKE :lib')
            ->setParameter('lib', '%'.$term.'%')
            ->getQuery()
            ->execute();
    }


    
    public function search2($term)
    {
        return $this->createQueryBuilder('formation')
            ->andWhere('formation.lieu LIKE :lib')
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

    

    public function findAuteur($value)
    {
        return $this->createQueryBuilder('f')
            ->join('f.auteur', 'toId')
            ->andWhere('auteur.formation = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }


    public function findFormationsLikedByUser($user)
    {
        return $this->createQueryBuilder('f')
            ->join('f.likes', 'formationLike')
            ->andWhere('formationLike.user = :id')
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
            ->select('MIN(p.prix) as min', 'MAX(p.prix) as max')
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
                ->andWhere('p.prix >= :min')
                ->setParameter('min', $search->min);
        }

        if (!empty($search->max) && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.prix <= :max')
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

        if (!empty($search->category)) {
            $query = $query
                ->andWhere('c.id IN (:category)')
                ->setParameter('category', $search->category);
        }

        return $query;
    }
}
