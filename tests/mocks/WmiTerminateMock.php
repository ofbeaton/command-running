<?php

namespace Ofbeaton\Command\Tests\Mocks;

/**
 * Class ComMock
 * @since 2015-08-16
 */
class WmiTerminateMock
{

    /**
     * @var array
     * @since 2015-08-16
     */
    public $results = [];

    // @codingStandardsIgnoreStart
    /**
     * @param string $query To run.
     *
     * @return array
     *
     * @since 2015-08-16
     */
    public function ExecQuery($query) {
        // @codingStandardsIgnoreEnd

        if ($query === "SELECT * FROM Win32_Process WHERE ParentProcessId = '7212'") {
            $process1 = new Win32ProcessResult();
            $this->results[] = $process1;
            return $this->results;
        }

        throw new \RuntimeException('Invalid query: '.$query);
    }//end ExecQuery()
}//end class
