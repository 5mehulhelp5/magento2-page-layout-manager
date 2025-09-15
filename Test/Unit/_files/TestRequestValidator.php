<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Test\Unit\_files;

use Hryvinskyi\PageLayoutManager\Api\RequestValidatorInterface;

/**
 * Test implementation of RequestValidatorInterface for unit testing
 */
class TestRequestValidator implements RequestValidatorInterface
{
    /**
     * @param bool $shouldAllow
     */
    public function __construct(
        private readonly bool $shouldAllow = true
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function isRequestAllowed(
        array $parameters = [],
        ?string $defaultHandle = null,
        array $context = []
    ): bool {
        return $this->shouldAllow;
    }
}