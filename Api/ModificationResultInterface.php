<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Api;

/**
 * Interface for parameter modification results
 */
interface ModificationResultInterface
{
    /**
     * Get the modified parameters
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array;

    /**
     * Get the modified default handle
     *
     * @return string|null
     */
    public function getDefaultHandle(): ?string;

    /**
     * Check if any modifications were made
     *
     * @return bool
     */
    public function hasModifications(): bool;
}