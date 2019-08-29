<?php


namespace PhilKra\Helper;

/**
 * Class Metric
 * Calculate and collection information about CPUs and Memory usage
 *
 * @package PhilKra\Helper
 */
class MetricHelper
{
    private $totalMemory = 0;
    private $availableMemory = 0;
    private $processMemory = 0;

    private $totalCpuUsed = 0;
    private $processCpuUsage = 0;

    private $startProcessMemory;
    private $startCpuUsage;

    public function __construct()
    {

    }

    public function start() {
        $this->startProcessMemory = memory_get_usage();
        $load = sys_getloadavg();
        $this->startCpuUsage = $load[0];
    }

    public function end() {
        $this->processMemory = memory_get_usage() - $this->startProcessMemory;
        $load = sys_getloadavg();
        $this->processCpuUsage = $load[0] - $this->startCpuUsage;

        if ($this->processMemory < 0) {
            $this->processMemory = 0;
        }

        if ($this->processCpuUsage < 0) {
            $this->processCpuUsage = 0;
        }
    }

    /**
     * Collect all information about memory and cpu
     * and put them to array of APM metrics
     *
     * @return array
     */
    public function collectInformation(): array {
        $data = [
            'system.cpu.total.norm.pct' => 0,
            'system.memory.actual.free' => 0,
            'system.memory.total' => 0,
            'system.process.cpu.total.norm.pct' => 0,
            'system.process.cpu.system.norm.pct' => 0,
            'system.process.cpu.user.norm.pct' => 0,
            'system.process.memory.size' => 0,
            'system.process.memory.rss.bytes' => 0
        ];

        $load = sys_getloadavg();
        $data['system.cpu.total.norm.pct'] = $load[0];
        $data['system.process.cpu.total.norm.pct'] = $this->processCpuUsage;
        $data['system.process.cpu.system.norm.pct'] = $this->processCpuUsage;
        $data['system.process.memory.size'] = $this->processMemory;
        //$data['system.process.memory.rss.bytes'] = $this->processMemory;

        $mem = $this->getMemoryUsage();

        $data['system.memory.total'] = $mem['total'];
        $data['system.memory.actual.free'] = $mem['free'];

        return $data;
    }

    private function getMemoryUsage() {
        $mem = [
            'total' => 0,
            'free' => 0,
            'available' => 0
        ];
        //Read /proc/meminfo
        $procFile = '/proc/meminfo';
        if (file_exists($procFile) && is_readable($procFile)) {
            $data = file_get_contents($procFile);
            $dataArray = explode("\n", $data);
            $data = [];
            foreach ($dataArray as $line) {
                $ls = explode(' ', $line);
                foreach ($ls as $value) {
                    if (is_numeric($value)) {
                        $data[] = $value;
                    }
                }
            }
            $mem['total'] = $data[0] * 1024;
            $mem['free'] = $data[1] * 1024;
        } else {
            $free = shell_exec('free');
            $free = (string) trim($free);
            $free_arr = explode("\n", $free);
            $memArray = explode(" ", $free_arr[1]);
            $memArray = array_filter($memArray, function($value) { return ($value !== null && $value !== false && $value !== ''); });
            $memArray = array_merge($memArray);
            $mem['total'] = $memArray[1] * 1024;
            $mem['free'] = $memArray[3] * 1024;
        }

        return $mem;
    }

    /**
     * @return int
     */
    public function getTotalMemory(): int
    {
        return $this->totalMemory;
    }

    /**
     * @return int
     */
    public function getAvailableMemory(): int
    {
        return $this->availableMemory;
    }

    /**
     * @return int
     */
    public function getProcessMemory(): int
    {
        return $this->processMemory;
    }

    /**
     * @return int
     */
    public function getTotalCpuUsed(): int
    {
        return $this->totalCpuUsed;
    }

    /**
     * @return int
     */
    public function getProcessCpuUsed(): int
    {
        return $this->processCpuUsage;
    }


}