<?php

namespace EnjoysCMS\RedirectManage\Entity;


use Doctrine\ORM\Mapping as ORM;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;

#[ORM\Entity(repositoryClass: UrlRedirectRepository::class)]
#[ORM\Table(name: 'redirects')]
class UrlRedirect
{

    public const string TO_ROUTE = 'route';
    public const string TO_URL = 'url';


    private const array TYPES = [
        self::TO_ROUTE,
        self::TO_URL
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 1000)]
    private string $oldUrl;

    #[ORM\Column(type: 'string')]
    private string $type;

    #[ORM\Column(type: 'json')]
    private array $redirectParams;

    #[ORM\Column(type: 'boolean', options: [
        'default' => true
    ])]
    private bool $active = true;


    public function getId(): int
    {
        return $this->id;
    }

    public function getOldUrl(): string
    {
        return $this->oldUrl;
    }

    public function setOldUrl(string $oldUrl): void
    {
        $this->oldUrl = $oldUrl;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Type %s not  supported. Allowed types is: %s', $type, implode(',', self::TYPES))
            );
        }
        $this->type = $type;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getRedirectParams(): array
    {
        return $this->redirectParams;
    }

    public function setRedirectParams(array $redirectParams): void
    {
        $this->redirectParams = $redirectParams;
    }
}
