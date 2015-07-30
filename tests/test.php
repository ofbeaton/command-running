<?php

require 'Running.php';
require 'RunningFilter.php';

$running = new \Ofbeaton\Command\Running();

$filter = new \Ofbeaton\Command\RunningFilter();
$filter->setProcess('/php\s+forever\.php/');

$cmdStart = microtime(true);
$pids = $running->getPids([$filter]);
$cmdStop = microtime(true);

// windows:  2.4 - 2.6 average
// linux:    0.03 - 0.04 average
echo 'list: '.round(($cmdStop - $cmdStart), 4).PHP_EOL;


foreach ($pids as $pid) {
    $cmdStart = microtime(true);
    $running->killPid($pid);
    $cmdStop = microtime(true);
  // windows: 0.28
  // linux:   0.0044
    echo 'Kill: '.round(($cmdStop - $cmdStart), 4).PHP_EOL;
}
