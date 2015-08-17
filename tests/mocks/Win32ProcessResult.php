<?php

namespace Ofbeaton\Command\Tests\Mocks;

/**
 * Class Win32ProcessResult
 * @since 2015-08-16
 */
class Win32ProcessResult
{

    /**
     * @var boolean
     * @since 2015-08-16
     */
    public $terminate = false;

    // @codingStandardsIgnoreStart
    /**
     * @return void
     * 
     * @since 2015-08-16
     */
    public function Terminate()
    {
        // @codingStandardsIgnoreEnd
        $this->terminate = true;
    }//end Terminate()
}//end class
