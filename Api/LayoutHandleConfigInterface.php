<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Api;

/**
 * Configuration interface for layout handle management
 */
interface LayoutHandleConfigInterface
{
    /**
     * Check if entity-specific layout handles are enabled
     *
     * @return bool
     */
    public function isEntityLayoutEnabled(): bool;

    /**
     * Check if only specific validators should be used
     * When enabled, only requests that pass validator checks are allowed
     * When disabled, all requests are allowed when entity-specific handles are enabled
     *
     * @return bool
     */
    public function isOnlySpecificValidatorsEnabled(): bool;
}