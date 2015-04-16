<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 */

header("content-type:text/html;charset=utf-8");
class BillController extends BaseController{
	public function __construct(){
        parent::__construct();
        $this->_billModel = EndBillModel::getInstance();
        $this->_rechargeModel = FrontRechargeModel::getInstance();
        $this->_frontconsumeModel  = FrontConsumeModel::getInstance();
        $this->_userrechargeModel  = EndUsersRechargeModel::getInstance();
        
    }

    public function defaultAction(){
        if(!in_array(10,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'ctime';
        }
        $data = MemberController::lastTime();
        Search::getCondition($this->_billModel);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$page_size = 20;
		$offset = $page_size*($page-1);
        $list = $this->_billModel->limit($offset,$page_size)->getAll();
        MemberController::lastTime($data);
        Search::getCondition($this->_billModel);
        $count  = $this->_billModel->fields(array('count(auid) as nums'))->getOne();
        $nums   = $count['nums'];
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        $data['list'] = $list;
        include_once("./tpl/bill-stat.html");
    }


    /**
     * [listAction 时间汇总统计]
     * @author morgan 
     * date  2014-12-26
     */
    public function listAction(){
        
        $start_time = empty($_GET['search']['ctime']['ge'])?'0000-00-00 00:00:00':$_GET['search']['ctime']['ge'];
        $end_time   = empty($_GET['search']['ctime']['le'])?date('Y-m-d H:i:s',time()):$_GET['search']['ctime']['le'];
        $chargeUser = $this->_rechargeModel->lt(array('created'=>$end_time))->gt(array('created'=>$start_time))->getAll();
        $hisUserId  = $this->_userrechargeModel->fields(array('uid','ctime'))->lt(array('ctime'=>$end_time))->gt(array('ctime'=>$start_time))->getAll();
        $consumeUser    = $this->_frontconsumeModel->lt(array('created'=>$end_time))->gt(array('created'=>$start_time))->getAll();
        
        /*****统计数据****/
        $_rsArr = array(
            'charge' => array(
                'new' => 0,
                'userids' => array(),
                'nums' => 0,
                'totalPoints' => 0
            ),
            'consume' => array(
                'userids' => array(),
                'nums' => 0,
                'totalPoints' => 0
            )
        );
        //算下新充值人数
        $hisUserIdList = array_map(function ($val) {return $val['uid'];}, $hisUserId);
       
        if (is_array($chargeUser) && !empty($chargeUser)) {
            foreach ($chargeUser as $user) {
                //判断是否是新充值用户
                if (!in_array($user['uid'], $hisUserIdList)) {
                    //写入数据库和add++
                    $hisUserIdList[] = $user['uid'];
                    $_rsArr['charge']['new']++;
                }
                //判断是否重复
                if (!in_array($user['uid'], $_rsArr['charge']['userids'])) {
                    $_rsArr['charge']['userids'][] = $user['uid'];
                    $_rsArr['charge']['nums']++;
                }
                $_rsArr['charge']['totalPoints'] += $user['points'];
            }
        }
        //算下消费用户
        if (is_array($consumeUser) && !empty($chargeUser)) {
            foreach ($consumeUser as $user) {
                //判断是否重复
                if (!in_array($user['send_uid'], $_rsArr['consume']['userids'])) {
                    $_rsArr['consume']['userids'][] = $user['send_uid'];
                    $_rsArr['consume']['nums']++;
                }
                $_rsArr['consume']['totalPoints'] += $user['points'];
            }
        }

        //订单arpu
        $arpu_bill = 0;
        if (is_array($consumeUser) && !empty($chargeUser)) {
            $arpu_bill = ceil($_rsArr['consume']['totalPoints']/count($chargeUser));
        }
        //充值arpu
        $arpu_recharge = 0;
        if ($_rsArr['charge']['nums']) {
            $arpu_recharge = ceil($_rsArr['charge']['totalPoints']/$_rsArr['charge']['nums']);
        }
        //消费arpu
        $arpu_consume = 0;
        if ($_rsArr['consume']['nums']) {
            $arpu_consume = ceil($_rsArr['consume']['totalPoints']/$_rsArr['consume']['nums']);
        }
        $total_bill = empty($chargeUser)?0:count($chargeUser);
        $data = array(
            'new_recharge' => $_rsArr['charge']['new'], //新充值人数
            'total_recharge' => $_rsArr['charge']['nums'], //总的充值人数
            'total_money'=> $_rsArr['charge']['totalPoints'], //总的充值钱数
            'total_bill' => $total_bill,//总的订单数
            'total_customer' => $_rsArr['consume']['nums'], //消费人数
            'total_amount' =>  round(($_rsArr['consume']['totalPoints']/10),2), //消费金额
            'arpu_bill' =>     round(($arpu_bill/10),2),
            'arpu_recharge' => round(($arpu_recharge/10),2),
            'arpu_customer' => round(($arpu_consume/10),2),
           
        );
        $data['search_time'] = $start_time.' --- '.$end_time;
        
        include_once("./tpl/bill-list.html");
    }
} 