<?php

namespace EnjoysCMS\RedirectManage\Controller;

use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Exception\ExceptionRule;
use Enjoys\Forms\Form;
use Enjoys\Forms\Rules;
use EnjoysCMS\ContentEditor\AceEditor\Ace;
use EnjoysCMS\Module\Admin\AdminController;
use EnjoysCMS\Module\Admin\Config;
use EnjoysCMS\RedirectManage\Entity\UrlRedirect;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

#[Route(
    path: '/admin/redirects/edit',
    name: 'redirects/edit',
    options: [
        'comment' => '[ADMIN] Редактирование адреса перенаправления'
    ]
)]
class EditUrlRedirect extends AdminController
{
    /**
     * @throws NoResultException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotSupported
     * @throws ExceptionRule
     */
    public function __invoke(
        ServerRequestInterface $request,
        UrlRedirectRepository $repository,
        EntityManager $em,
        Config $adminConfig
    ): ResponseInterface {
        $urlRedirect = $repository->find(
            $request->getQueryParams()['id'] ?? 0
        ) ?? throw new NoResultException();

        $form = new Form();
        $form->setDefaults([
            'oldUrl' => $urlRedirect->getOldUrl(),
            'type' => $urlRedirect->getType(),
            'redirectParams' => Yaml::dump($urlRedirect->getRedirectParams()),
            'active' => [(int)$urlRedirect->isActive()],
        ]);
        $form->checkbox('active')
            ->setPrefixId('active')
            ->addClass(
                'custom-switch custom-switch-off-danger custom-switch-on-success',
                Form::ATTRIBUTES_FILLABLE_BASE
            )
            ->fill([1 => 'Включен?']);

        $form->text('oldUrl', 'Старый URL');
        $form->select('type', 'Тип')
            ->fill([
                UrlRedirect::TO_URL => 'Url',
                UrlRedirect::TO_ROUTE => 'Route'
            ]);
        $form->textarea('redirectParams', 'Параметры перенаправления')->addRule(
            Rules::CALLBACK,
            'RedirectParams is not valid',
            function () use ($request) {
                $data = Yaml::parse($request->getParsedBody()['redirectParams'] ?? '');
                return match ($request->getParsedBody()['type']) {
                    UrlRedirect::TO_URL => array_key_exists('url', $data),
                    UrlRedirect::TO_ROUTE => array_key_exists('route', $data),
                    default => false,
                };
            }
        );
        $form->submit();

        if ($form->isSubmitted()) {
            $urlRedirect->setOldUrl($request->getParsedBody()['oldUrl'] ?? null);
            $urlRedirect->setType($request->getParsedBody()['type'] ?? null);
            $urlRedirect->setRedirectParams(Yaml::parse($request->getParsedBody()['redirectParams'] ?? ''));
            $urlRedirect->setActive((bool)($request->getParsedBody()['active'] ?? false));

            $em->flush();
            return $this->redirect->toRoute('@redirect_manage_list');
        }
        $renderer->setForm($form);

        return $this->response(
            $this->twig->render('@redirect-manage/form.twig', [
                'title' => 'Добавить redirect',
                'editorEmbedCode' => $contentEditor
                    ->withConfig([
                        Ace::class => [
                            'template' => $_ENV['ROOT_PATH'].'/template/ace-editor-yaml.twig'
                        ]
                    ])
                    ->setSelector('#redirectParams')
                    ->getEmbedCode(),
                'form' => $renderer
            ])
        );
    }
}
