<?php

namespace Ofbeaton\Command;

/**
 * Class Running
 * @since 2015-07-30
 */
class Running
{

    /**
     * @since 2015-07-30
     */
    const OS_LINUX = 'linux';

    /**
     * @since 2015-07-30
     */
    const OS_WINDOWS = 'windows';

    /**
     * @var array
     * @since 2015-07-30
     */
    protected $supportedOs = [
        self::OS_LINUX,
        self::OS_WINDOWS,
    ];

    /**
     * @var null
     * @since 2015-07-30
     */
    protected $os = null;

    /**
     * @var null
     * @since 2015-07-30
     */
    protected $osRaw = null;

    /**
     * @var null
     * @since 2015-07-30
     */
    protected $pidFile = null;

    /**
     * @var \COM
     * @since 2015-08-15
     */
    protected $com = null;


    /**
     * @param string $pidFile   Filename and path to PID file.
     * @param string $unknownOs OS to use if detected an unknown OS.
     *
     * @since 2015-07-30
     */
    public function __construct($pidFile = null, $unknownOs = self::OS_LINUX)
    {
        $this->pidFile = $pidFile;
        $this->detectOs($unknownOs);
    }//end __construct()


    /**
     * @return string OS
     *
     * @since 2015-07-30
     */
    public function getOs()
    {
        return $this->os;
    }//end getOs()


    /**
     * @return boolean
     *
     * @since 2015-07-30
     */
    public function isWindows()
    {
        if ($this->os === self::OS_WINDOWS) {
            return true;
        }

        return false;
    }//end isWindows()


    /**
     * @param object $com The \COM interface.
     *
     * @return $this
     *
     * @since 2015-08-16
     */
    public function setCom($com)
    {
        $this->com = $com;
        return $this;
    }//end setCom()


    /**
     * @return boolean
     *
     * @since 2015-07-30
     */
    public function isLinux()
    {
        if ($this->os === self::OS_LINUX) {
            return true;
        }

        return false;
    }//end isLinux()


    /**
     * @param string|null $unknownOs OS to use if OS is not supported.
     *
     * @return void
     * @throws \InvalidArgumentException OS is not supported and no unknown OS specified.
     *
     * @since 2015-07-30
     */
    protected function detectOs($unknownOs = self::OS_LINUX)
    {
        /*
            Http://stackoverflow.com/questions/738823/possible-values-for-php-os
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
            $error = 'unknownOs `'.$unknownOs.'` is not in list of supportedOs `'.implode(', ', $this->supportedOs);
            throw new \InvalidArgumentException($error);
        }
    }//end detectOs()


    /**
     * @param array $filters Filters.
     *
     * @return array
     * @throws \InvalidArgumentException Filter value invalid.
     *
     * @since 2015-07-30
     */
    protected function transformFilters(array $filters)
    {
        foreach ($filters as $key => $value) {
            if (is_string($value) === true) {
                $filters[$key] = new RunningFilter();
                $filters[$key]->setCommand($value);
            } elseif (is_array($value) === true && count($value) === 2) {
                $filters[$key] = new RunningFilter();
                $filters[$key]->setCommand($value[0]);
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
     * @throws \RuntimeException OS not supported.
     *
     * @since 2015-07-30
     */
    public function getPids(array $filters, $ignoreCase = true)
    {
        $filters = $this->transformFilters($filters);

        $method = 'getPids'.ucfirst($this->os);
        if (method_exists($this, $method) === false) {
            throw new \RuntimeException('os `'.$this->osRaw.'` is not supported by method `getPids`');
        }

        $pids = $this->$method($filters, $ignoreCase);

        return $pids;
    }//end getPids()


    /**
     * @param array   $filters    Of strings representing filters on the process list.
     * @param boolean $ignoreCase Ignores case in filters.
     *
     * @return array of pids matching the filters.
     * @throws \RuntimeException OS not supported.
     * @throws \RuntimeException Could not retrieve PID list.
     *
     * @since 2015-08-16
     */
    protected function getPidsWindows(array $filters, $ignoreCase = true)
    {
        /*
            Should note that WMI GetOwner() retval parameters do not seem to work, so we can't use WMI for this yet
            WMI also requires the COM_DOTNET windows php extension.
            we might be able to use wmic command, wmic process get processid,parentprocessid,commandline /format:rawxml
            but we would also need to do a wmic call GetOwner for EVERY process. This is not cool.
            we could also combine both to get the username,
                maybe tasklist can be made fast if only getting pid and username?
            get-process is also powershell only, so won't work with exec,
                though we might be able to pipe through the 'powershell' command.
        */

        // on windows, this command is very slow, and it's filters DO NOT speed it up
        $cmd = 'tasklist /V /FO CSV /NH';

        exec($cmd, $output, $returnVal);

        if ($returnVal !== 0) {
            throw new \RuntimeException('Command `'.$cmd.' did not execute successfully');
        }

        $pids = [];

        foreach ($output as $line) {
            // if we don't skip this, we hang
            if ($line === '') {
                continue;
            }

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
                'command' => $splitLine[0].' '.$splitLine[8],
                'pid' => intval($splitLine[1]),
                'group' => intval($splitLine[1]), // fake it
            ];

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

            $pids[$details['pid']] = $details;
        }//end foreach

        return $pids;
    }//end getPidsWindows()


    /**
     * @param array   $filters    Of strings representing filters on the process list.
     * @param boolean $ignoreCase Ignores case in filters.
     *
     * @return array of pids matching the filters.
     * @throws \RuntimeException OS not supported.
     * @throws \RuntimeException Could not retrieve PID list.
     *
     * @since 2015-08-16
     */
    protected function getPidsLinux(array $filters, $ignoreCase = true)
    {
        $cmd = 'ps -Ao "%p,%r,%U,%a" --no-headers';

        exec($cmd, $output, $returnVal);

        if ($returnVal !== 0) {
            throw new \RuntimeException('Command `'.$cmd.' did not execute successfully');
        }

        $pids = [];

        foreach ($output as $line) {
            // if we don't skip this, we hang
            if ($line === '') {
                continue;
            }

            $splitLine = explode(',', $line, 4);

            /*
                0 pid (padded)
                1 group (padded)
                2 user (padded)
                3 command
            */

            $details = [
                'os' => $this->os,
                'pid' => intval(trim($splitLine[0])),
                'group' => intval(trim($splitLine[1])),
                'user' => trim($splitLine[2]),
                'command' => $splitLine[3],
            ];

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

            $pids[$details['pid']] = $details;
        }//end foreach

        return $pids;
    }//end getPidsLinux()

    /**
     * @param string $pid Pid.
     *
     * @return boolean Success.
     * @throws \RuntimeException OS not supported.
     * @throws \RuntimeException Could not execute kill command.
     *
     * @since 2015-07-29
     */
    public function killPid($pid)
    {
        // we could use posix_kill() for linux
        // we can also use wmi for windows, see comments: http://php.net/manual/en/function.posix-kill.php
        if ($pid === false) {
            return false;
        }

        if ($this->isWindows() === true) {
            $cmd = 'taskkill /PID '.$pid;
        } elseif ($this->isLinux() === true) {
            // on linux
            $cmd = 'kill -9 '.$pid.' 2>&1';
        } else {
            throw new \RuntimeException('os `'.$this->osRaw.'` is not supported by method `killPid`');
        }

        exec($cmd, $output, $returnVal);

        if ($returnVal !== 0) {
            throw new \RuntimeException('Command `'.$cmd.' did not execute successfully');
        }

        return true;
    }//end killPid()


    /**
     * @param string $group Process group id.
     *
     * @return boolean Success.
     * @throws \RuntimeException OS not supported.
     * @throws \RuntimeException Could not execute kill command.
     *
     * @since 2015-08-16
     */
    public function killGroup($group)
    {
        if ($group === false || $group === 0) {
            return false;
        }

        if ($this->isWindows() === true) {
            // PHP needs to have the COM DOTNET extension enabled for this
            if ($this->com !== null || class_exists('\COM') === true) {
                if ($this->com === null) {
                    $wmi = new \COM("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2");
                } else {
                    $wmi = $this->com;
                }

                $procs = $wmi->ExecQuery("SELECT * FROM Win32_Process WHERE ParentProcessId = '".$group."'");
                foreach ($procs as $proc) {
                    $proc->Terminate();
                }
            }

            $this->killPid($group);
        } elseif ($this->isLinux() === true) {
            // on linux
            $cmd = 'kill -9 -'.$group.' 2>&1';

            exec($cmd, $output, $returnVal);

            if ($returnVal !== 0) {
                throw new \RuntimeException('Command `'.$cmd.' did not execute successfully');
            }
        } else {
            throw new \RuntimeException('os `'.$this->osRaw.'` is not supported by method `killGroup`');
        }//end if

        return true;
    }//end killGroup()


    /**
     * @param string $pid Pid.
     *
     * @return array|boolean False on Failure or array details on success.
     *
     * @since 2015-08-15
     */
    public function getPid($pid)
    {
        $filter = new RunningFilter();
        $filter->setPid($pid);

        $pids = $this->getPids([$filter]);
        if (isset($pids[$pid]) === true) {
            return $pids[$pid];
        }

        return false;
    }//end getPid()


    /**
     * @param array   $filters    Of strings representing filters on the process list.
     * @param boolean $ignoreCase Ignores case in filters.
     * @param boolean $kill       Should we kill PIDs we find.
     *
     * @return boolean success
     *
     * @since 2015-08-12
     */
    public function claimProcess(array $filters, $ignoreCase = true, $kill = false)
    {
        $pids = $this->getPids($filters, $ignoreCase);

        // we are running and we don't want to kill
        if ($kill === false && count($pids) > 0) {
            return false;
        }

        $success = true;
        foreach ($pids as $pid => $details) {
            if ($this->killGroup($details['group']) === false) {
                $success = false;
            }
        }

        // this allowed us to kill as many as possible, so we have the least amount to clean up
        if ($success === false) {
            return false;
        }

        return true;
    }//end claimProcess()


    /**
     * @return array|boolean Details or false if no file.
     * @throws \InvalidArgumentException No pid file specified.
     *
     * @since 2015-07-30
     */
    public function getPidFromFile()
    {
        $file = $this->pidFile;
        if ($file === null) {
            throw new \InvalidArgumentException('No pid file specified.');
        }

        if (file_exists($file) === true) {
            $pid = file_get_contents($file);
            $pid = intval($pid);


            $details = $this->getPid($pid);
            if ($details === false) {
                return false;
            }

            return $details;
        }

        return false;
    }//end getPidFromFile()


    /**
     * @return boolean
     * @throws \InvalidArgumentException No pid file specified.
     *
     * @since 2015-07-30
     */
    public function killPidFromFile()
    {
        $file = $this->pidFile;
        if ($file === null) {
            throw new \InvalidArgumentException('No pid file specified.');
        }

        $details = $this->getPidFromFile();
        if ($details === false) {
            return false;
        }

        $result = $this->killGroup($details['group']);

        // delete the pid file
        if ($result === true && file_exists($file) === true) {
            unlink($file);
        }

        return $result;
    }//end killPidFromFile()


    /**
     * @param boolean $kill Current recorded PID.
     *
     * @return boolean
     * @throws \InvalidArgumentException No pid file specified.
     *
     * @since 2015-07-30
     */
    public function claimPidFile($kill = false)
    {
        $file = $this->pidFile;
        if ($file === null) {
            throw new \InvalidArgumentException('No pid file specified.');
        }

        $details = $this->getPidFromFile();
        if ($details !== false) {
            if ($kill === true && $details !== false) {
                $this->killGroup($details['group']);
            } elseif ($details !== false) {
                return false;
            }
        }

        $result = file_put_contents($file, getmypid());

        if ($result === false) {
            return false;
        }

        return true;
    }//end claimPidFile()


    /**
     * @return boolean Success.
     * @throws \InvalidArgumentException No pid file specified.
     *
     * @since 2015-08-12
     */
    public function releasePidFile()
    {
        $file = $this->pidFile;
        if ($file === null) {
            throw new \InvalidArgumentException('No pid file specified.');
        }

        // delete the pid file
        if (file_exists($file) === true) {
            unlink($file);
            return true;
        }

        return false;
    }//end releasePidFile()
}//end class
