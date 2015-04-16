<?php
/**
 * Created by PhpStorm.
 * User: raby
 * Date: 15-04-07
 * Time: 下午12:45
 * 主播
 */

header("content-type:text/html;charset=utf-8");
class MrechargeController extends BaseController{
    public function __construct(){
         parent::__construct();
        $this->_endMrechargeModel = EndMrechargeModel::getInstance();
        $this->_endMrechargeStatModel = EndMrechargeStatModel::getInstance();
        $this->_frontUserModel = FrontUserModel::getInstance();
        $this->_frontAgentsModel = FrontAgentsModel::getInstance();
    }
    /**
     * [defaultAction 人工充值（申请）]
     */
    public function defaultAction(){

        $data['type'] = array(0=>'代理',1=>'用户');
        $data['rechargetype'] = array(0=>'人工充值',1=>'活动奖励',2=>'平台赔偿');
        include_once("./tpl/Mrecharge-addapply.html");
    }

    /**
     * [checkAction 人工充值（申请）]
     * @param String $account [充值帐号]
     * @param String $rechargetype [充值账号类型]
     * @param String $type [充值类型]
     * @param String $plus_gold [充值金额]
     * @param String $remark [备注]
     * @author raby
     * date   2015/04/07
     */
    public function ajaxAddapplyAction(){
        //查询
        $account = isset($_POST['account']) ? trim($_POST['account']) : 0;
        $rechargetype = isset($_POST['rechargetype']) ? trim($_POST['rechargetype']) : 0;
        $type = isset($_POST['type']) ? trim($_POST['type']) : 0;
        $plus_gold = isset($_POST['plus_gold']) ? trim($_POST['plus_gold']) : 0;
        $remark = isset($_POST['remark']) ? trim($_POST['remark']) : 0;

        $data =  array(
            'account'=>$account,
            'type'=>$type,
            'plus_gold'=>$plus_gold,
            'minus_gold'=>0,
            'rechargetype'=>$rechargetype,
            'status'=>0,    //待审核
            'apply_name'=>$this->_login['name'],
            'check_name'=>'',
            'apply_time'=>date('Y-m-d h:m:s',time()),
            'check_time'=>'0000-00-00 00:00:00',
            'remark'=>$remark,
        );
        if(is_numeric($plus_gold)){
            $rs_user = $this->_frontUserModel->eq(array('uid'=>$account))->getOne();
            if(empty($rs_user)){
                echo "账号不存在";
            }else{
                $rs_insert = $this->_endMrechargeModel->insert($data);
                if($rs_insert){
                    echo "成功";
                }else{
                    echo "失败";
                }
            }
        }else{
            echo "金额必须为数据";
        }
    }
    /**
     * [checkAction 人工充值（审核）]
     * @param String status [充值帐号]
     * @param String apply_name [充值账号类型]
     * @param String check_name [充值类型]
     * @param String check_time [充值金额]\
     * @author raby
     * date   2015/04/07
     */
    public function checkAction(){
        $check_time_ge = isset($_GET['search']['check_time']['ge']) ? trim($_GET['search']['check_time']['ge']) : '0000-00-00 00:00:00';
        $check_time_le = isset($_GET['search']['check_time']['le']) ? trim($_GET['search']['check_time']['le']) : date('Y-m-d H:m:s',time());

        if(empty($check_time_ge)){
            $check_time_ge = '0000-00-00 00:00:00';
        }
        if(empty($check_time_le)){
            $check_time_le = date('Y-m-d H:m:s',time());
        }

        $a = date('Y-m-d H:m:s');
        $where = array();
        foreach($_GET['search'] as $k=>$v){
            if($v===''){
            }else{
                if(is_array($v)){
                }else{
                    $where[$k]=$v;
                }
            }
        }

        $list = $this->_endMrechargeModel->eq($where)->gt(array('plus_gold'=>0))->ge(array('check_time'=>$check_time_ge))->le(array('check_time'=>$check_time_le))->order("apply_time desc")->getAll();

        //调用代理接口----所属团队
        foreach($list as $k=>$v){
            $uid = $v['account'];
            $sql = "SELECT agentname FROM `video_agents` as a LEFT JOIN video_agent_relationship as r on a.id = r.aid WHERE r.uid=$uid";
            $agents = $this->_frontAgentsModel->getOne($sql);
            if($agents){
                $list[$k]['team'] = $agents['agentname'];
            }
        }

        $data['mrecharge_data'] = $list;
        $data['status'] = array(0=>'等待审核',1=>'审核通过',2=>'审核失败');
        include_once("./tpl/Mrecharge-check.html");
    }

    /**
     * [ajaxCheckAction AJAX人工充值（审核）]
     * @param String id [ID]
     * @param String status [是否通过]
     * @param String check_remark [审核不通过原因]
     * @author raby
     * date   2015/04/07
     */
    public function ajaxCheckAction(){
        $id = $_POST['id'];
        $status = $_POST['status'];
        $check_remark = $_POST['check_remark'];
        $rs_update = $this->_endMrechargeModel->update(array('status'=>$status,'check_name'=>$this->_login['name'],'check_time'=>date('Y-m-d H:m:s',time()),'check_remark'=>$check_remark), ' id='.$id);
        if($rs_update){
            //是否审核通过
            if($status==1){
                $rs_data = $this->_endMrechargeModel->eq(array('id'=>$id))->getOne();
                $ExcuseDate = array('uid'=>$rs_data['account'],'points'=>$rs_data['plus_gold']);
                $status  = $this->global->vedioInterface($ExcuseDate,$this->config['recharge_url']);
                if($status){
                    //是否扣款成功
                    if($status['ret']==1){
                        echo "扣款成功";

                    }else{
                        echo "扣款失败".$status['ret'];
                    }
                    $ret = $status['ret'];
                }else{
                    $ret = -1;
                }
                //写日志
                $field = array(
                    'uid'=>$rs_data['account'],
                    'admin_id'=>$this->_login['admin_id'],
                    'charge_amount'=>$rs_data['plus_gold'],
                    'content'=>$rs_data['remark'],
                    'ctime'=>date('Y-m-d H:m:s',time()),
                    'status'=>$ret,
                    'type'=>1       //`type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '手动：  1 ： 增加    2 ：  扣减',
                );
                $rs_insert = $this->_endMrechargeStatModel->insert($field);
                if($rs_insert){
                    //echo "写日志成功";
                }else{
                    echo "写日志失败";
                }
            }else{
                echo "更新成功";
            }
        }else{
            echo "更新失败";
        }
    }





    /**
     * [defaultAction 人工扣减（申请）]
     * @author raby
     * date   2015/04/07
     */
    public function minusapplyAction(){
        $data['type'] = array(0=>'代理',1=>'用户');
        include_once("./tpl/Mrecharge-minusapply.html");
    }

    /**
     * [checkAction 人工扣减（申请）]
     * @param String $account [扣减帐号]
     * @param String $rechargetype [扣减账号类型]
     * @param String $minus_gold [扣减金额]
     * @param String $remark [备注]
     * @author raby
     * date   2015/04/07
     */
    public function ajaxMinusapplyAction(){
        //查询
        $account = isset($_POST['account']) ? trim($_POST['account']) : 0;
        $type = isset($_POST['type']) ? trim($_POST['type']) : 0;
        $minus_gold = isset($_POST['minus_gold']) ? trim($_POST['minus_gold']) : 0;
        $remark = isset($_POST['remark']) ? trim($_POST['remark']) : 0;

        $data =  array(
            'account'=>$account,
            'plus_gold'=>0,
            'minus_gold'=>$minus_gold,
            'type'=>$type,
            'status'=>0,    //待审核
            'apply_name'=>$this->_login['name'],
            'check_name'=>'',
            'apply_time'=>date('Y-m-d h:m:s',time()),
            'check_time'=>'0000-00-00 00:00:00',
            'remark'=>$remark,
        );
        if(is_numeric($minus_gold)){
            $rs_user = $this->_frontUserModel->eq(array('uid'=>$account))->getOne();
            if(empty($rs_user)){
                echo "账号不存在";
            }else{
                $rs_insert = $this->_endMrechargeModel->insert($data);
                if($rs_insert){
                    echo "成功";
                }else{
                    echo "失败";
                }
            }
        }else{
            echo "金额必须是数字";
        }
    }

    /**
     * [checkAction 人工扣减（审核）]
     * @param String status [扣减帐号]
     * @param String apply_name [申请人]
     * @param String check_name [审核人]
     * @param String check_time_ge [审核开始时间]
     * @param String check_time_le [审核结束时间]
     * @author raby
     * date   2015/04/07
     */
    public function checkminusAction(){
        $check_time_ge = isset($_GET['search']['check_time']['ge']) ? trim($_GET['search']['check_time']['ge']) : '0000-00-00 00:00:00';
        $check_time_le = isset($_GET['search']['check_time']['le']) ? trim($_GET['search']['check_time']['le']) : date('Y-m-d H:m:s',time());

        if(empty($check_time_ge)){
            $check_time_ge = '0000-00-00 00:00:00';
        }
        if(empty($check_time_le)){
            $check_time_le = date('Y-m-d H:m:s',time());
        }

        $a = date('Y-m-d H:m:s');
        $where = array();
        foreach($_GET['search'] as $k=>$v){
            if($v===''){
            }else{
                if(is_array($v)){
                }else{
                    $where[$k]=$v;
                }
            }
        }

        $list = $this->_endMrechargeModel->eq($where)->gt(array('minus_gold'=>0))->ge(array('check_time'=>$check_time_ge))->le(array('check_time'=>$check_time_le))->order("apply_time desc")->getAll();

        //调用代理接口----所属团队
        foreach($list as $k=>$v){
            $uid = $v['account'];
            $sql = "SELECT agentname FROM `video_agents` as a LEFT JOIN video_agent_relationship as r on a.id = r.aid WHERE r.uid=$uid";
            $agents = $this->_frontAgentsModel->getOne($sql);
            if($agents){
                $list[$k]['team'] = $agents['agentname'];
            }
        }

        $data['mrecharge_data'] = $list;
        $data['status'] = array(0=>'等待审核',1=>'审核通过',2=>'审核失败');
        include_once("./tpl/Mrecharge-checkminus.html");
    }

    /**
     * [ajaxCheckAction AJAX人工扣减（审核）]
     * @param String id [ID]
     * @param String status [是否通过]
     * @param String check_remark [审核不通过原因]
     * @author raby
     * date   2015/04/07
     */
    public function ajaxCheckMinusAction(){
        $id = $_POST['id'];
        $status = $_POST['status'];
        $check_remark = $_POST['check_remark'];
        $rs_update = $this->_endMrechargeModel->update(array('status'=>$status,'check_name'=>$this->_login['name'],'check_time'=>date('Y-m-d H:m:s',time()),'check_remark'=>$check_remark), ' id='.$id);
        if($rs_update){
            //是否审核通过
            if($status==1){
                $rs_data = $this->_endMrechargeModel->eq(array('id'=>$id))->getOne();
                $ExcuseDate = array('uid'=>$rs_data['account'],'points'=>$rs_data['minus_gold']);
                $status  = $this->global->vedioInterface($ExcuseDate,$this->config['minus_gold_url']);
                if($status){
                    //是否扣款成功
                    if($status['ret']==1){
                        echo "扣款成功";
                    }else{
                        echo "扣款失败".$status['ret'];
                    }
                    $ret = $status['ret'];
                }else{
                    $ret = -1;
                }
                //写日志
                $field = array(
                    'uid'=>$rs_data['account'],
                    'admin_id'=>$this->_login['admin_id'],
                    'charge_amount'=>$rs_data['minus_gold'],
                    'content'=>$rs_data['remark'],
                    'ctime'=>date('Y-m-d H:m:s',time()),
                    'status'=>$ret,
                    'type'=>2       //`type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '手动：  1 ： 增加    2 ：  扣减',
                );
                $rs_insert = $this->_endMrechargeStatModel->insert($field);
                if($rs_insert){
                    //echo "写日志成功";
                }else{
                    echo "写日志失败";
                }
            }else{
                echo "更新成功";
            }
        }else{
            echo "更新失败";
        }
    }
} 