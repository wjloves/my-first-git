<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:51
 */

class FrontLiveModel extends FrontDBModel{
    protected static $_instance = null;
    protected $_tbl = 'video_live_list';

    public function getTodayStatistics(){
        $_model = self::getInstance();
        $_stime = date('Y-m-d 00:00:00');
        $_etime = date('Y-m-d 23:59:59');


        $_stime = array('created'=>$_stime);
        $_etime = array('created'=>$_etime);
        $rs = $_model->ge($_stime)->le($_etime)->getAll();

        return $rs;
    }

    /**
     * [getCountTime 获取总时间]
     * @param  string $uid [用户UID]
     * @return [array or Boolean]       [description]
     * @author [morgan] <[email]>
     * @date(2014/11/03)
     */
    public function getCountTime($uid=''){
        $result = $this->getOne("SELECT sum(duration) as time FROM video_live_list where uid=".$uid);
        return $result ? $result : false;
    }

    public  function   getTodayAirTime(){
        $_stime = date('Y-m-d 00:00:00');
        $_etime = date('Y-m-d 23:59:59');
        $result = $this->getAll("SELECT uid FROM video_live_list where  start_time<='".$_etime."' AND start_time >='".$_stime."'  group by uid");
        return $result ? $result : false;
    }
}