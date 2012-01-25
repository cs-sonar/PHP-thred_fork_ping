<?php
/**
 * thred_fork_ping.php.
 * PHP CLI マルチプロセスサンプル.
 * このプログラムは、テキストに書かれたサーバーへPINGを行い、死活チェックを行うサンプルです。.
 * usage.
 * #php thred_fork_ping.php int $param
 *
 * @autor gom
 * @autor sonar
 * @param int 並列処理回数
 *
 */

// コマンドラインの引数を取得し、スレッド数としてセット
if(!$argv[1]){
	$argv[1] = 1;
//}elseif(!is_numeric($argv[1])){
}elseif(!preg_match('/^[1-9][0-9]+$/', $argv[1])){
	die("引数は正の整数でお願いします。\n");
	exit;
}

$time_start = microtime(true);
$readline = file('list.dat');
foreach($readline as $v){
	$tmp[] = trim($v);
}
$readline = $tmp;

foreach($readline as $line){
	$pid = pcntl_fork();

	switch($pid){
		case -1: // フォーク失敗
			die("フォーク失敗\n");
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
			if ( count( $pids ) >= $argv[1] ) { // 指定のスレッド数より多くなっていれば
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

exit;

