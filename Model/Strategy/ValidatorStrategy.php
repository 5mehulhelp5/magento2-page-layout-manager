<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Model\Strategy;

use Hryvinskyi\PageLayoutManager\Api\LayoutHandleConfigInterface;
use Hryvinskyi\PageLayoutManager\Api\LayoutHandleStrategyInterface;
use Hryvinskyi\PageLayoutManager\Api\RequestValidatorInterface;

/**
 * Strategy that uses validators to determine if entity layout handles should be allowed
 */
class ValidatorStrategy implements LayoutHandleStrategyInterface
{
    /**
     * @param LayoutHandleConfigInterface $config
     * @param array<RequestValidatorInterface> $requestValidators
     */
    public function __construct(
        private readonly LayoutHandleConfigInterface $config,
        private readonly array $requestValidators = []
    ) {
        // Validate that all validators implement the correct interface
        foreach ($requestValidators as $key => $validator) {
            if (!$validator instanceof RequestValidatorInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Validator "%s" must implement %s, %s given',
                        $key,
                        RequestValidatorInterface::class,
                        get_class($validator)
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function shouldAllowEntityLayout(
        array $parameters = [],
        ?string $defaultHandle = null,
        bool $entitySpecific = true
    ): bool {
        // Always allow non-entity-specific handles
        if (!$entitySpecific) {
            return true;
        }

        // If entity layouts are enabled, skip module functionality (Magento works as default)
        if ($this->config->isEntityLayoutEnabled()) {
            return true;
        }

        // Entity layouts are disabled, check if we should use validators
        if ($this->config->isOnlySpecificValidatorsEnabled()) {
            // Use validators to determine if request should be allowed
            $context = [
                'entity_specific' => $entitySpecific
            ];

            // If any validator allows it, return true
            foreach ($this->requestValidators as $validator) {
                if ($validator->isRequestAllowed($parameters, $defaultHandle, $context)) {
                    return true;
                }
            }

            return false; // No validator allowed this request
        }

        // If validators are not enabled, block all entity-specific layouts
        return false;
    }
}