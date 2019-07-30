<?php


namespace PhilKra\Helper;


class System
{
    /** @var array  */
    private $systemInfo = [];

    public function __construct()
    {
        try {
            if (is_dir('/sys') && is_dir('/proc')) {
                $this->systemInfo['system.cpu.total.norm.pct'] = $this->getCPU();
                $memInfo = $this->getRam();
                if (!empty($memInfo)) {
                    $this->systemInfo['system.memory.total'] = $memInfo['MemTotal'];
                    $this->systemInfo['system.memory.actual.free'] = $memInfo['MemFree'];
                }
            }
        } catch (\Exception $e) {

        }
    }
    /**
     * Get used memory status
     *
     * @return array
     */
    private function getRam()
    {
        // We'll return the contents of this
        $return = [];
        // Files containing juicy info
        $procFileMem = '/proc/meminfo';
        // First off, these need to exist..
        if (!is_readable($procFileMem)) {
            return [];
        }
        // To hold their values
        $memVals = [];
        // Get memContents
        @preg_match_all('/^([^:]+)\:\s+(\d+)\s*(?:k[bB])?\s*/m', $this->readSystemFile($procFileMem), $matches, PREG_SET_ORDER);
        // Deal with it
        foreach ((array) $matches as $memInfo) {
            $memVals[$memInfo[1]] = $memInfo[2];
        }
        return $memVals;
        //return  round((($memVals['MemTotal']  - $memVals['MemFree']) / $memVals['MemTotal']) * 100, 2);
    }
    /**
     * Get CPU used status
     *
     * @return float
     */
    private function getCPU()
    {

        $iterations = 2;
        // Probably only inline function here. Only used once so it makes sense.
        for ($i = 0; $i < $iterations; ++$i) {
            $contents = $this->readSystemFile('/proc/stat', false);
            // Yay we can't read it so we won't sleep below!
            if (!$contents) {
                continue;
            }
            // Overall system CPU usage
            if (preg_match('/^cpu\s+(.+)/', $contents, $m)) {
                $key = 'overall';
                $line = $m[1];
                // With each iteration we compare what we got to last time's version
                // as the file changes every milisecond or something
                static $prev = [];
                // Using regex/explode is excessive here, not unlike rest of linfo :/
                $ret = sscanf($line, '%Lu %Lu %Lu %Lu %Lu %Lu %Lu %Lu');
                // Negative? That's crazy talk now
                foreach ($ret as $k => $v) {
                    if ($v < 0) {
                        $ret[$k] = 0;
                    }
                }
                // First time; set our vals
                if (!isset($prev[$key])) {
                    $prev[$key] = $ret;
                }
                // Subsequent time; difference with last time
                else {
                    $orig = $ret;
                    foreach ($ret as $k => $v) {
                        $ret[$k] -= $prev[$key][$k];
                    }
                    $prev[$key] = $orig;
                }
                // Refer back to top.c for the reasoning here. I just copied the algorithm without
                // trying to understand why.
                $retSum = (float) array_sum($ret);
                if($retSum > 0) {
                    $scale = 100 / $retSum;
                } else {
                    $scale = 100;
                }
                $cpu_percent = $ret[0] * $scale;
                return round($cpu_percent, 2);
            }
        }
        return 0;
    }

    /**
     * @param $file
     * @param string $default
     * @return string
     */
    private function readSystemFile($file, $default = '') {
        return !is_file($file) || !is_readable($file) || !($contents = @file_get_contents($file)) ? $default : trim($contents);
    }
    /**
     * @return array
     */
    public function getSystemInfo(): array
    {
        return $this->systemInfo;
    }

}