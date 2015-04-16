<?php
/**
 * Created by PhpStorm.
 * User: morgan
 * Date: 14-11-03
 * Time: 早晨09:57
 * 手动充值
 */
header("content-type:text/html;charset=utf-8");
class HandrechargeController extends BaseController{
    public function __construct(){
         parent::__construct();
         $this->_rechargeModel      = EndRechargeModel::getInstance();
         $this->_frontuserModel     = FrontUserModel::getInstance();
         $this->_adminModel         = EndAdminModel::getInstance();
         
    }
    /**
     * [defaultAction 手动充值]
     * @author [morgan] 
     * @date(2014/11/03)
     */
    public function defaultAction(){
       //判断权限
        if(!in_array(17,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        if(isset($_POST['admin_id'])){
              $conditions['uid']           =  $ExcuseDate['uid'] = addslashes($_POST['uid']);
              $conditions['admin_id']      =  addslashes($_POST['admin_id']);
              $conditions['charge_amount'] =  intval($_POST['charge_amount'])?intval($_POST['charge_amount']):0;
              $conditions['content']       =  addslashes($_POST['content']);
              $conditions['ctime']         =  date('Y-m-d H:i:s',time());
              $ExcuseDate['points']        =  intval($_POST['points'])?intval($_POST['points']):0;
              
              if($ExcuseDate['points']<0){
                  $ExcuseDate['points'] = -$ExcuseDate['points'];
                  if($conditions['charge_amount']<0){
                       $conditions['charge_amount'] = -$conditions['charge_amount'];
                  }
              }
              if($ExcuseDate['points']==0 || empty($ExcuseDate['points'])){
                     echo '<script>alert("非法操作");location.href="./index.php?do=handrecharge";</script>';
                     return false; 
              }
              if(is_numeric($ExcuseDate['points'])){
                      $ExcuseDate['pay_type']      = 3;
                      $status  = $this->global->vedioInterface($ExcuseDate,$this->config['recharge_url']);
                      
                      if($status){
                         $conditions['status'] = $status['ret'];
                      }else{
                         $conditions['status'] = -1;
                      }
                      //记录充值操作
                      $this->userActionLog("Recharge");
                      $conditions['type']  = 1;
                      $state = $this->_rechargeModel->insert($conditions);
                      
                      if($state && ($status['ret']==1)){
                        echo '<script>alert("操作成功");location.href="./index.php?do=handrecharge";</script>';
                      }else{
                        echo '<script>alert("操作失败");location.href="./index.php?do=handrecharge";</script>';
                      }
              
              }else{
                     echo '<script>alert("请输入整数");location.href="./index.php?do=handrecharge";</script>';
                     return false;     
              }
        }      
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $count  = $this->_rechargeModel->fields(array('count(id) as nums'))->eq(array("type"=>1))->getOne();
        $nums   = $count['nums'];
        $list = $this->_rechargeModel->order('ctime desc')->eq(array("type"=>1))->limit($offset,$page_size)->getAll();
        foreach ($list as $key => $value) {
              $admin = $this->_adminModel->fields(array('name'))->eq(array('admin_id'=>$value['admin_id']))->getOne();
              $user  = $this->_frontuserModel->fields(array('nickname'))->eq(array('uid'=>$value['uid']))->getOne();
              $list[$key]['admin_name'] = $admin['name'];
              $list[$key]['nickname']   = $user['nickname'];
        }
        $data['list']  = $list;
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        
        include_once("./tpl/hand-recharge.html");
    }


    public function ajaxGetOneAction(){
        $uid = intval($_GET['id']);
        $user = $this->_frontuserModel->eq(array('uid'=>$uid))->getOne();
        if($user){
          echo 1;
        }else{
          echo 0;
        }
    }
} 