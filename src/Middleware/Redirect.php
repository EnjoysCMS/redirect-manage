<?php

declare(strict_types=1);

namespace EnjoysCMS\RedirectManage\Middleware;

use EnjoysCMS\RedirectManage\RedirectCollection;
use EnjoysCMS\RedirectManage\RedirectStackCollection;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Redirect implements MiddlewareInterface
{

    private bool $permanent = true;

    private bool $query = true;

    /**
     * @var string[]
     */
    private array $method = ['GET'];

    private readonly ResponseFactoryInterface $responseFactory;

    public function __construct(
        private readonly UrlRedirectRepository $repository,
        ResponseFactoryInterface $responseFactory = null
    ) {
        $this->responseFactory = $responseFactory ?: Factory::getResponseFactory();
    }

    /**
     * Whether return a permanent redirect.
     */
    public function permanent(bool $permanent = true): self
    {
        $this->permanent = $permanent;
        return $this;
    }

    /**
     * Whether include the query to search the url
     */
    public function query(bool $query = true): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Configure the methods in which make the redirection
     * @param string[] $method
     * @return Redirect
     */
    public function method(array $method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Process a request and return a response.
     */
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();

        if ($this->query && strlen($query) > 0) {
            $uri .= '?' . $query;
        }

        foreach ($this->repository->findBy(['active' => true]) as $urlRedirect) {


            if (!preg_match(sprintf('/%s/', $urlRedirect->getPattern()), $uri)) {
                continue;
            }

            $responseCode = $this->determineResponseCode($request);
            return $this->responseFactory->createResponse($responseCode)
                ->withAddedHeader('Location', preg_replace(sprintf('/%s/', $urlRedirect->getPattern()), $urlRedirect->getReplacement(), $uri));
        }

        return $handler->handle($request);
    }

    /**
     * Determine the response code according with the method and the permanent config
     */
    private function determineResponseCode(ServerRequestInterface $request): int
    {
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'CONNECT', 'TRACE', 'OPTIONS'])) {
            return $this->permanent ? 301 : 302;
        }

        return $this->permanent ? 308 : 307;
    }
}
