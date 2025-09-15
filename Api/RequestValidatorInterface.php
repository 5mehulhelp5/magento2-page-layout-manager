<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Api;

/**
 * Interface for validating if a request should allow entity-specific caching
 */
interface RequestValidatorInterface
{
    /**
     * Validate if the current request should allow entity-specific caching
     *
     * @param array<string, mixed> $parameters
     * @param string|null $defaultHandle
     * @param array<string, mixed> $context Additional context data
     * @return bool
     */
    public function isRequestAllowed(
        array $parameters = [],
        ?string $defaultHandle = null,
        array $context = []
    ): bool;
}