<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Command\DoctorCommand;
use PHPUnit\Framework\TestCase;

final class DoctorCommandTest extends TestCase
{
    public function testDoctorRunsAndReturnsSuccess(): void
    {
        ob_start();
        $code = DoctorCommand::run();
        $output = ob_get_clean();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('Doctor result: SUCCESS', $output);
    }
}
