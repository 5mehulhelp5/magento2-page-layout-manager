<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Api;

/**
 * Interface for layout decision value objects
 */
interface LayoutDecisionInterface
{
    /**
     * Whether the layout should be allowed
     *
     * @return bool
     */
    public function isAllowed(): bool;

    /**
     * Get the (potentially modified) parameters
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array;

    /**
     * Get the (potentially modified) default handle
     *
     * @return string|null
     */
    public function getDefaultHandle(): ?string;
}