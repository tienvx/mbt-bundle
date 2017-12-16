<?php

namespace Tienvx\Bundle\MbtBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;

class TaskRepository extends EntityRepository
{
    /**
     * Get all tasks
     *
     * @param integer $currentPage The current page (passed from controller)
     *
     * @return Paginator
     */
    public function getAllTasks($currentPage = 1)
    {
        $query = $this->createQueryBuilder('t')
            ->orderBy('t.created', 'DESC')
            ->getQuery();

        $paginator = $this->paginate($query, $currentPage);

        return $paginator;
    }

    /**
     * Paginate results from query
     *
     * @param Query              $query DQL Query Object
     * @param integer            $page  Current page (defaults to 1)
     * @param integer            $limit The total number per page (defaults to 5)
     *
     * @return Paginator
     */
    public function paginate($query, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($query);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }
}
