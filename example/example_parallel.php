<?php

include __DIR__ . '/../ParserAbstract.php';
include __DIR__ . '/Hotel.php';

$processes = !empty($argv[1]) && intval($argv[1]) > 0 ? intval($argv[1]) : 5;

$hotel = new Hotel('Hotel', 'hotel_p.log');

$count = ParserAbstract::count('hotel.xml', 'Hotel');
$jobs = $processes === 1 ? 1 : floor($count / $processes);

echo "count {$count}\n";
echo "jobs {$jobs}\n";
echo "processes {$processes}\n";

do_parallel_func($processes, $jobs, function ($job, $proc_num) use ($jobs, $hotel, $processes, $count) {
    $msg = "process: {$proc_num}, job: {$job}";
    if (($jobs - ($job + 1) == 0)) {
        $msg .= ' last';
    }

    $limit = ($jobs - ($job + 1) == 0) ? -1 : $jobs;
    $offset = $jobs * $job;

    echo "{$msg}, load {$offset} {$limit}\n";

    $hotel->load('hotel.xml', $offset, $limit);
});

//=======================================================================
/**
 * @param $processes_count
 * @param $jobs_count
 * @param $func
 * @throws ErrorException
 */
function do_parallel_func($processes_count, $jobs_count, $func)
{
    if (!is_numeric($processes_count) || !is_numeric($jobs_count)) {
        throw new ErrorException('processes_count and jobs_count should be numeric, proc: ' . gettype($processes_count) . ', jobs: ' . gettype($jobs_count));
    }
    if ($processes_count <= 0 || $jobs_count <= 0) {
        throw new ErrorException("processes_count and jobs_count should be bigger than zero, proc: {$processes_count}, jobs: {$jobs_count}");
    }
    for ($proc_num = 0; $proc_num < $processes_count; $proc_num++) {
        $pid = pcntl_fork();
        if ($pid < 0) {
            fwrite(STDERR, "Cannot fork\n");
            exit(1);
        }
        if ($pid == 0) {
            break;
        }
    }

    if (!empty($pid)) {
        for ($i = 0; $i < $processes_count; $i++) {
            pcntl_wait($status);
            $exitcode = pcntl_wexitstatus($status);
            if ($exitcode) {
                exit(1);
            }
        }
        return;
    }

    for ($i = $proc_num; $i < $jobs_count; $i += $processes_count) {
        if (is_callable($func)) {
            $func($i, $proc_num);
        }
    }

    exit(0);
}