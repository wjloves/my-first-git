<?php
/**
 * Created by PhpStorm.
 * User: morgan
 * Date: 14-12-09
 * Time: 上午9:58
 */

header("content-type:text/html;charset=utf-8");
class CustomServiceController extends BaseController{
	public function __construct(){
        parent::__construct();
        $this->_frontmailModel = FrontMailModel::getInstance();
        $this->_endadminModel  = EndAdminModel::getInstance();
        $this->_frontuserModel = FrontUserModel::getInstance();
        $this->_endtimetaskModel = EndTimingTaskModel::getInstance();
        
    }

    /**
     * [defaultAction 系统消息日志表]
     * @author morgan 
     * date   2014/12/9
     */
    public function defaultAction(){
        //判断权限
        if(!in_array(26,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $data['keyword']      = isset($_GET['keyword']) ? addslashes($_GET['keyword']) : '';
        $data['custom_name']  = isset($_GET['custom_name']) ? addslashes($_GET['custom_name']) : '';
        $_GET['search']['order'] = 'created';
        Search::getCondition($this->_frontmailModel);
        /*搜索关键字*/
        $this->keyword($data['keyword']);
        $list  = $this->_frontmailModel->fields(array('id','send_uid','rec_uid','content','category','status','created','mail_type'))->eq(array('category'=>1))->limit($offset,$page_size)->getAll();
        /*搜索条件*/
        Search::getCondition($this->_frontmailModel);
        /*搜索关键字*/
        $this->keyword($data['keyword']);
        $count  = $this->_frontmailModel->fields(array('count(id) as nums'))->eq(array('category'=>1))->getOne();
        $nums   = $count['nums'];
        $data['list']  = $list;
        foreach ($data['list'] as $key => $value) {
                $user = $this->_endadminModel->fields(array('name'))->eq(array('admin_id'=>$value['send_uid']))->getOne();
                $data['list'][$key]['sent_user'] = $user['name'];
        }
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
     
        //获取客服列表
        $data['custom'] = $this->_endadminModel->fields(array('admin_id,name'))->eq(array('status'=>0))->getAll();
        $data['mail_type']  = $this->config['mail_type'];
        $data['level_rich'] = $this->_frontuserModel->getAll("SELECT level_id FROM `video_level_rich`");
        include_once('./tpl/custom-list.html');
    }

    /**
     * [search 搜索]
     * @return  object   
     * @author morgan 
     * date   2014/12/9
     */
    public function keyword($keyword){
        if(!empty($keyword)){
            $this->_frontmailModel->like(array('rec_uid'=>$keyword,'content'=>$keyword));
        }
        return $this->_frontmailModel;
    }

    /**
     * [ajaxMesaageAction AJAX发送系统消息]
     * @param String   $data['content']      [消息内容]
     * @param Int      $data['mail_type']    [系统消息分类]
     * @param Int      $data['category']     [消息类别]
     * @param Int      $data['status']       [消息状态0：未读 1：已读]
     * @param Date     $data['created']      [创建时间]
     * @param Int      $data['send_uid']     [发送者用户ID]
     * @param Int      $data['rec_uid']      [接收者用户ID]
     * @author morgan 
     * date   2014/12/9
     */
    public function  ajaxMesaageAction(){
            $choose  = intval($_POST['choose']);
            $data['content']   =  $content = addslashes($_POST['content']);
            $data['mail_type']    = intval($_POST['type']);
            $data['category']     = 1;
            $data['status']       = 0;
            $data['created']      = date('Y-m-d H:i:s',time());
            $data['send_uid'] = $this->_login['admin_id'];

            //判断是群发 OR  单独发送
            if($choose == 1){
                    $data['rec_uid']  = intval($_POST['uid']);
                    $status[] = $this->_frontmailModel->insert($data);
            }else{
                    $levle = $data['lv_rich'] = intval($_POST['levle']);
                    if($levle!=0){
                       $this->_frontuserModel->ge(array('lv_rich'=>$levle));
                    }
                    $users = $this->_frontuserModel->fields(array('count(uid) as num'))->getOne();
                   
                    if($users['num'] > 2000){
                        $data['content'] = base64_encode($data['content']);
                        $status[] = $this->_endtimetaskModel->insert(array('conditions'=>serialize($data)));
                    }else{
                        if($levle!=0){
                            $this->_frontuserModel->ge(array('lv_rich'=>$levle));
                        }
                        $result = $this->_frontuserModel->fields(array('uid'))->getAll();
                        if($result){
                            $sql = "INSERT INTO `video_mail`(`send_uid`,`rec_uid`,`content`,`category`,`status`,`created`,`mail_type`) values ";
                            foreach ($result as $key => $value) {
                                   $sql .= '('.$data['send_uid'];
                                   $sql .= ','.$value['uid'];
                                   $sql .= ','."'".$data['content']."'";
                                   $sql .= ','.$data['category'];
                                   $sql .= ','.$data['status'];
                                   $sql .= ','."'".$data['created']."'";
                                   $sql .= ','.$data['mail_type'].'),';
                            }
                            $sql = substr_replace($sql,';',-1,1);
                            $status[] = $this->_frontmailModel->execute($sql);
                        }
                    }
            }
             if(in_array(false, $status)){
               echo json_encode("添加失败");
            }else{
               echo json_encode("添加成功");
            }
    }
} 