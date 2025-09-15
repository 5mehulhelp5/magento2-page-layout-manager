<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Model\Config;

use Hryvinskyi\PageLayoutManager\Api\LayoutHandleConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Layout handle configuration implementation
 */
class LayoutHandleConfig implements LayoutHandleConfigInterface
{
    private const XML_PATH_ENABLED = 'hryvinskyi_page_layout/entity_specific/enabled';
    private const XML_PATH_ENABLED_ONLY_SPECIFIC = 'hryvinskyi_page_layout/entity_specific/enabled_only_specific';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isEntityLayoutEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function isOnlySpecificValidatorsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED_ONLY_SPECIFIC);
    }
}