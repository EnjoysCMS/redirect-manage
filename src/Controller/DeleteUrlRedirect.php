<?php

namespace EnjoysCMS\RedirectManage\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use EnjoysCMS\Core\Routing\Annotation\Route;
use EnjoysCMS\Module\Admin\AdminController;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use Psr\Http\Message\ResponseInterface;

#[Route(
    path: '/admin/redirects/delete',
    name: 'redirects/delete',
    comment: 'Удаление адреса перенаправления'
)]
class DeleteUrlRedirect extends AbstractController
{
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws NotSupported
     * @throws NoResultException
     */
    public function __invoke(
        UrlRedirectRepository $repository,
        EntityManager $em,
    ): ResponseInterface {
        $urlRedirect = $repository->find(
            $this->request->getQueryParams()['id'] ?? 0
        ) ?? throw new NoResultException();

        $em->remove($urlRedirect);
        $em->flush();

        return $this->redirect->toRoute('@redirect_manage_list');
    }
}
