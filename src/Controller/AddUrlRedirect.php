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
use EnjoysCMS\Core\ContentEditor\ContentEditor;
use EnjoysCMS\Core\Routing\Annotation\Route;
use EnjoysCMS\RedirectManage\Entity\UrlRedirect;
use Psr\Http\Message\ResponseInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: '/admin/redirects/add',
    name: '@redirect_manage_add',
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
        $form->text('pattern', 'Pattern')
            ->setDescription(
                <<<HTML
                    <b>Cheatsheet:</b><br>
                    ASCII SLUG: <code>[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*</code><br>
                    CATCH ALL: <code>.+</code><br>
                    DATE YYYY-MM-DD: <code>[0-9]{4}-(?:0[1-9]|1[012])-(?:0[1-9]|[12][0-9]|(?&lt!02-)3[01])</code><br>
                    DIGITS: <code>[0-9]+</code><br>
                    POSITIVE_INT: <code>[1-9][0-9]*</code><br>
                    UID_BASE32: <code>[0-9A-HJKMNP-TV-Z]{26}</code><br>
                    UID_BASE58: <code>[1-9A-HJ-NP-Za-km-z]{22}</code><br>
                    UID_RFC4122: <code>[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}</code><br>
                    ULID: <code>[0-7][0-9A-HJKMNP-TV-Z]{25}</code><br>
                    UUID: <code>[0-9a-f]{8}-[0-9a-f]{4}-[13-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}</code><br>
                    UUID_V1: <code>[0-9a-f]{8}-[0-9a-f]{4}-1[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}</code><br>
                    UUID_V3: <code>[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}</code><br>
                    UUID_V4: <code>[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}</code><br>
                    UUID_V5: <code>[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}</code><br>
                    UUID_V6: <code>[0-9a-f]{8}-[0-9a-f]{4}-6[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}</code><br>
                    UUID_V7: <code>[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}</code><br>
                    UUID_V8: <code>[0-9a-f]{8}-[0-9a-f]{4}-8[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}</code><br>
                HTML
            )->addRule(Rules::REQUIRED);
        $form->text('replacement', 'Replacement')->addRule(Rules::REQUIRED);
        $form->textarea('description', 'Описание');
        $form->submit();

        if ($form->isSubmitted()) {
            $urlRedirect = new UrlRedirect();
            $urlRedirect->setPattern($this->request->getParsedBody()['pattern'] ?? '');
            $urlRedirect->setReplacement($this->request->getParsedBody()['replacement'] ?? '');
            $urlRedirect->setDescription($this->request->getParsedBody()['description'] ?? null);
            $em->persist($urlRedirect);
            $em->flush();
            return $this->redirect->toRoute('@redirect_manage_list');
        }

        $renderer = $this->adminConfig->getRendererForm();
        $renderer->setForm($form);

        $this->breadcrumbs
            ->add('@redirect_manage_list', 'Управление переадресациями')
            ->setLastBreadcrumb('Добавление правила переадресации');

        return $this->response(
            $this->twig->render('@redirect-manage/form.twig', [
                '_title' => 'Добавление правила переадресации - RedirectManage | Admin | ' . $this->setting->get(
                        'sitename'
                    ),
                'form' => $renderer
            ])
        );
    }
}
