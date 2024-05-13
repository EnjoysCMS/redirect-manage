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
use Enjoys\Forms\Rules;
use EnjoysCMS\ContentEditor\AceEditor\Ace;
use EnjoysCMS\Core\ContentEditor\ContentEditor;
use EnjoysCMS\Core\Routing\Annotation\Route;
use EnjoysCMS\RedirectManage\RedirectType;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: '/admin/redirects/edit',
    name: 'redirects/edit',
    comment: 'Редактирование адреса перенаправления'
)]
class EditUrlRedirect extends AbstractController
{

    /**
     * @throws DependencyException
     * @throws ExceptionRule
     * @throws LoaderError
     * @throws NoResultException
     * @throws NotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        UrlRedirectRepository $repository,
        EntityManager $em,
        ContentEditor $contentEditor
    ): ResponseInterface {
        $urlRedirect = $repository->find(
            $this->request->getQueryParams()['id'] ?? 0
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
                RedirectType::URL->value => 'Url',
                RedirectType::ROUTE->value => 'Route'
            ]);
        $form->textarea('redirectParams', 'Параметры перенаправления')->addRule(
            Rules::CALLBACK,
            'RedirectParams is not valid',
            function () {
                $data = Yaml::parse($this->request->getParsedBody()['redirectParams'] ?? '');
                return match ($this->request->getParsedBody()['type'] ?? '') {
                    RedirectType::URL->value => array_key_exists('url', $data),
                    RedirectType::ROUTE->value => array_key_exists('route', $data),
                    default => false,
                };
            }
        );
        $form->submit();

        if ($form->isSubmitted()) {
            $urlRedirect->setOldUrl($this->request->getParsedBody()['oldUrl'] ?? null);
            $urlRedirect->setType(RedirectType::from($this->request->getParsedBody()['type'] ?? null));
            $urlRedirect->setRedirectParams(Yaml::parse($this->request->getParsedBody()['redirectParams'] ?? ''));
            $urlRedirect->setActive((bool)($this->request->getParsedBody()['active'] ?? false));

            $em->flush();
            return $this->redirect->toRoute('@redirect_manage_list');
        }
        $renderer = $this->adminConfig->getRendererForm();
        $renderer->setForm($form);

        return $this->response(
            $this->twig->render('@redirect-manage/form.twig', [
                'title' => 'Добавить redirect',
                'editorEmbedCode' => $contentEditor
                    ->withConfig([
                        Ace::class => [
                            'template' => __DIR__ . '/../../template/ace-editor-yaml.twig'
                        ]
                    ])
                    ->setSelector('#redirectParams')
                    ->getEmbedCode(),
                'form' => $renderer
            ])
        );
    }
}
