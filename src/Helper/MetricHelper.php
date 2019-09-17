<?php


namespace PhilKra\Helper;

/**
 * Class Metric
 * Calculate and collection information about CPUs and Memory usage
 *
 * @package PhilKra\Helper
 *
 *
 * @codeCoverageIgnore
 */
class MetricHelper
{
    /**
     * Collect all information about memory and cpu
     * and put them to array of APM metrics
     *
     * @return array
     */
    public function collectInformation(): array {
        $data = [];
        $load = sys_getloadavg();
        $data['system.cpu.total.norm.pct'] = $load[0];
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
        try {
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
        } catch (\Exception $exception) {
            //Can not get system memory info
        }

        return $mem;
    }


}