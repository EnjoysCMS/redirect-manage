<?php

namespace EnjoysCMS\RedirectManage\Entity;


use Doctrine\ORM\Mapping as ORM;
use EnjoysCMS\RedirectManage\Repository\UrlRedirectRepository;

#[ORM\Entity(repositoryClass: UrlRedirectRepository::class)]
#[ORM\Table(name: 'redirect_manage_list')]
class UrlRedirect
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 1000)]
    private string $pattern;

    #[ORM\Column(type: 'string', length: 1000)]
    private string $replacement;


    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $permanent = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $inclQuery = true;

    #[ORM\Column(type: 'boolean', options: [
        'default' => true
    ])]
    private bool $active = true;


    public function getId(): int
    {
        return $this->id;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getReplacement(): string
    {
        return $this->replacement;
    }

    public function setReplacement(string $replacement): void
    {
        $this->replacement = $replacement;
    }

    public function isPermanent(): bool
    {
        return $this->permanent;
    }

    public function setPermanent(bool $permanent): void
    {
        $this->permanent = $permanent;
    }

    public function isInclQuery(): bool
    {
        return $this->inclQuery;
    }

    public function setInclQuery(bool $inclQuery): void
    {
        $this->inclQuery = $inclQuery;
    }
}
