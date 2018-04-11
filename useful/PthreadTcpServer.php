<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 2018/3/24
 * Time: 23:41
 */

if ( ($sock = socket_create(AF_INET, SOCK_STREAM ,SOL_TCP)) < 0 ) {
	echo "failed to create socket:" . socket_strerror($sock) . "\n";
	exit();
}

if ( ($ret = socket_bind($sock, "192.168.1.100", 8631))  < 0 ) {
	echo "failed to bind socket:" . socket_strerror($ret) . "\n";
	exit();
}

if ( ($ret = socket_listen($sock, 0) ) < 0  ) {
	echo "failed to listen to socket: ". socket_strerror($ret) . "\n";
	exit();
}

$hanke = "welcome to connect";

while( true ) {
	$conn = @socket_accept($sock);

	if ( !$conn ) {
		echo "connected failed\n";
	}else{
		echo "conncted success\n";
		if ( pcntl_fork() == 0 ) {

			echo " send hello to connect\n";
			socket_write($conn, $hanke."\n");
			$recv = socket_read($conn, 8192);
			//处理数据
			$sendData = "server: " . $recv;
			socket_write($conn, $sendData);
			socket_close($conn);
			exit(0);
		}else{
			socket_close($conn);
		}

	}
}
