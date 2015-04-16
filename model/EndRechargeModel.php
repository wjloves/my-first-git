<?php
/**
 * Created by Sublime.
 * User: morgan
 * Date: 14-11-03
 * Time: 早晨09:57
 */

class EndRechargeModel extends BackDBModel{
    protected static $_instance = null;
    protected $_tbl = 'vbos_mrecharge_stat';

    public function getTodayStatistics(){
        $_model = self::getInstance();
        $_stime = date('Y-m-d 00:00:00');
        $_etime = date('Y-m-d 23:59:59');


        $_stime = '2014-11-29 00:00:00';
        $_etime = '2014-12-04 23:59:59';

        $_stime = array('ctime'=>$_stime);
        $_etime = array('ctime'=>$_etime);
        $rs = $_model->ge($_stime)->le($_etime)->getAll();
        $num = count($rs);

        //points：充值金额，users：充值人数,times：充值次数
        $_rsArr = array('charge_amount'=>0,'users'=>0,'times'=>0);
        $_users = array();
        for($i=0;$i<$num;$i++){
            $_rsArr['charge_amount'] += $rs[$i]['charge_amount'];
            $_users[$rs[$i]['uid']] = 1;
            $_rsArr['times']++;
        }
        $_rsArr['users'] = count($_users);
        return $_rsArr;
    }
}