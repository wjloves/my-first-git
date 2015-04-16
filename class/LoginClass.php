<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午4:42
 */

class LoginClass{
    public static function check(){
        $login = $_SESSION['login'];
        if(empty($login)){
            return false;
        }
        $login = unserialize($login);
        $admin = EndAdminModel::getInstance();
        $info = $admin->fields(array('admin_id'))->eq(array('admin_id'=>$login['admin_id']))->getOne();
        if(empty($info)){
        	return false;
        }
        return $login;
    }
}