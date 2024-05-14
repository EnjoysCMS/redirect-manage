<?php

declare(strict_types=1);


namespace EnjoysCMS\RedirectManage;


use Doctrine\Common\Collections\ArrayCollection;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RedirectCollection extends ArrayCollection implements RedirectCollectionInterface
{
    public function __construct(
        private readonly UrlRedirectRepository $repository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($this->getCollection());
    }

    private function getCollection(): array
    {
        $collection = [];
        foreach ($this->repository->findBy(['active' => true]) as $urlRedirect) {
            try {
                if ($urlRedirect->getType() === RedirectType::ROUTE) {
                    $collection[$urlRedirect->getOldUrl()] =
                        $this->urlGenerator->generate(
                            $urlRedirect->getRedirectParams()['route'] ?? throw new \RuntimeException(),
                            $urlRedirect->getRedirectParams()['params'] ?? []
                        );
                    continue;
                }

                if ($urlRedirect->getType() === RedirectType::URL) {
                    $collection[$urlRedirect->getOldUrl()] =
                        $urlRedirect->getRedirectParams()['url'] ?? throw new \RuntimeException();
                }
            } catch (RouteNotFoundException|\RuntimeException) {
                continue;
            }
        }
        return $collection;
    }
}
