<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Test\Unit\Plugin\Framework\View\Result\Page;

use Hryvinskyi\PageLayoutManager\Api\LayoutHandleStrategyInterface;
use Hryvinskyi\PageLayoutManager\Plugin\Framework\View\Result\Page\LayoutHandleManager;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for LayoutHandleManager plugin
 */
class LayoutHandleManagerTest extends TestCase
{
    private LayoutHandleManager $layoutHandleManager;
    private LayoutHandleStrategyInterface|MockObject $layoutStrategyMock;
    private LoggerInterface|MockObject $loggerMock;
    private Page|MockObject $pageMock;

    protected function setUp(): void
    {
        $this->layoutStrategyMock = $this->createMock(LayoutHandleStrategyInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->pageMock = $this->createMock(Page::class);

        $this->layoutHandleManager = new LayoutHandleManager(
            $this->layoutStrategyMock,
            $this->loggerMock
        );
    }

    public function testAroundAddPageLayoutHandlesWhenStrategyAllows(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $entitySpecific = true;
        $expectedResult = true;

        $this->layoutStrategyMock->expects($this->once())
            ->method('shouldAllowEntityLayout')
            ->with($parameters, $defaultHandle, $entitySpecific)
            ->willReturn(true);

        $proceed = function ($params, $handle, $specific) use ($parameters, $defaultHandle, $entitySpecific, $expectedResult) {
            $this->assertSame($parameters, $params);
            $this->assertSame($defaultHandle, $handle);
            $this->assertSame($entitySpecific, $specific);
            return $expectedResult;
        };

        $result = $this->layoutHandleManager->aroundAddPageLayoutHandles(
            $this->pageMock,
            $proceed,
            $parameters,
            $defaultHandle,
            $entitySpecific
        );

        $this->assertSame($expectedResult, $result);
    }

    public function testAroundAddPageLayoutHandlesWhenStrategyBlocksEntitySpecific(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $entitySpecific = true;

        $this->layoutStrategyMock->expects($this->once())
            ->method('shouldAllowEntityLayout')
            ->with($parameters, $defaultHandle, $entitySpecific)
            ->willReturn(false);

        $proceed = function () {
            $this->fail('Proceed should not be called when entity-specific is blocked');
        };

        $result = $this->layoutHandleManager->aroundAddPageLayoutHandles(
            $this->pageMock,
            $proceed,
            $parameters,
            $defaultHandle,
            $entitySpecific
        );

        // Should return true to indicate the handles were "processed" (blocked)
        $this->assertTrue($result);
    }

    public function testAroundAddPageLayoutHandlesWhenStrategyBlocksNonEntitySpecific(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $entitySpecific = false;
        $expectedResult = false;

        $this->layoutStrategyMock->expects($this->once())
            ->method('shouldAllowEntityLayout')
            ->with($parameters, $defaultHandle, $entitySpecific)
            ->willReturn(false);

        $proceed = function ($params, $handle, $specific) use ($parameters, $defaultHandle, $entitySpecific, $expectedResult) {
            $this->assertSame($parameters, $params);
            $this->assertSame($defaultHandle, $handle);
            $this->assertSame($entitySpecific, $specific);
            return $expectedResult;
        };

        $result = $this->layoutHandleManager->aroundAddPageLayoutHandles(
            $this->pageMock,
            $proceed,
            $parameters,
            $defaultHandle,
            $entitySpecific
        );

        $this->assertSame($expectedResult, $result);
    }

    public function testAroundAddPageLayoutHandlesHandlesException(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';
        $entitySpecific = true;
        $exception = new \Exception('Test exception');
        $expectedResult = false;

        $this->layoutStrategyMock->expects($this->once())
            ->method('shouldAllowEntityLayout')
            ->with($parameters, $defaultHandle, $entitySpecific)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                'Error in LayoutHandleManager: Test exception',
                [
                    'parameters' => $parameters,
                    'defaultHandle' => $defaultHandle,
                    'entitySpecific' => $entitySpecific
                ]
            );

        $proceed = function ($params, $handle, $specific) use ($parameters, $defaultHandle, $entitySpecific, $expectedResult) {
            $this->assertSame($parameters, $params);
            $this->assertSame($defaultHandle, $handle);
            $this->assertSame($entitySpecific, $specific);
            return $expectedResult;
        };

        $result = $this->layoutHandleManager->aroundAddPageLayoutHandles(
            $this->pageMock,
            $proceed,
            $parameters,
            $defaultHandle,
            $entitySpecific
        );

        $this->assertSame($expectedResult, $result);
    }
}