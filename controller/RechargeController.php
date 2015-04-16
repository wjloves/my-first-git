<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-11-25
 * Time: 下午14:54
 */

header("content-type:text/html;charset=utf-8");
class RechargeController extends BaseController {
    public function __construct(){
        parent::__construct();
        $this->_rechargeModel  = FrontRechargeModel::getInstance();
        $this->_maillistModel  = FrontConsumeModel::getInstance();

        $this->_chargeListModel = FrontChargeListModel::getInstance();
        $this->_userModel = FrontUserModel::getInstance();
       

    }

    /**
     * [defaultAction 充值明细]
     * @author kid  morgan
     */
    public function defaultAction(){
        //判断权限
        if(!in_array(14,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;
        }

        $this->seachRechar();
        Search::getCondition($this->_rechargeModel);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $data['list'] = $this->_rechargeModel->order('created desc')->limit($offset,$page_size)->getAll();
        $this->seachRechar();
        Search::getCondition($this->_rechargeModel);
        $count = $this->_rechargeModel->fields(array('count(id) as nums'))->getOne();
        $nums = $count['nums'];
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        $data['page_nums'] = ceil($nums/$page_size);
        $data['pay_type'] = $this->config['pay_type'];
        include_once("./tpl/recharge-list.html");
    }

    public function  seachRechar(){
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
            $keyword = addslashes($_GET['keyword']);
            $this->_rechargeModel->like(array("order_id"=>$keyword,"pay_id"=>$keyword));
        }
        return $this->_rechargeModel;
    }

    /**
     * [auditAction 显示页面]
     * @author cannyco
     * date 2015/04/06
     */
    public function  auditAction()
    {
        //判断权限
        if (!in_array(48, $this->_priv_arr)) {
            echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
            return;
        }

        $this->seachOrder();
        Search::getCondition($this->_chargeListModel);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $data['list'] = $this->_chargeListModel->order('ctime desc')->limit($offset, $page_size)->getAll();
        $this->seachOrder();
        Search::getCondition($this->_chargeListModel);
        $count = $this->_chargeListModel->fields(array('count(id) as nums'))->getOne();
        $nums = $count['nums'];
        $data['pages'] = $this->global->pages($page_size, $nums, 5, $page);

        $data['pay_type'] = $this->config['pay_type'];


        //处理用户数据，单独取名字,还有取下财务定单号

        if (is_array($data['list']) && $data['list']) {
            foreach ($data['list'] as $key => $val) {
                $where = array('uid' => $val['uid']);
                $a = $this->_userModel->eq($where)->getOne();
                $data['list'][$key]['uname'] = $a['username'];

                $where2 = array('order_id' => $val['tradeno']);
                $b = $this->_rechargeModel->eq($where2)->getOne();
                //var_dump($b);exit;
                $data['list'][$key]['tradeno2'] = $b['pay_id'];
            }
        }

        $search = $_POST;
        include_once("./tpl/recharge-audit.html");
    }

    /**
     * [seachOrder 搜索条件]
     * @author cannyco
     * date 2015/04/06
     */
    public function  seachOrder(){
        if(isset($_POST['search']) && !empty($_POST['search'])) {
            //处理下判断条件
            $username = $_POST['search']['uname'];
            if ($username) {
                //查询下用户id
                $sql = 'select uid from video_user where `username` like "%' . $username . '%" limit 20';
                $uidlist = $this->_userModel->getAll($sql);
                array_walk($uidlist, function(&$v, $k){$v = $v['uid'];});
                $this->_chargeListModel->in(array("uid" => $uidlist));
            }

            $searchuid = $_POST['search']['uid'];
            if ($searchuid) {
                $this->_chargeListModel->eq(array("uid" => $searchuid));
            }

            $searchtradeno = $_POST['search']['tradeno'];
            if ($searchtradeno) {
                $this->_chargeListModel->eq(array("tradeno" => $searchtradeno));
            }


            //处理下查询财务订单号的情况
            $searchtradeno2 = $_POST['search']['tradeno2'];
            if ($searchtradeno2) {
                //查询下用户id
                $sql = 'select order_id from video_recharge where `pay_id` = "' . $searchtradeno2 . '" limit 1';
                $orderidResult = $this->_userModel->getOne($sql);
                $this->_chargeListModel->eq(array("tradeno" => $orderidResult['order_id']));
            }


            $searchstatus = $_POST['search']['pay_status'];
            if ($searchstatus) {
                //传过来的1其实数据库存的是0
                if ($searchstatus == 1) $searchstatus = 0;
                $this->_chargeListModel->eq(array("status" => $searchstatus));
            }

            //订单生成时间
            $searchctime = $_POST['search']['ctime'];
            if ($searchctime['ge']) {
                $this->_chargeListModel->ge(array("ctime" => $searchctime['ge']));
            }
            if ($searchctime['lt']) {
                $this->_chargeListModel->le(array("ctime" => $searchctime['le']));
            }
            //订单支付完成时间
            $searchttime = $_POST['search']['ttime'];
            if ($searchttime['ge']) {
                $this->_chargeListModel->ge(array("ttime" => $searchttime['ge']));
            }
            if ($searchttime['le']) {
                $this->_chargeListModel->le(array("ttime" => $searchttime['le']));
            }

        }
    }

    /**
     * [dealAction 处理确认操作]
     * @author cannyco
     * date 2015/04/06
     */
    public function dealAction()
    {
        $realmoney = $_POST['realmoney']?$_POST['realmoney']:'';
        $message = $_POST['message']?$_POST['message']:'后台手动处理！';
        $orderid = $_POST['tradeno']?$_POST['tradeno']:0;
        $userid = $_POST['reuid']?$_POST['reuid']:0;
        if (!$realmoney || !$orderid || !$userid) {
            echo json_encode(array('code' => 1, 'message' => '钱没有填或者订单号为空！'));exit;
        }
        //video_charge_list表修改状态，添加钻石
        $points = ceil($realmoney*10);
        $data = array(
            'paymoney'=> $realmoney,
            'points'=> $points,
            'status'=> 2,
            'ttime'=> date('Y-m-d H:i:s'),
            'message' => $message
        );
        $chargeListResult = $this->_chargeListModel->update($data, "tradeno = '" . $orderid . "'");
        if ($chargeListResult == 1) {
            //调用gesila的接口，加上钻石，里面包含redis操作
            $diamondData = array(
                'uid' => $userid,
                'points' => $points,
                'pay_type' => 1,
            );
            $reUrl = $this->config['recharge_url'];
            $this->global->vedioInterface($diamondData, $reUrl);
            //调用活动接口
            $token = $this->_generateUidToken($userid);
            $activityData = array(
                'ctype' => $this->config['activity_name'], //活动类型
                'money'=> $realmoney, //充值的金额
                'uid'=>   $userid, //用户id
                'token' => $token, //口令牌
                'order_num' => $orderid, //定单号
            );
            $re2Url = $this->config['activity_url'];
            $this->sendRequestUrl($re2Url, $activityData);
            echo json_encode(array('code' => 0, 'message' => 'success!'));exit;
        } else {
            echo json_encode(array('code' => 2, 'message' => '数据问题，请排查！'));exit;
        }
    }

    /**
     * [nodealAction 处理确认失败操作]
     * @author cannyco
     * date 2015/04/06
     */
    public function nodealAction()
    {
        $message = $_POST['message']?$_POST['message']:'后台手动处理！';
        $orderid = $_POST['tradeno']?$_POST['tradeno']:0;
        if (!$orderid) {
            echo json_encode(array('code' => 1, 'message' => '订单号为空！'));exit;
        }
        //video_charge_list表修改状态，添加钻石
        $data = array(
            'status'=> 3,
            'ttime'=> date('Y-m-d H:i:s'),
            'message' => $message
        );
        $chargeListResult = $this->_chargeListModel->update($data, "tradeno = '" . $orderid . "'");
        if ($chargeListResult == 1) {
            //调用gesila的接口，加上钻石，里面包含redis操作
            echo json_encode(array('code' => 0, 'message' => '确认失败成功!'));exit;
        } else {
            echo json_encode(array('code' => 2, 'message' => '数据问题，请排查！'));exit;
        }
    }

    /**
     * [_generateUidToken 生成用户对一个的token]
     * @author cannyco
     * @param $uid
     * @return $token
     * date 2015/04/06
     */
    private function _generateUidToken($uid){
        $token = md5(uniqid(mt_rand(), true));
        $redis = new redis();
        $redis->connect($this->config['redis_ip'],$this->config['redis_port']);
        $redis->set('user_token:'.$uid, $token);
        $redis->close();
        return $token;
    }

    /**
     * [sendRequestUrl 发送请求]
     * @author cannyco
     * date 2015/04/06
     */
    public function sendRequestUrl($activityUrl, $activityPostData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $activityUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $activityPostData);
        $activityResult = curl_exec ($ch);
        curl_close ($ch);
        return $activityResult;
    }

    /**
     * [variableAction 帐变列表]
     * @author morgan
     * 2015/03/30
     */
    public function  variableAction(){
         //判断权限
        if(!in_array(47,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        //分页
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 10;
        $offset = $page_size*($page-1);
     
        //默认查询用户  @标示状态  $status 0：正常查询两表    1： 只查询mail_list   2： 只查询recharge
        $data['roled'] = $status = $countr['renums'] = $countm['manums'] = 0;
        //公用查询条件
        $where = " and u.roled = ".$data['roled'];
        $recondition = $macondition = ' WHERE u.status!=0 ';

        //用户类型查询
        if (isset($_GET['roled'])) {
            $data['roled']  =  $_GET['roled'];
            $where = " and u.roled = ".$data['roled'];
        }

        //帐变类型查询
        if(isset($_GET['variable_type']) && !empty($_GET['variable_type'])){
            $variable_type = intval($_GET['variable_type']);
            if($variable_type != 2){
                $recondition = " and r.pay_type = ".$variable_type;
                $status = 2;
            }else{
                $status = 1;
                $where  .= " AND m.send_uid = u.uid";
            }
            $page_size   = 20;
            $offset = $page_size*($page-1);
        }
        //时间查询
        if(isset($_GET['ge']) && !empty($_GET['ge'])){
            $ge  = addslashes($_GET['ge']);
            $recondition .= " and r.created >='".$ge."'";
            $macondition .= " and m.created >='".$ge."'";
        }
        if(isset($_GET['le']) && !empty($_GET['le'])){
            $le  = addslashes($_GET['le']);
            $recondition .= "  and r.created <='".$le."'";
            $macondition .= "  and m.created <='".$le."'";
        }
        //帐变金额查询
        if(isset($_GET['ge_points']) && !empty($_GET['ge_points'])){
            $ge_points  = addslashes($_GET['ge_points']);
            $recondition .= " and r.points >='".$ge_points."'";
            $macondition .= " and m.points >='".$ge_points."'";
        }
        if(isset($_GET['le_points']) && !empty($_GET['le_points'])){
            $le_points  = addslashes($_GET['le_points']);
            $recondition .= "  and r.points <='".$le_points."'";
            $macondition .= "  and m.points <='".$le_points."'";
        }

        //用户查询
        if(isset($_GET['username']) && !empty($_GET['username'])){
            $username = addslashes($_GET['username']);
            $where .= ' and u.username like "%'.$username.'%"';
        }
        

        if($status != 1){
            //查询充值表，主播和用户不区分，都统计为收入字段
            $resql      = " SELECT r.uid,r.points as repoints,r.pay_type,r.created,u.username,u.points,u.roled FROM `video_recharge` r  JOIN `video_user` u ON r.uid = u.uid 
                   ".$recondition.$where." ORDER BY r.created desc limit ".$offset.",".$page_size;    
            $recharges  = $this->_rechargeModel->getAll($resql);
            $rechargesql = " SELECT count(1) as renums FROM `video_recharge` r  JOIN `video_user` u ON r.uid = u.uid ".$recondition.$where;
            $countr    = $this->_rechargeModel->getOne($rechargesql);
        }


        //如果充值表数据小于10  则对送礼表进行数量调整
        if(isset($recharges) && !empty($recharges)){
            $_recharge_nums = count($recharges);
            if($_recharge_nums<10){
                $page_size = (20-$_recharge_nums);
                $offset = $page_size*($page-1);
            }
        }else{
            $page_size = 20;
            $offset = $page_size*($page-1);
        }
        
        if($status != 2){
            //查询送礼表，区分主播和用户，查询用户为消费，查询主播为收入，如果送礼为主播，显示为消费
            $mailsql     = " SELECT m.points as cepoints,m.created,m.send_uid,m.rec_uid,u.uid,u.username,u.points,u.roled FROM `video_mall_list` m  JOIN `video_user` u ON u.uid = m.send_uid  
              ".$macondition.$where." ORDER BY m.created desc limit ".$offset.",".$page_size;
            $maillists = $this->_maillistModel->getAll($mailsql);
            $macsql    = " SELECT count(1) as manums  FROM `video_mall_list` m  JOIN `video_user` u ON u.uid = m.send_uid ".$macondition.$where;
            $countm    = $this->_rechargeModel->getOne($macsql);
        }
        $nums = $countm['manums'];
        $page_size = 20;
  
        
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        
        if(!empty($recharges) && !empty($maillists)){
            $list = array_merge($recharges,$maillists);
        }
        if(empty($list) && !empty($recharges)){
             $list = $recharges;
        }
        if(empty($list) && !empty($maillists)){
             $list = $maillists;
        }

        //对查询结果分析和拆分
        foreach ($list as $key => $value) {
            if($value['roled'] == 3){
                //对主播在送礼表中的状态 分析  如果送礼ID为主播ID 则显示消费   如果收礼ID为主播ID  则显示收入
                if(isset($value['rec_uid'])){
                    if($value['rec_uid'] == $value['uid']){
                        $list[$key]['repoints'] = $value['cepoints'];
                        $list[$key]['cepoints'] = 0;
                    }
                }
            }
        }
        $data['list'] = $list;
        $data['pay_type'] = $this->config['pay_type'];
        include_once("./tpl/recharge-variable.html");
    }
} 