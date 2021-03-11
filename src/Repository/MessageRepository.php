<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }


    public function findUserMessages($user)
    {
        return $this->createQueryBuilder('m')
            ->Where('m.to_id = :val')
            ->andWhere('m.to_id != m.from_id')
            ->setParameter('val', $user)
            ->getQuery()
            ->getResult();
    }

    public function countMessages($user)
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m)')
            ->Where('m.to_id = :val')
            ->andWhere('m.to_id != m.from_id')
            ->andWhere('m.isRead = 0')
            ->setParameter('val', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function getDiscussion(User $sender, User $receiver)
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('m.from_id = :sender and m.to_id = :receiver')
            ->orWhere('m.from_id = :receiver and m.to_id = :sender')
            ->orderBy('m.createdAt', 'ASC')
            ->setParameters([
                'sender' => $sender,
                'receiver' => $receiver,
            ]);

        return $qb->getQuery()->getResult();
    }
}
