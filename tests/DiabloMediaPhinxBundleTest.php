<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Tests;

use DiabloMedia\PhinxBundle\DependencyInjection\DiabloMediaPhinxExtension;
use DiabloMedia\PhinxBundle\DiabloMediaPhinxBundle;
use PHPUnit\Framework\TestCase;

final class DiabloMediaPhinxBundleTest extends TestCase
{
    public function testBundleUsesExpectedContainerExtension(): void
    {
        $bundle = new DiabloMediaPhinxBundle();

        self::assertInstanceOf(DiabloMediaPhinxExtension::class, $bundle->getContainerExtension());
    }
}
