<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 */

header("content-type:text/html;charset=utf-8");
class BaseController{
    
    protected $_login = array();
    public function __construct(){
       // error_reporting(E_ERROR);
    	include_once('./conf/ParamConf.php');
        $this->config = @$config;
        $this->global = new GlobalClass();
        $this->search = new Search();
        $this->_login = LoginClass::check();
        if(!$this->_login){
            header("location:./index.php?do=login");return;
        }

        $this->_privuserModel  = EndPrivUserModel::getInstance();
        $this->_privModel = EndPrivModel::getInstance();
        $this->_usrlogModel  = EndActionLogModel::getInstance();
        if($this->_login){
           $this->_priv_arr = $this->getPrivAction();
        }
        $this->userActionLog();
        setcookie('priv',serialize($this->_priv_arr));
        
    }

    /**
     * [getPrivAction 获取当前用户权限]
     * @author morgan 
     * date 2014/12/31
     */
    protected function getPrivAction(){
        //如果是超级管理员  则返回全部权限
        if($this->_login['admin_id'] == 1){
            $result = $this->_privModel->fields(array('priv_id'))->getAll();
        }else{
            $result = $this->_privuserModel->fields(array('priv_id'))->eq(array('admin_id'=>$this->_login['admin_id']))->getAll();
        }
        foreach ($result as $key => $value) {
            $_priv_arr[] = (int)$value['priv_id'];
        }
        return $_priv_arr;
    }

    /**
     * [userActionLog 用户后台日志]
     * @param  $retype        [特殊传参]
     * @return [type]         [description]
     */
    public  function  userActionLog($retype = ""){
        //记录方法名和类名
        if($this->_login){
            $_do = unserialize($_COOKIE['action']);
            $_do = explode('-',$_do);
            $_conf = RouteConf::get();
            $contion['controller'] = ucfirst('index');
            $contion['action']     = ucfirst('default');
            $contion['type']       = ucfirst('default');
            if(!empty($_do[0])){
              $contion['controller'] = ucfirst($_do[0]);
            }
            
            if(!empty($_do[1])){
              if(!empty($_do[0]) && $_do[0]=='index'){
                $contion['action'] = ucfirst('index');
              }else{
                $contion['action'] = ucfirst($_do[1]);
              }
            }
            //对特殊操作进行判断
            if(!empty($retype)){
                $contion['type'] = $retype;
            }else{
                $type  =  $this->config['action_type'];
                foreach ($type as $key => $ty) {   
                    if(strpos($contion['action'],$key)){
                        $contion['type'] = $key;
                    }
                }
            }
            
            $contion['admin_id'] = $this->_login['admin_id'];
            $contion['created']    = date("Y-m-d H:i:s",time());
            //$this->_usrlogModel->insert($contion);
        }
    }
} 