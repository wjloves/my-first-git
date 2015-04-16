<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:51
 */

class FrontVideoLiveModel extends FrontDBModel{
    protected static $_instance = null;
    protected $_tbl = 'video_live_list';

    public function getTodayStatistics(){
        $_model = self::getInstance();
        $_stime = date('Y-m-d 00:00:00');
        $_etime = date('Y-m-d 23:59:59');



        //当日新注册人数
        $_stime = array('created'=>$_stime);
        $_etime = array('created'=>$_etime);
        $rs['new'] = $_model->ge($_stime)->le($_etime)->getCnt();

        //当日登陆人数
        $_stime = date('Y-m-d 00:00:00');
        $_etime = date('Y-m-d 23:59:59');
        $_stime = array('logined'=>$_stime);
        $_etime = array('logined'=>$_etime);
        $rs['login'] = $_model->flush()->ge($_stime)->le($_etime)->getCnt();
        return $rs;
    }
}