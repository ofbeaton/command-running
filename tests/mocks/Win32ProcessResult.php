<?php

namespace Ofbeaton\Command\Tests\Mocks;

/**
 * Class Win32ProcessResult
 * @since 2015-08-16
 */
class Win32ProcessResult
{
    public $terminate = false;

    // @codingStandardsIgnoreStart
    public function Terminate()
    {
        // @codingStandardsIgnoreEnd
        $this->terminate = true;
    }//end Terminate()
}//end class
