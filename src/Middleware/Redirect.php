<?php

declare(strict_types=1);

namespace EnjoysCMS\RedirectManage\Middleware;

use EnjoysCMS\RedirectManage\Entity\UrlRedirect;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Redirect implements MiddlewareInterface
{
    public function __construct(
        private readonly UrlRedirectRepository $repository,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    /**
     * Process a request and return a response.
     */
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();


        foreach ($this->repository->findBy(['active' => true]) as $urlRedirect) {
            if (!preg_match(sprintf('/%s/', $urlRedirect->getPattern()), $uri)) {
                continue;
            }

            $responseCode = $this->determineResponseCode($urlRedirect, $request);

            $location = preg_replace(sprintf('/%s/', $urlRedirect->getPattern()), $urlRedirect->getReplacement(), $uri);

            if ($urlRedirect->isInclQuery() && strlen($query) > 0) {
                $location .= '?' . $query;
            }

            return $this->responseFactory
                ->createResponse($responseCode)
                ->withAddedHeader('Location', $location);
        }

        return $handler->handle($request);
    }

    /**
     * Determine the response code according to the method and the permanent config
     */
    private function determineResponseCode(UrlRedirect $urlRedirect, ServerRequestInterface $request): int
    {
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'CONNECT', 'TRACE', 'OPTIONS'])) {
            return $urlRedirect->isPermanent() ? 301 : 302;
        }

        return $urlRedirect->isPermanent() ? 308 : 307;
    }
}
