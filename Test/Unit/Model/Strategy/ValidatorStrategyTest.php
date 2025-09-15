<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Test\Unit\Model\Strategy;

use Hryvinskyi\PageLayoutManager\Api\LayoutHandleConfigInterface;
use Hryvinskyi\PageLayoutManager\Api\LayoutDecisionInterface;
use Hryvinskyi\PageLayoutManager\Api\ModificationResultInterface;
use Hryvinskyi\PageLayoutManager\Api\ParameterModifierInterface;
use Hryvinskyi\PageLayoutManager\Api\RequestValidatorInterface;
use Hryvinskyi\PageLayoutManager\Model\LayoutDecision;
use Hryvinskyi\PageLayoutManager\Model\LayoutDecisionFactory;
use Hryvinskyi\PageLayoutManager\Model\Strategy\ValidatorStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ValidatorStrategy
 */
class ValidatorStrategyTest extends TestCase
{
    private ValidatorStrategy $validatorStrategy;
    private LayoutHandleConfigInterface|MockObject $configMock;
    private LayoutDecisionFactory|MockObject $layoutDecisionFactoryMock;
    private LayoutDecision|MockObject $layoutDecisionMock;
    private RequestValidatorInterface|MockObject $validator1Mock;
    private RequestValidatorInterface|MockObject $validator2Mock;
    private ParameterModifierInterface|MockObject $parameterModifier1Mock;
    private ParameterModifierInterface|MockObject $parameterModifier2Mock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(LayoutHandleConfigInterface::class);
        $this->layoutDecisionFactoryMock = $this->createPartialMock(
            LayoutDecisionFactory::class,
            ['create']
        );
        $this->layoutDecisionMock = $this->createMock(LayoutDecision::class);
        $this->validator1Mock = $this->createMock(RequestValidatorInterface::class);
        $this->validator2Mock = $this->createMock(RequestValidatorInterface::class);
        $this->parameterModifier1Mock = $this->createMock(ParameterModifierInterface::class);
        $this->parameterModifier2Mock = $this->createMock(ParameterModifierInterface::class);

        $this->validatorStrategy = new ValidatorStrategy(
            $this->configMock,
            $this->layoutDecisionFactoryMock,
            [
                'validator1' => $this->validator1Mock,
                'validator2' => $this->validator2Mock,
            ],
            [
                'validator1' => $this->parameterModifier1Mock,
                'validator2' => $this->parameterModifier2Mock,
            ]
        );
    }

    public function testShouldAllowEntityLayoutAlwaysAllowsNonEntitySpecific(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $expectedDecision = $this->createMock(LayoutDecisionInterface::class);

        $this->layoutDecisionFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'allowed' => true,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ])
            ->willReturn($expectedDecision);

        $decision = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: false
        );

        $this->assertSame($expectedDecision, $decision);
    }

    public function testShouldAllowEntityLayoutWhenEntityLayoutsAreEnabled(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $expectedDecision = $this->createMock(LayoutDecisionInterface::class);

        $this->configMock->expects($this->once())
            ->method('isEntityLayoutEnabled')
            ->willReturn(true);

        $this->layoutDecisionFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'allowed' => true,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ])
            ->willReturn($expectedDecision);

        $decision = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: true
        );

        $this->assertSame($expectedDecision, $decision);
    }

    public function testShouldAllowEntityLayoutWhenDisabledAndValidatorsDisabled(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $expectedDecision = $this->createMock(LayoutDecisionInterface::class);

        $this->configMock->expects($this->once())
            ->method('isEntityLayoutEnabled')
            ->willReturn(false);

        $this->configMock->expects($this->once())
            ->method('isOnlySpecificValidatorsEnabled')
            ->willReturn(false);

        $this->layoutDecisionFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'allowed' => false,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ])
            ->willReturn($expectedDecision);

        $decision = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: true
        );

        $this->assertSame($expectedDecision, $decision);
    }

    public function testShouldAllowEntityLayoutWhenValidatorAllows(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $expectedContext = ['entity_specific' => true];
        $expectedDecision = $this->createMock(LayoutDecisionInterface::class);

        $this->configMock->expects($this->once())
            ->method('isEntityLayoutEnabled')
            ->willReturn(false);

        $this->configMock->expects($this->once())
            ->method('isOnlySpecificValidatorsEnabled')
            ->willReturn(true);

        $this->validator1Mock->expects($this->once())
            ->method('isRequestAllowed')
            ->with($parameters, $defaultHandle, $expectedContext)
            ->willReturn(false);

        $this->validator2Mock->expects($this->once())
            ->method('isRequestAllowed')
            ->with($parameters, $defaultHandle, $expectedContext)
            ->willReturn(true);

        // Mock modification result since validator2 returns true
        $modificationResult = $this->createMock(ModificationResultInterface::class);
        $modificationResult->expects($this->once())
            ->method('getParameters')
            ->willReturn($parameters);
        $modificationResult->expects($this->once())
            ->method('getDefaultHandle')
            ->willReturn($defaultHandle);

        // Parameter modifier2 is called because validator2 returned true
        $this->parameterModifier2Mock->expects($this->once())
            ->method('modifyParameters')
            ->with($parameters, $defaultHandle, $expectedContext)
            ->willReturn($modificationResult);

        $this->layoutDecisionFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'allowed' => true,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ])
            ->willReturn($expectedDecision);

        $decision = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: true
        );

        $this->assertSame($expectedDecision, $decision);
    }

    public function testShouldAllowEntityLayoutWhenNoValidatorAllows(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $expectedContext = ['entity_specific' => true];
        $expectedDecision = $this->createMock(LayoutDecisionInterface::class);

        $this->configMock->expects($this->once())
            ->method('isEntityLayoutEnabled')
            ->willReturn(false);

        $this->configMock->expects($this->once())
            ->method('isOnlySpecificValidatorsEnabled')
            ->willReturn(true);

        $this->validator1Mock->expects($this->once())
            ->method('isRequestAllowed')
            ->with($parameters, $defaultHandle, $expectedContext)
            ->willReturn(false);

        $this->validator2Mock->expects($this->once())
            ->method('isRequestAllowed')
            ->with($parameters, $defaultHandle, $expectedContext)
            ->willReturn(false);

        $this->layoutDecisionFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'allowed' => false,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ])
            ->willReturn($expectedDecision);

        $decision = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: true
        );

        $this->assertSame($expectedDecision, $decision);
    }

    public function testShouldAllowEntityLayoutReturnsImmutableDecision(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $originalParameters = $parameters;
        $originalDefaultHandle = $defaultHandle;
        $expectedDecision = $this->createMock(LayoutDecisionInterface::class);

        $this->layoutDecisionFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'allowed' => true,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ])
            ->willReturn($expectedDecision);

        $decision = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: false
        );

        // Verify that original parameters are unchanged (pure function)
        $this->assertEquals($originalParameters, $parameters);
        $this->assertEquals($originalDefaultHandle, $defaultHandle);

        // Verify the expected decision is returned
        $this->assertSame($expectedDecision, $decision);
    }

    public function testValidatorWithParameterModifierAppliesModifications(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $modifiedParameters = ['test' => 'modified_value', 'new_param' => 'added'];
        $modifiedDefaultHandle = 'modified_handle';
        $expectedContext = ['entity_specific' => true];
        $expectedDecision = $this->createMock(LayoutDecisionInterface::class);

        $this->configMock->expects($this->once())
            ->method('isEntityLayoutEnabled')
            ->willReturn(false);

        $this->configMock->expects($this->once())
            ->method('isOnlySpecificValidatorsEnabled')
            ->willReturn(true);

        // Validator1 allows the request
        $this->validator1Mock->expects($this->once())
            ->method('isRequestAllowed')
            ->with($parameters, $defaultHandle, $expectedContext)
            ->willReturn(true);

        // Mock modification result
        $modificationResult = $this->createMock(ModificationResultInterface::class);
        $modificationResult->expects($this->once())
            ->method('getParameters')
            ->willReturn($modifiedParameters);
        $modificationResult->expects($this->once())
            ->method('getDefaultHandle')
            ->willReturn($modifiedDefaultHandle);

        // Parameter modifier1 is called because validator1 returned true
        $this->parameterModifier1Mock->expects($this->once())
            ->method('modifyParameters')
            ->with($parameters, $defaultHandle, $expectedContext)
            ->willReturn($modificationResult);

        // Factory creates decision with modified parameters
        $this->layoutDecisionFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'allowed' => true,
                'parameters' => $modifiedParameters,
                'defaultHandle' => $modifiedDefaultHandle
            ])
            ->willReturn($expectedDecision);

        $decision = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: true
        );

        $this->assertSame($expectedDecision, $decision);
    }

    public function testValidatorWithoutParameterModifierUsesOriginalParameters(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $expectedContext = ['entity_specific' => true];
        $expectedDecision = $this->createMock(LayoutDecisionInterface::class);

        // Create strategy without parameter modifiers
        $strategyWithoutModifiers = new ValidatorStrategy(
            $this->configMock,
            $this->layoutDecisionFactoryMock,
            ['validator1' => $this->validator1Mock],
            [] // No parameter modifiers
        );

        $this->configMock->expects($this->once())
            ->method('isEntityLayoutEnabled')
            ->willReturn(false);

        $this->configMock->expects($this->once())
            ->method('isOnlySpecificValidatorsEnabled')
            ->willReturn(true);

        $this->validator1Mock->expects($this->once())
            ->method('isRequestAllowed')
            ->with($parameters, $defaultHandle, $expectedContext)
            ->willReturn(true);

        // Factory creates decision with original parameters (no modifier)
        $this->layoutDecisionFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'allowed' => true,
                'parameters' => $parameters,
                'defaultHandle' => $defaultHandle
            ])
            ->willReturn($expectedDecision);

        $decision = $strategyWithoutModifiers->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: true
        );

        $this->assertSame($expectedDecision, $decision);
    }

    public function testConstructorValidatesValidatorTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validator "invalid" must implement');

        new ValidatorStrategy(
            $this->configMock,
            $this->layoutDecisionFactoryMock,
            ['invalid' => new \stdClass()]
        );
    }

    public function testConstructorValidatesParameterModifierTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter modifier "invalid" must implement');

        new ValidatorStrategy(
            $this->configMock,
            $this->layoutDecisionFactoryMock,
            [],
            ['invalid' => new \stdClass()]
        );
    }
}