<?php

declare(strict_types=1);

namespace EnjoysCMS\RedirectManage\Repository;

use Doctrine\ORM\EntityRepository;
use EnjoysCMS\RedirectManage\Entity\UrlRedirect;

/**
 * @method UrlRedirect|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlRedirect|null findOneBy(array $criteria, array $orderBy = null)
 * @method list<UrlRedirect> findAll()
 * @method list<UrlRedirect> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlRedirectRepository extends EntityRepository
{
}
