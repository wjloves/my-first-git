<?php 
/**
 *  定时任务指定运行文件
 */
class  TaskClass{
	
   public function __construct(){
       
        $this->_frontmailModel = FrontMailModel::getInstance();
        $this->_endtimingtaskModel = EndTimingTaskModel::getInstance();
       
    }

   public function  mail_taskAction(){
           $perform = $this->_endtimingtaskModel->eq(array('status'=>0))->getAll();
           foreach($perform  as  $key => $value ){
                       $data = unserialize($value['conditions']);
                       $data['content'] = base64_decode($data['content']);
                       if($data['lv_rich']!=0){
                           $this->_frontuserModel->gt(array('lv_rich'=>$data['lv_rich']));
                       }
                       $result = $this->_frontuserModel->fields(array('uid'))->getAll();
                       $users  = array_chunk($result,1);
                       foreach ($users as $u => $user) {
                                $sql = "INSERT INTO `video_mail`(`send_uid`,`rec_uid`,`content`,`category`,`status`,`created`,`mail_type`) values ";
                                foreach ($user as $k => $val) {
                                    $sql .= '('.$data['send_uid'];
                                    $sql .= ','.$val['uid'];
                                    $sql .= ','."'".$data['content']."'";
                                    $sql .= ','.$data['category'];
                                    $sql .= ','.$data['status'];
                                    $sql .= ','."'".$data['created']."'";
                                    $sql .= ','.$data['mail_type'].'),';
                                }
                                $sql = substr_replace($sql,';',-1,1);
                                $this->_frontmailModel->execute($sql);
                                usleep(500);
                       }
                       $this->_endtimingtaskModel->update(array("status"=>1),' id= '.$value['id']);
                       
           }
   }
}