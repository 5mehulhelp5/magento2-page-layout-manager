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
use Hryvinskyi\PageLayoutManager\Api\LayoutDecisionInterface;
use Hryvinskyi\PageLayoutManager\Api\ParameterModifierInterface;
use Hryvinskyi\PageLayoutManager\Api\RequestValidatorInterface;
use Hryvinskyi\PageLayoutManager\Model\LayoutDecisionFactory;

/**
 * Strategy that uses validators to determine if entity layout handles should be allowed
 * and parameter modifiers to modify parameters when allowed
 */
class ValidatorStrategy implements LayoutHandleStrategyInterface
{
    /**
     * @param LayoutHandleConfigInterface $config
     * @param LayoutDecisionFactory $layoutDecisionFactory
     * @param array<RequestValidatorInterface> $requestValidators
     * @param array<ParameterModifierInterface> $parameterModifiers
     */
    public function __construct(
        private readonly LayoutHandleConfigInterface $config,
        private readonly LayoutDecisionFactory $layoutDecisionFactory,
        private readonly array $requestValidators = [],
        private readonly array $parameterModifiers = []
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

        // Validate that all parameter modifiers implement the correct interface
        foreach ($parameterModifiers as $key => $modifier) {
            if (!$modifier instanceof ParameterModifierInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Parameter modifier "%s" must implement %s, %s given',
                        $key,
                        ParameterModifierInterface::class,
                        get_class($modifier)
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
    ): LayoutDecisionInterface {
        // Always allow non-entity-specific handles
        if (!$entitySpecific) {
            return $this->layoutDecisionFactory->create([
                'allowed' => true,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ]);
        }

        // If entity layouts are enabled, skip module functionality (Magento works as default)
        if ($this->config->isEntityLayoutEnabled()) {
            return $this->layoutDecisionFactory->create([
                'allowed' => true,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ]);
        }

        // Entity layouts are disabled, check if we should use validators
        if ($this->config->isOnlySpecificValidatorsEnabled()) {
            $context = [
                'entity_specific' => $entitySpecific
            ];

            // Check each validator and if one allows, apply the corresponding parameter modifier
            foreach ($this->requestValidators as $validatorKey => $validator) {
                if ($validator->isRequestAllowed($parameters, $defaultHandle, $context)) {
                    // Validator allowed - now apply parameter modifier with same key if exists
                    $finalParameters = $parameters;
                    $finalDefaultHandle = $defaultHandle;

                    if (isset($this->parameterModifiers[$validatorKey])) {
                        $modifier = $this->parameterModifiers[$validatorKey];
                        $modificationResult = $modifier->modifyParameters($parameters, $defaultHandle, $context);
                        $finalParameters = $modificationResult->getParameters();
                        $finalDefaultHandle = $modificationResult->getDefaultHandle();
                    }

                    return $this->layoutDecisionFactory->create([
                        'allowed' => true,
                        'parameters' => $finalParameters,
                        'defaultHandle' => $finalDefaultHandle
                    ]);
                }
            }

            // No validator allowed this request
            return $this->layoutDecisionFactory->create([
                'allowed' => false,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ]);
        }

        // If validators are not enabled, block all entity-specific layouts
        return $this->layoutDecisionFactory->create([
            'allowed' => false,
            'parameters' => $parameters,
            'defaultHandle' => $defaultHandle
        ]);
    }
}