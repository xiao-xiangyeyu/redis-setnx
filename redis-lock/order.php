<?php
include './db.php';
/**
 * 订单；订单操作类
 */
class Order{

	private $db;
	function __construct(){
		$this->db = (new DB())->getDb();
	}

	/**
	 * @param $uid [用户标识]
	 * @param $good_id [商品标识]
	 * @param $num [商品的数量]
	 */
	function dealOrder($uid,$good_id,$num){
		
		// 查询用户信息


		// 查询商品库存
		$goodInfo = $this->getGoodInfo($good_id);
		if($num >3){
			throw new Exception("每个用户只能购买两个商品", 1002);
		}
		if(!$goodInfo){
			throw new Exception("商品信息未找到", 1003);
		}
		$good_stock = $goodInfo['good_stock']; // 商品库存
		// 限制库存
		if($good_stock < $num){
			throw new Exception("商品库存不足", 1004);
		}
		// 减库存
		$goodData =array(
				'good_id'=>$good_id,
				'good_stock'=>$good_stock-$num,
			);

		$ret = $this->updateGoods($goodData);

		// 订单数据
		$orderData=array(
				'uid'=>$uid,
				'good_id'=>$good_id,
				'purchases_num'=>$num,
				'add_time'=>date('Y-m-d H:i:s'),
				'order_num'=>md5(time().uniqid()),
			);
		$res = $this->addOrder($orderData);
		if($res && $ret) return true;
		

	}

	/**
	 * 更新库存表
	 */
	private function updateGoods($goodData){
		$sql = "UPDATE tb_goods SET good_stock =".$goodData['good_stock']." where good_id = ".$goodData['good_id'];
		$query = $this->db->multi_query($sql);
		return $query;
	}


	private function addOrder($param){
		$sql = "INSERT INTO tb_order (`uid`,`good_id`,`purchases_num`,`add_time`,`order_num`) values 
				(".$param['uid'].",".$param['good_id'].",".$param['purchases_num'].",'".$param['add_time']."','".$param['order_num']."') ";
		$query = $this->db->multi_query($sql);
		return $query;
	}


	/**
	 *  获取商品的信息
	 */
	private function getGoodInfo($good_id){
		$sql = "select * from tb_goods where good_id = ".$good_id;
		$query = $this->db->query($sql);
		return current($query->fetch_all(MYSQLI_ASSOC))?:[];

	}



}




