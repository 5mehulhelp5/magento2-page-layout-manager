<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Api;

/**
 * Interface for modifying layout parameters and handles
 */
interface ParameterModifierInterface
{
    /**
     * Modify layout parameters and default handle
     *
     * @param array<string, mixed> $parameters
     * @param string|null $defaultHandle
     * @param array<string, mixed> $context Additional context data
     * @return ModificationResultInterface
     */
    public function modifyParameters(
        array $parameters,
        ?string $defaultHandle,
        array $context = []
    ): ModificationResultInterface;
}