<?php
/*
 * autor @gom
 * autor @sonar
 *
 * PHP CLI マルチプロセスサンプル
 * このプログラムは、テキストに書かれたホストへPINGを行い、死活チェックを行うサンプルです。
 *
 */

const THREAD = 30;			// プロセス数

$time_start = microtime(true);
$readline = file('test.txt');
foreach($readline as $v){
	$tmp[] = trim($v);
}
$readline = $tmp;

foreach($readline as $line){
	$pid = pcntl_fork();

	switch($pid){
		case -1: // フォーク失敗
			die("フォーク失敗");
			break;
		case 0: // 子プロセス
			$ping_command_str = "ping -c 2 -w 4 " . $line;
			$ping_command_str = `$ping_command_str`;
			if (!strstr($ping_command_str, '100% packet loss')) {
				echo 'PING OK : ' .$line . "\n";
			} else {
				echo 'PING NG : ' .$line . "\n";
			}
			exit(0); // 子プロセスはここで処理を終了
			break;
		default: // 親プロセス
			$pids[$pid] = $pid; // 子プロセスIDを保持
			if ( count( $pids ) >= THREAD ) { // 指定のスレッド数より多くなっていれば待機状態へ
				unset( $pids[ pcntl_waitpid( -1, $status, WUNTRACED ) ] );
			}
			break;
	}
}

// 全ての子プロセスが終了するまで親プロセスは待つ
foreach($pids as $pid){
	pcntl_waitpid($pid, $status);
}
$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Parent process finish. time:".$time."\n";

