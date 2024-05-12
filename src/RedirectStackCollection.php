<?php

declare(strict_types=1);

namespace EnjoysCMS\RedirectManage;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use EnjoysCMS\RedirectManage\Entity\UrlRedirect;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use RuntimeException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class RedirectStackCollection
{
    private Collection $collection;

    public function __construct(
        private UrlRedirectRepository $repository,
        private UrlGeneratorInterface $urlGenerator
    ) {
        $this->collection = new ArrayCollection();
    }

    public function getCollection(): Collection
    {
        return $this->collection->isEmpty() ? $this->buildAndGetCollection() : $this->collection;
    }

    private function buildAndGetCollection(): Collection
    {
        foreach ($this->repository->findBy(['active' => true]) as $urlRedirect) {
            try {
                if ($urlRedirect->getType() === UrlRedirect::TO_ROUTE) {
                    $this->collection->set(
                        $urlRedirect->getOldUrl(),
                        $this->urlGenerator->generate(
                            $urlRedirect->getRedirectParams()['route'] ?? throw new RuntimeException(),
                            $urlRedirect->getRedirectParams()['params'] ?? []
                        )
                    );
                    continue;
                }

                if ($urlRedirect->getType() === UrlRedirect::TO_URL) {
                    $this->collection->set(
                        $urlRedirect->getOldUrl(),
                        $urlRedirect->getRedirectParams()['url'] ?? throw new RuntimeException()
                    );
                }
            } catch (RouteNotFoundException|RuntimeException) {
                continue;
            }
        }
        return $this->collection;
    }
}
