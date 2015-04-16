<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:51
 */

class FrontConsumeModel extends FrontDBModel{
    protected static $_instance = null;
    protected $_tbl = 'video_mall_list';

    public function getTodayStatistics(){
        $_model = self::getInstance();
        $_stime = date('Y-m-d 00:00:00');
        $_etime = date('Y-m-d 23:59:59');



        $_stime = array('created'=>$_stime);
        $_etime = array('created'=>$_etime);
        $rs = $_model->ge($_stime)->le($_etime)->getAll();
        $num = 0;
        if(!empty($rs)){
            $num = count($rs);
        }

        //points：消费金额，users：消费人数,times：消费次数，roomPoints：按照房间号统计的消费金额
        $_rsArr = array('points'=>0,'users'=>0,'times'=>0,'roomPoints'=>array(),'userPoints'=>array());
        $_users = array();
        $_roomPoints = array();
        $_userPoints = array();
        if(!empty($num)){
                for($i=0;$i<$num;$i++){
                    $_rsArr['points'] += $rs[$i]['points'];
                    $_users[$rs[$i]['send_uid']] = 1;
                    $_rsArr['times']++;
                    if(!isset($_roomPoints[$rs[$i]['rid']])){
                        $_roomPoints[$rs[$i]['rid']] = $rs[$i]['points'];
                    }else{
                        $_roomPoints[$rs[$i]['rid']] += $rs[$i]['points'];
                    }
                    if(!isset($_userPoints[$rs[$i]['rec_uid']])){
                        $_userPoints[$rs[$i]['rec_uid']] = $rs[$i]['points'];
                    }else{
                        $_userPoints[$rs[$i]['rec_uid']] += $rs[$i]['points'];
                    }
                }
        }
    
        $_rsArr['users'] = count($_users);
        $_rsArr['roomPoints'] = $_roomPoints;
        $_rsArr['userPoints'] = $_userPoints;
        
        return $_rsArr;
    }
}