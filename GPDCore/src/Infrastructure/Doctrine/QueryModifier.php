<?php

declare(strict_types=1);

namespace GPDCore\Infrastructure\Doctrine;

use Doctrine\ORM\QueryBuilder;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\QueryModifierInterface;
use GraphQL\Type\Definition\ResolveInfo;

class QueryModifier implements QueryModifierInterface
{
    private ?callable $decorator = null;

    public function __construct(?callable $decorator = null)
    {
        $this->decorator = $decorator;
    }

    public static function create(callable $decorator): self
    {
        return new self($decorator);
    }

    public function __invoke(QueryBuilder $qb, mixed $root, array $args, AppContextInterface $context, ResolveInfo $info): QueryBuilder
    {
        if ($this->decorator === null) {
            return $qb;
        }

        return ($this->decorator)($qb, $root, $args, $context, $info);
    }

    public function setDecorator(?callable $decorator): self
    {
        $this->decorator = $decorator;

        return $this;
    }

    public function getDecorator(): ?callable
    {
        return $this->decorator;
    }
}
