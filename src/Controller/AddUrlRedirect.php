<?php

namespace EnjoysCMS\RedirectManage\Controller;

use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Exception\ExceptionRule;
use Enjoys\Forms\Form;
use Enjoys\Forms\Rules;
use EnjoysCMS\ContentEditor\AceEditor\Ace;
use EnjoysCMS\Core\ContentEditor\ContentEditor;
use EnjoysCMS\Module\Admin\AdminController;
use EnjoysCMS\RedirectManage\Entity\UrlRedirect;
use EnjoysCMS\RedirectManage\RedirectType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: '/admin/redirects/add',
    name: 'redirects/add',
    options: [
        'comment' => '[ADMIN] Добавление адреса перенаправления'
    ]
)]
class AddUrlRedirect extends AbstractController
{
    /**
     * @throws ExceptionRule
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        EntityManager $em,
        ContentEditor $contentEditor
    ): ResponseInterface {
        $form = new Form();
        $form->setDefaults([
            'redirectParams' => 'url:'
        ]);
        $form->text('pattern', 'Старый URL')->addRule(Rules::REQUIRED);
        $form->text('replacement', 'New URL')->addRule(Rules::REQUIRED);

        $form->submit();

        if ($form->isSubmitted()) {
            $urlRedirect = new UrlRedirect();
            $urlRedirect->setPattern($this->request->getParsedBody()['pattern'] ?? null);
            $urlRedirect->setReplacement($this->request->getParsedBody()['replacement'] ?? null);
            $em->persist($urlRedirect);
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
