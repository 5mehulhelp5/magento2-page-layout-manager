<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Test\Unit\Model\Strategy;

use Hryvinskyi\PageLayoutManager\Api\LayoutHandleConfigInterface;
use Hryvinskyi\PageLayoutManager\Api\RequestValidatorInterface;
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
    private RequestValidatorInterface|MockObject $validator1Mock;
    private RequestValidatorInterface|MockObject $validator2Mock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(LayoutHandleConfigInterface::class);
        $this->validator1Mock = $this->createMock(RequestValidatorInterface::class);
        $this->validator2Mock = $this->createMock(RequestValidatorInterface::class);

        $this->validatorStrategy = new ValidatorStrategy(
            $this->configMock,
            [
                'validator1' => $this->validator1Mock,
                'validator2' => $this->validator2Mock,
            ]
        );
    }

    public function testShouldAllowEntityLayoutAlwaysAllowsNonEntitySpecific(): void
    {
        $result = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: ['test' => 'value'],
            defaultHandle: 'test_handle',
            entitySpecific: false
        );

        $this->assertTrue($result);
    }

    public function testShouldAllowEntityLayoutWhenEntityLayoutsAreEnabled(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEntityLayoutEnabled')
            ->willReturn(true);

        $result = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: ['test' => 'value'],
            defaultHandle: 'test_handle',
            entitySpecific: true
        );

        $this->assertTrue($result);
    }

    public function testShouldAllowEntityLayoutWhenDisabledAndValidatorsDisabled(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEntityLayoutEnabled')
            ->willReturn(false);

        $this->configMock->expects($this->once())
            ->method('isOnlySpecificValidatorsEnabled')
            ->willReturn(false);

        $result = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: ['test' => 'value'],
            defaultHandle: 'test_handle',
            entitySpecific: true
        );

        $this->assertFalse($result);
    }

    public function testShouldAllowEntityLayoutWhenValidatorAllows(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $expectedContext = ['entity_specific' => true];

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

        $result = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: true
        );

        $this->assertTrue($result);
    }

    public function testShouldAllowEntityLayoutWhenNoValidatorAllows(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $expectedContext = ['entity_specific' => true];

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

        $result = $this->validatorStrategy->shouldAllowEntityLayout(
            parameters: $parameters,
            defaultHandle: $defaultHandle,
            entitySpecific: true
        );

        $this->assertFalse($result);
    }

    public function testConstructorValidatesValidatorTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validator "invalid" must implement');

        new ValidatorStrategy(
            $this->configMock,
            ['invalid' => new \stdClass()]
        );
    }
}