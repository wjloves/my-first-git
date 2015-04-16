<?php
header("content-type:text/html;charset=utf-8");
class  VideoInterFaceClass{
    public function __construct(){
        parent::__construct();
         
    }

    public static function FaceStart(){
        $data['hid'] = $_GET['hid'];
        $data['hname'] = $_GET['hname'];
        $type = $_GET['8f0d4c1d5f31b8340228caba1aab7486'];
        $actions = VideoInterFaceClass::ActionType();
        VideoInterFaceClass::$actions[$type]($data);
    }

    public static function EditHostName($data = array()){
        if(empty($data['hid']) || empty($data['hname'])){
            echo  json_encode(array("status"=>-103,"msg"=>"参数错误"));  
        }
        $hid    = intval($data['hid']);
        $hname  = addslashes($data['hname']);

        //查询主播是否存在
        $_EndhostinfoModel   = EndHostInfoModel::getInstance();

        $host_msg  = $_EndhostinfoModel->fields(array("uid"))->eq(array("uid"=>$hid))->getOne();
        if(!$host_msg){
            echo  json_encode(array("status"=>-102,"msg"=>"查询不到，此主播信息")); return;
        }
        //修改主播昵称
        $result = $_EndhostinfoModel->update(array("nick"=>$hname)," uid = ".$hid);
        if($result){
            echo  json_encode(array("status"=>1,"msg"=>"修改成功")); return;
        }else{
            echo  json_encode(array("status"=>-101,"msg"=>"修改失败"));return;
        }
    }

    public static  function  ActionType(){
        $actions = array(
                "hostnick"=>"EditHostName"
            );
        return $actions;
    }
}