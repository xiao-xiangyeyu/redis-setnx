<?php
include './order.php';
include './redisLock.php';

/**
 *  压力测试模拟请求
 * ab  -n 10 -c 5 127.0.0.1:80/redis-lock/index.php
 *	
 *  -c  一次产生的请求个数 并发数
 *  -n   总请求个数
 *  ab压力测试 发10次请求 1秒完成 一次请求10个
 *	
 */
try{
	$flag = false; // 是否开启redis锁  true开启redis锁, false 关闭redis锁的情况
	$uid = rand(11111,2222); // 用户标识	
	$good_id = 1; // 商品标识
	$num = rand(1,3); // 购买商品数

	$redis = new redisLock();
	$key = 'good_id:'.$good_id;
	$lock = $redis->lock($key);
	if( $flag && !$lock){
		$return=array(
			'msg'=>'系统繁忙稍后再试',
			'code'=>500
		);
		exit(json_encode($return));
	}
	$orderObj = new Order();
	$res = $orderObj ->dealOrder($uid,$good_id,$num);
	$param = array( 'uid'=>$uid, 'good_id'=>$good_id,'num'=>$num);
	if(!$res)throw new Exception("下单失败", 1006);
		// 处理成功释放锁
		$redis->unlock($key);
		file_put_contents('./run.log','时间:'.date('Y-m-d H:i:s').',订单处理成功:'.json_encode($param).PHP_EOL,FILE_APPEND);
		$return=array(
			'msg'=>'下单成功',
			'code'=>200
		);
		exit(json_encode($return));
}catch(EXception $e){
	// 异常情况下释放锁
	$redis->unlock($key);
	file_put_contents('./run.log','时间:'.date('Y-m-d H:i:s').',订单处理失败msg:'.$e->getMessage().',code:'.$e->getCode().PHP_EOL,FILE_APPEND);
	$return=array(
			'msg'=>'下单失败',
			'code'=>200
		);
	exit(json_encode($return));
}

