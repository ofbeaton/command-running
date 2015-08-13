<?php

namespace Ofbeaton\Command\Tests;

use Ofbeaton\Command\Running;
use Ofbeaton\Command\RunningFilter;
use phpmock\phpunit\PHPMock;

/**
 * Class RunningTest
 * @since 2015-08-12
 */
class RunningTest extends \PHPUnit_Framework_TestCase
{
    const OS_WINDOWS = 'Windows';

    const OS_LINUX = 'Linux';

    use PHPMock;


    /**
     * @return void
     * @throws \RuntimeException Too many calls to exec().
     *
     * @since 2015-08-12
     */
    public function testClaimProcessWindows()
    {
        $uname = $this->getFunctionMock('\Ofbeaton\Command', 'php_uname');
        $uname->expects($this->once())->willReturnCallback(
            function ($mode) {
                if ($mode !== 's') {
                    throw new \RunetimeException('unexpected mode to php_uname');
                }

                return self::OS_WINDOWS;
            }
        );

        $exec = $this->getFunctionMock('\Ofbeaton\Command', 'exec');
        $exec->expects($this->exactly(2))->willReturnCallback(
            function ($command, &$output, &$returnVar) {
                static $pid = null;

                if ($pid === null && $command === 'tasklist /V /FO CSV /NH') {
                    $pid = 7212;
                    $output = file_get_contents(__DIR__.'/fixtures/windows_forever.txt');
                    $output = explode("\n", $output);
                    $returnVar = 0;
                } elseif ($pid === 7212 && $command === 'taskkill /PID 7212') {
                    $returnVar = 0;
                } else {
                    throw new \RuntimeException('unexpected call to exec');
                }
            }
        );

        $running = new Running();
        $filter = new RunningFilter();
        $filter->setProcess('/php\s+forever\.php/');

        $result = $running->claimProcess([$filter]);
        $this->assertTrue($result);
    }//end testClaimProcessWindows()


    /**
     * @return void
     * @throws \RuntimeException Too many calls to exec().
     *
     * @since 2015-08-12
     */
    public function testClaimProcessLinux()
    {
        $uname = $this->getFunctionMock('\Ofbeaton\Command', 'php_uname');
        $uname->expects($this->once())->willReturnCallback(
            function ($mode) {
                if ($mode !== 's') {
                    throw new \RunetimeException('unexpected mode to php_uname');
                }

                return self::OS_LINUX;
            }
        );

        $exec = $this->getFunctionMock('\Ofbeaton\Command', 'exec');
        $exec->expects($this->exactly(2))->willReturnCallback(
            function ($command, &$output, &$returnVar) {
                static $pid = null;

                if ($pid === null && $command === 'ps -Ao "%p,%U,%a" --no-headers') {
                    $pid = 14397;
                    $output = file_get_contents(__DIR__.'/fixtures/linux_forever.txt');
                    $output = explode("\n", $output);
                    $returnVar = 0;
                } elseif ($pid === 14397 && $command === 'kill -9 14397') {
                    $returnVar = 0;
                } else {
                    throw new \RuntimeException('unexpected call to exec: '.$command);
                }
            }
        );

        $running = new Running();
        $filter = new RunningFilter();
        $filter->setProcess('/php\s+forever\.php/');

        $result = $running->claimProcess([$filter]);
        $this->assertTrue($result);
    }//end testClaimProcessLinux()
}
