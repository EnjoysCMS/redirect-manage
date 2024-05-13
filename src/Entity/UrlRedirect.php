<?php

namespace EnjoysCMS\RedirectManage\Entity;


use Doctrine\ORM\Mapping as ORM;
use EnjoysCMS\RedirectManage\RedirectType;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;

#[ORM\Entity(repositoryClass: UrlRedirectRepository::class)]
#[ORM\Table(name: 'redirects')]
class UrlRedirect
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 1000)]
    private string $oldUrl;

    #[ORM\Column(type: 'string', enumType: RedirectType::class)]
    private RedirectType $type;

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

    public function getType(): RedirectType
    {
        return $this->type;
    }

    public function setType(RedirectType $type): void
    {
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
