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
use EnjoysCMS\Core\ContentEditor\ContentEditor;
use EnjoysCMS\Core\Routing\Annotation\Route;
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
    ): ResponseInterface {
        $urlRedirect = $repository->find(
            $this->request->getQueryParams()['id'] ?? 0
        ) ?? throw new NoResultException();

        $form = new Form();
        $form->setDefaults([
            'pattern' => $urlRedirect->getPattern(),
            'replacement' => Yaml::dump($urlRedirect->getReplacement()),
            'active' => [(int)$urlRedirect->isActive()],
        ]);
        $form->checkbox('active')
            ->setPrefixId('active')
            ->addClass(
                'custom-switch custom-switch-off-danger custom-switch-on-success',
                Form::ATTRIBUTES_FILLABLE_BASE
            )
            ->fill([1 => 'Включен?']);

        $form->text('pattern', 'Старый URL');
        $form->text('replacement', 'New URL');


        $form->submit();

        if ($form->isSubmitted()) {
            $urlRedirect->setPattern($this->request->getParsedBody()['pattern'] ?? null);
            $urlRedirect->setReplacement($this->request->getParsedBody()['replacement'] ?? null);
            $urlRedirect->setActive((bool)($this->request->getParsedBody()['active'] ?? false));

            $em->flush();
            return $this->redirect->toRoute('@redirect_manage_list');
        }
        $renderer = $this->adminConfig->getRendererForm();
        $renderer->setForm($form);

        return $this->response(
            $this->twig->render('@redirect-manage/form.twig', [
                'title' => 'Добавить redirect',
                'form' => $renderer
            ])
        );
    }
}
