<?php
/**
 * Created by PhpStorm.
 * User: ws
 * Date: 14-10-23
 * Time: 下午7:27
 */
class BackDBModel extends BaseModel{
    protected  function __construct(){
        if($this->_db == null){
            $this->_db = new DBClass();
            $dbConf = DBConf::getEnd();
            $this->_db->connect($dbConf['db'], $dbConf['host'], $dbConf['user'], $dbConf['pwd']);
        }
    }
}