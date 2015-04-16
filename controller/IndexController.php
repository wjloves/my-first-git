<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 */

header("content-type:text/html;charset=utf-8");
class IndexController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->_rechargeModel = FrontRechargeModel::getInstance();
        $this->_consumeModel  = FrontConsumeModel::getInstance();
        $this->_userModel     = FrontUserModel::getInstance();
        $this->_liveModel     = FrontLiveModel::getInstance();
        $this->_onlineMode    = FrontOnlineModel::getInstance();
        $this->_userstatMode  = EndUsrStatModel::getInstance();
        $this->_endrechargeModel = EndRechargeModel::getInstance();
    }

    /**
     * [indexAction 及时数据显示]
     * @author morgan
     */
    public function indexAction(){
        //判断权限
        if(!in_array(8,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=login-logout';</script>";
                return;   
        }
        $data = array();
        //送礼交易流水
        $data['consume'] = $this->_consumeModel->getTodayStatistics();
        //获取今日充值数据
        $data['today']         = $this->_getTodayData();
        
       
        //总收入和付费用户
        $_all =  $this->_getAllData();

        if(!empty($_all) && !empty($data)){
            $data =  array_merge($data,$_all);    
        }
        //本日登录账号数和新注册人数
        $data['today_login'] = $this->_userModel->getTodayStatistics();
        //本日最高在线人数
        $data['max_online']  = $this->_onlineMode->fields(array("max(users) as musers"))->lt(array('created'=>date('Y-m-d 23:59:59')))->gt(array('created'=>date('Y-m-d 00:00:00')))->getOne();
        //在线总数和游客在线总数
        $data['total_people'] = $this->_onlineMode->fields(array('users','guest_users'))->order('created desc')->getOne();
        
        //分页
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $contion['page'] = $page;
        //主播列表
        $data['list']  = $this->global->vedioInterface($contion,$this->config['host_online_url']);
        include_once('./tpl/index.html');
    }



    /**
     * [getTodayStatistics 当日充值人数，次数，金额]
     * @return array || Boolean
     * @author morgan
     * date 2014.12.04
     * update  2014.12.24
     */
    public  function _getTodayData(){
        //前台本日充值数据
        $front_today = $this->_rechargeModel->lt(array('created'=>date('Y-m-d 23:59:59')))->gt(array('created'=>date('Y-m-d 00:00:00')))->eq(array('pay_status'=>1))->getAll(); 
        //后台本日手动充值数据
        // $end_today   = $this->_endrechargeModel->lt(array('ctime'=>date('Y-m-d 23:59:59')))->gt(array('ctime'=>date('Y-m-d 00:00:00')))->getAll(); 
        //计算本日充值人数，次数，总金额
        return $this->_calculationStatistics($front_today,'');
    }

    /**
     * [getAllStatistics 周，月金额和人数计算]
     * @return array || Boolean
     * @author morgan
     * date    2014.12.04
     * update  2014.12.24
     */
    public function  _getAllData(){
        //本周开始时间
        $week_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))); 
        //本周结束时间
        $week_end   = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"))); 
        //本月开始时间
        $month_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y"))); 
        //本月结束时间
        $month_end   = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y"))); 
        //本周收入和人数
        $week_users = $this->_rechargeModel->fields(array('uid','sum(points) as points'))->eq(array('pay_status'=>1))->gt(array('created'=>$week_start))->lt(array('created'=>$week_end))->group(' uid')->getAll();
      
        $data['week_users'] = $this->_calculationStatistics($week_users,'');
        //var_dump($data['week_users']);
        //本月收入和人数
        $month_users = $this->_rechargeModel->fields(array('uid','sum(points) as points'))->eq(array('pay_status'=>1))->gt(array('created'=>$month_start))->lt(array('created'=>$month_end))->group(' uid')->getAll();
        $data['month_users'] = $this->_calculationStatistics($month_users,'');
        return $data;
    }

    /**
     * [_calculation description]
     * @param  [array] $front [前台用户充值数据]
     * @param  [array] $end   [后台用户充值数据]
     * @return array || Boolean
     * @author morgan
     * date 2014.12.04
     */
    public function _calculationStatistics($front,$end){
        $_rsArr = array(
                "points"=>0,
                "users" =>0,
                "times" =>0
            );
        $_users = array();
        $front_num = $end_num = 0;
        if(!empty($front)){
            $front_num = count($front);
        }
        if(!empty($end)){
             $end_num   = count($end);
        }
        for($i=0;$i<$front_num;$i++){
            $_rsArr['points'] += $front[$i]['points'];
            $_rsArr['times']++;
            $_users[$front[$i]['uid']] = $_rsArr['times'];
        }
        for($j=0;$j<$end_num;$j++){
            $_rsArr['points'] += $end[$j]['charge_amount'];
            $_rsArr['times']++;
            if(!in_array($end[$j]['uid'],array_flip($_users))){
                    $_users[$end_today[$j]['uid']] = $_rsArr['times'];
            }
        }
        $_rsArr['points'] = round(($_rsArr['points']/10),2);
        $_rsArr['users'] = count($_users);
        return $_rsArr;
    }
} 