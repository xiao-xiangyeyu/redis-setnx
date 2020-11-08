<?php

class DB{
	private $host = '127.0.0.1';
	private $username = 'root';
	private $pass = '123456';
	private $port = 3306;
	private $dbName='testdb';
	private $db = '';
	function __construct(){

		$db = new mysqli($this->host, $this->username, $this->pass, $this->dbName, $this->port);
		if(!$db){
			throw new Exception("数据库连接失败", 1001);
		}
		$this->db = $db;
	}	

	/**
	 * 获取数据库连接对象
	 */
	function getDb(){
		return $this->db;
	}									
}


