<?php

namespace EnjoysCMS\RedirectManage\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Exception\ExceptionRule;
use Enjoys\Forms\Form;
use Enjoys\Forms\Interfaces\RendererInterface;
use Enjoys\Forms\Rules;
use EnjoysCMS\ContentEditor\AceEditor\Ace;
use EnjoysCMS\Module\Admin\AdminController;
use EnjoysCMS\RedirectManage\Entity\UrlRedirect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

#[Route(
    path: '/admin/redirects/add',
    name: 'redirects/add',
    options: [
        'comment' => '[ADMIN] Добавление адреса перенаправления'
    ]
)]
class AddUrlRedirect extends AdminController
{
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws ExceptionRule
     */
    public function __invoke(
        ServerRequestInterface $request,
        EntityManager $em,
        RendererInterface $renderer,
    ): ResponseInterface {
        $form = new Form();
        $form->setDefaults([
            'redirectParams' => 'url:'
        ]);
        $form->text('oldUrl', 'Старый URL')->addRule(Rules::REQUIRED);
        $form->select('type', 'Тип')
            //  ->addAttribute(AttributeFactory::create('onchange', 'setTemplateType(this.value, "redirectParams")'))
            ->fill([
                UrlRedirect::TO_URL => 'Url',
                UrlRedirect::TO_ROUTE => 'Route'
            ]);
        $form->textarea('redirectParams', 'Параметры перенаправления')
            ->addRule(Rules::CALLBACK, 'RedirectParams is not valid', function () use ($request) {
                $data = Yaml::parse($request->getParsedBody()['redirectParams'] ?? '');
                return match ($request->getParsedBody()['type']) {
                    UrlRedirect::TO_URL => array_key_exists('url', $data),
                    UrlRedirect::TO_ROUTE => array_key_exists('route', $data),
                    default => false,
                };
            });
        $form->submit();

        if ($form->isSubmitted()) {
            $urlRedirect = new UrlRedirect();
            $urlRedirect->setOldUrl($request->getParsedBody()['oldUrl'] ?? null);
            $urlRedirect->setType($request->getParsedBody()['type'] ?? null);
            $urlRedirect->setRedirectParams(Yaml::parse($request->getParsedBody()['redirectParams'] ?? ''));
            $em->persist($urlRedirect);
            $em->flush();
            return $redirect->toRoute('redirects/manage');
        }
        $renderer->setForm($form);

        return $this->responseText(
            $this->view('@redirect-manage/form.twig', [
                'title' => 'Добавить redirect',
                'editorEmbedCode' => $contentEditor
                    ->withConfig([
                        Ace::class => [
                            'template' => $_ENV['ROOT_PATH'] . '/template/agro/app/redirects/ace-editor-yaml.twig'
                        ]
                    ])
                    ->setSelector('#redirectParams')
                    ->getEmbedCode(),
                'form' => $renderer
            ])
        );
    }
}
