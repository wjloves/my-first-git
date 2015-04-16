<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:51
 */

class FrontChargeListModel extends FrontDBModel{
    protected static $_instance = null;
    protected $_tbl = 'video_charge_list';

    public function getTodayStatistics(){
        $_model = self::getInstance();
        $_stime = date('Y-m-d 00:00:00');
        $_etime = date('Y-m-d 23:59:59');

        $_stime = array('created'=>$_stime);
        $_etime = array('created'=>$_etime);
        $rs = $_model->ge($_stime)->le($_etime)->getAll();
        $num = count($rs);

        //points：充值金额，users：充值人数,times：充值次数
        $_rsArr = array('points'=>0,'users'=>0,'times'=>0);
        $_users = array();
        for($i=0;$i<$num;$i++){
            $_rsArr['points'] += $rs[$i]['points'];
            $_users[$rs[$i]['uid']] = 1;
            $_rsArr['times']++;
        }
        $_rsArr['users'] = count($_users);
        return $_rsArr;
    }
}