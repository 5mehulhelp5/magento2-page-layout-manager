<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Model;

use Hryvinskyi\PageLayoutManager\Api\LayoutDecisionInterface;

/**
 * Value object representing a layout decision with optional parameter modifications
 */
class LayoutDecision implements LayoutDecisionInterface
{
    /**
     * @param bool $allowed
     * @param array<string, mixed> $parameters
     * @param string|null $defaultHandle
     */
    public function __construct(
        private readonly bool $allowed,
        private readonly array $parameters,
        private readonly ?string $defaultHandle
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    /**
     * @inheritDoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultHandle(): ?string
    {
        return $this->defaultHandle;
    }
}