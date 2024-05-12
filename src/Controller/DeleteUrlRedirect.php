<?php

namespace EnjoysCMS\RedirectManage\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use EnjoysCMS\Module\Admin\AdminController;
use EnjoysCMS\RedirectManage\Entity\UrlRedirect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/admin/redirects/delete',
    name: 'redirects/delete',
    options: [
        'comment' => '[ADMIN] Удаление адреса перенаправления'
    ]
)]
class DeleteUrlRedirect extends AdminController
{
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws NotSupported
     * @throws NoResultException
     */
    public function __invoke(
        ServerRequestInterface $request,
        EntityManager $em,
        RedirectInterface $redirect
    ): ResponseInterface {
        $urlRedirect = $em->getRepository(UrlRedirect::class)->find(
            $request->getQueryParams()['id'] ?? 0
        ) ?? throw new NoResultException();

        $em->remove($urlRedirect);
        $em->flush();

        return $redirect->toRoute('redirects/manage');
    }
}
