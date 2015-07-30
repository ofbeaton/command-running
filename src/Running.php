<?php

namespace Ofbeaton\Command;

class Running
{


    const OS_LINUX = 'linux';

    const OS_WINDOWS = 'windows';

    protected $supportedOs = [
                              self::OS_LINUX,
                              self::OS_WINDOWS,
    ];

    protected $os = null;

    protected $osRaw = null;

    protected $pidFile = null;


    public function __construct($pidFile = null, $unknownOs = self::OS_LINUX)
    {
        $this->detectOs($unknownOs);
        $this->pidFile = $pidFile;
    }//end __construct()


    public function getOs()
    {
        return $this->os;
    }//end getOs()


    public function isWindows()
    {
        if ($this->os === self::OS_WINDOWS) {
            return true;
        }

        return false;
    }//end isWindows()


    public function isLinux()
    {
        if ($this->os === self::OS_LINUX) {
            return true;
        }

        return false;
    }//end isLinux()


    protected function detectOs($unknownOs = self::OS_LINUX)
    {
      /*
          http://stackoverflow.com/questions/738823/possible-values-for-php-os
          some possible values:
          CYGWIN_NT-5.1
          Darwin
          FreeBSD
          HP-UX
          IRIX64
          Linux
          NetBSD
          OpenBSD
          SunOS
          Unix
          WIN32
          WINNT
          Windows
      */
        $this->osRaw = php_uname('s');

        if ($this->osRaw === 'Linux') {
            $this->os = self::OS_LINUX;
        } elseif (stripos($this->osRaw, 'win') === 0) {
            $this->os = 'windows';
        } elseif ($unknownOs !== null && in_array($unknownOs, $this->supportedOs) === true) {
            $this->os = $unknownOs;
        } else {
            throw new \InvalidArgumentException('unknownOs `'.$unknownOs.'` is not in list of supportedOs `'.implode(', ', $this->supportedOs));
        }
    }//end detectOs()


    protected function transformFilters(array $filters)
    {
        foreach ($filters as $key => $value) {
            if (is_string($value) === true) {
                $filters[$key] = new RunningFilter();
                $filters[$key]->setProcess($value);
            } elseif (is_array($value) === true && count($array) === 2) {
                $filters[$key] = new RunningFilter();
                $filters[$key]->setProcess($value[0]);
                $filters[$key]->setOs($value[1]);
            } elseif (($value instanceof RunningFilter) === false) {
                throw new \InvalidArgumentException('filter `'.$value.'` is invalid.');
            }
        }

        return $filters;
    }//end transformFilters()


    /**
     * @param array   $filters    Of strings representing filters on the process list.
     * @param boolean $ignoreCase Ignores case in filters.
     *
     * @return array of pids matching the filters.
     */
    public function getPids(array $filters, $ignoreCase = true)
    {
        $filters = $this->transformFilters($filters);

        if ($this->isWindows()) {
          // on windows, this command is very slow, and it's filters DO NOT speed it up
            $cmd = 'tasklist /V /FO CSV /NH';
        } elseif ($this->isLinux()) {
          // on linux
            $cmd = 'ps -Ao "%p,%U,%a" --no-headers';
        } else {
            throw new \RuntimeException('os `'.$this->osRaw.'` is not supported by method `getPids`');
        }

        exec($cmd, $output, $returnVal);

        if ($returnVal !== 0) {
            throw new \RuntimeException('Command `'.$cmd.' did not execute successfully');
        }

        $pids = [];

        foreach ($output as $line) {
            if ($this->isWindows() === true) {
                $splitLine = trim($line, '"');
                $splitLine = explode('","', $splitLine, 9);
              /*
                  0 "Image Name",
                  1 "PID",
                  2 "Session Name",
                  3 "Session#",
                  4 "Mem Usage",
                  5 "Status",
                  6 "User Name",
                  7 "CPU Time",
                  8 "Window Title"
               */
                $details = [
                'os' => $this->os,
                'user' => $splitLine[6],
                'process' => $splitLine[0].' '.$splitLine[8],
                'pid' => $splitLine[1],
                ];

            } elseif ($this->isLinux() === true) {
                $splitLine = explode(',', $line, 3);
              /*
                  0 pid (padded)
                  1 user (padded)
                  2 command
              */
                $details = [
                'os' => $this->os,
                'user' => trim($splitLine[1]),
                'process' => $splitLine[2],
                'pid' => trim($splitLine[0]),
                ];
            }//end if

            $ok = true;
            foreach ($filters as $filter) {
                if ($filter->isOk($details) === false) {
                    $ok = false;
                    break;
                }
            }

            if ($ok === false) {
                continue;
            }

            $pids[] = intval($details['pid']);
        }//end foreach

        return $pids;
    }//end getPids()



    /**
     * @param integer $pid Pid.
     *
     * @return boolean Success.
     *
     * @since 2015-07-29
     */
    public function killPid($pid, $file = null)
    {
        if ($pid === false) {
            return false;
        }

        if ($this->isWindows() === true) {
            $cmd = 'taskkill /PID '.$pid;
        } elseif ($this->isLinux() === true) {
            // on linux
            $cmd = 'kill -9 '.$pid;
        } else {
            throw new \RuntimeException('os `'.$this->osRaw.'` is not supported by method `killPid`');
        }

        exec($cmd, $output, $returnVal);

        if ($returnVal !== 0) {
            throw new \RuntimeException('Command `'.$cmd.' did not execute successfully');
        }

        if ($file !== null && file_exists($file) === true) {
            unlink($file);
        }

        return true;
    }//end killPid()


    public function getPidFromFile($file)
    {
        if (file_exists($file) === true) {
            $pid = file_get_contents($file);
            return $pid;
        }

        return false;
    }//end getPidFromFile()


    public function killPidFromFile($file)
    {
        $result = $this->killPid($this->getPidFromFile($file), $file);
        return $result;
    }//end killPidFromFile()


    public function claimPidFile($file, $kill = false)
    {
        $pid = $this->getPidFromFile($file);
        if ($pid !== false) {
            if ($kill === true) {
                $this->killPid($pid);
            } else {
                return false;
            }
        }

        $result = file_put_contents($file, getmypid());

        if ($result === false) {
            return false;
        }

        return true;
    }//end claimPidFile()
}//end class
