<?php

namespace EnjoysCMS\RedirectManage\Controller;

use EnjoysCMS\Core\Routing\Annotation\Route;
use EnjoysCMS\RedirectManage\Config;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use Psr\Http\Message\ResponseInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: '/admin/redirects',
    name: '@redirect_manage_list',
    options: [
        'admin' => 'Управление переадресациями'
    ],
    comment: 'Менеджер переадресаций'
)]
class ManageUrlRedirect extends AbstractController
{

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function __invoke(UrlRedirectRepository $repository, Config $config): ResponseInterface
    {

        $this->breadcrumbs->setLastBreadcrumb('Управление переадресациями');

        return $this->response(
            $this->twig->render('@redirect-manage/manage.twig', [
                '_title' => 'Управление переадресациями - RedirectManage | Admin | ' . $this->setting->get(
                        'sitename'
                    ),
                'redirects' => $repository->findBy([], ['id' => 'desc'])
            ])
        );
    }
}
