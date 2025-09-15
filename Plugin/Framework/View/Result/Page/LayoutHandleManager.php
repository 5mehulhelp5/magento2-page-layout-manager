<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Plugin\Framework\View\Result\Page;

use Hryvinskyi\PageLayoutManager\Api\LayoutHandleStrategyInterface;
use Magento\Framework\View\Result\Page;
use Psr\Log\LoggerInterface;

/**
 * Configurable layout handle management plugin
 */
class LayoutHandleManager
{
    /**
     * @param LayoutHandleStrategyInterface $layoutStrategy
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LayoutHandleStrategyInterface $layoutStrategy,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Control entity-specific page layout handles based on configuration
     *
     * @param Page $subject
     * @param callable $proceed
     * @param array<string, mixed> $parameters
     * @param string|null $defaultHandle
     * @param bool $entitySpecific
     * @return bool
     * @noinspection PhpUnusedParameterInspection
     */
    public function aroundAddPageLayoutHandles(
        Page $subject,
        callable $proceed,
        array $parameters = [],
        ?string $defaultHandle = null,
        bool $entitySpecific = true
    ): bool {
        try {
            $decision = $this->layoutStrategy->shouldAllowEntityLayout(
                $parameters,
                $defaultHandle,
                $entitySpecific
            );

            if (!$decision->isAllowed() && $entitySpecific) {
                // Block entity-specific handles to prevent cache bloat
                return true;
            }

            // Use potentially modified parameters from the decision
            return $proceed(
                $decision->getParameters(),
                $decision->getDefaultHandle(),
                $entitySpecific
            );
        } catch (\Exception $e) {
            $this->logger->error('Error in LayoutHandleManager: ' . $e->getMessage(), [
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle,
                'entitySpecific' => $entitySpecific
            ]);

            // Fallback to original behavior on error
            return $proceed($parameters, $defaultHandle, $entitySpecific);
        }
    }
}