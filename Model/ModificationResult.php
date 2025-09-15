<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Model;

use Hryvinskyi\PageLayoutManager\Api\ModificationResultInterface;

/**
 * Value object representing parameter modification results
 */
class ModificationResult implements ModificationResultInterface
{
    /**
     * @param array<string, mixed> $parameters
     * @param string|null $defaultHandle
     * @param array<string, mixed> $originalParameters
     * @param string|null $originalDefaultHandle
     */
    public function __construct(
        private readonly array $parameters,
        private readonly ?string $defaultHandle,
        private readonly array $originalParameters = [],
        private readonly ?string $originalDefaultHandle = null
    ) {
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

    /**
     * @inheritDoc
     */
    public function hasModifications(): bool
    {
        return $this->parameters !== $this->originalParameters
            || $this->defaultHandle !== $this->originalDefaultHandle;
    }
}