<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Api;

/**
 * Strategy interface for layout handle decision making
 */
interface LayoutHandleStrategyInterface
{
    /**
     * Decide whether to allow entity-specific layout handles
     *
     * @param array<string, mixed> $parameters
     * @param string|null $defaultHandle
     * @param bool $entitySpecific
     * @return bool
     */
    public function shouldAllowEntityLayout(
        array $parameters = [],
        ?string $defaultHandle = null,
        bool $entitySpecific = true
    ): bool;
}