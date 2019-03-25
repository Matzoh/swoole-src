--TEST--
swoole_server/task: task in task worker
--SKIPIF--
<?php require __DIR__ . '/../../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../../include/bootstrap.php';
$pm = new ProcessManager;
$pm->setWaitTimeout(60);
$pm->parentFunc = function ($pid) use ($pm) {
    echo "SUCCESS\n";
    $pm->kill();
};

$pm->childFunc = function () use ($pm)
{
    ini_set('swoole.display_errors', 'Off');
    $serv = new swoole_server('127.0.0.1', $pm->getFreePort());
    $serv->set(array(
        "worker_num" => 1,
        'task_worker_num' => 1,
        'log_file' => '/dev/null',
    ));

    $serv->on("WorkerStart", function (\swoole_server $serv, $wid) use ($pm) {
        if ($serv->taskworker) {
            assert(@$serv->task(['type' => 'array', 'value' => 'task worker']) === false);
            $pm->wakeup();
        }
    });

    $serv->on('receive', function (swoole_server $serv, $fd, $rid, $data)
    {

    });

    $serv->on('task', function (swoole_server $serv, $task_id, $worker_id, $data) use($pm)
    {

    });

    $serv->on('finish', function (swoole_server $serv, $fd, $rid, $data)
    {

    });
    $serv->start();
};

$pm->childFirst();
$pm->run();
?>
--EXPECT--
SUCCESS
