<?php

namespace EnjoysCMS\RedirectManage\Controller;

use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Exception\ExceptionRule;
use Enjoys\Forms\Form;
use EnjoysCMS\Core\Routing\Annotation\Route;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: '/admin/redirects/switch',
    name: '@redirect_manage_switch',
    comment: 'Включение/отключение редиректов'
)]
class SwitchUrlRedirect extends AbstractController
{

    /**
     * @throws NoResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(
        UrlRedirectRepository $repository,
        EntityManager $em,
    ): ResponseInterface {
        $urlRedirect = $repository->find(
            $this->request->getQueryParams()['id'] ?? 0
        ) ?? throw new NoResultException();

            $urlRedirect->setActive(!$urlRedirect->isActive());
            $em->flush();

            return $this->redirect->toRoute('@redirect_manage_list');

    }
}
