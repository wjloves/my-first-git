<?php
/**
 * Created by Sublime.
 * User: morgan
 * Date: 15-03-13
 * Time: 下午14:22
 * 公告
 */
header("content-type:text/html;charset=utf-8");
class AnnouncementController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->_announcementModel = EndAnnouncementModel::getInstance();
        $this->_endadminModel     = EndAdminModel::getInstance();
    }
    /**
     * [defaultAction 公告管理]
     * @author [morgan] 
     * @date(2015/03/13)
     */
    public function defaultAction(){
        //判断权限
        if(!in_array(44,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $list= $this->_announcementModel->limit($offset,$page_size)->order("id desc")->getAll();

        $count  = $this->_announcementModel->fields(array('count(id) as nums'))->getOne();
        $nums   = $count['nums'];   
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        foreach($list as  $key=>$val){
            $list[$key]['admin'] = $this->_endadminModel->fields(array('name'))->eq(array('status'=>0,'admin_id'=>$val['admin_id']))->getOne();
        }
        $data['list']  = $list;
        include_once("./tpl/announcement-list.html");
    }

    /**
     * [getNewAnnouncementAction 获取最新的公告]
     * @author morgan
     * @return JSON [JSON格式字符串]
     * date 2015/03/13
     */
    public  function   getNewAnnouncementAction(){
    	$new = $this->_announcementModel->eq(array("status"=>1))->order("id desc")->getOne();
    	echo  json_encode($new);
    }

    /**
     * [editAnnouncementAction 修改公告]
     * @author morgan
     * date 2015/03/13
     */
    public  function   editAnnouncementAction(){
    	$data = json_decode($_POST['data'],true);
        $data['content'] = urlencode($data['content']);
        $data['created'] = date('Y-m-d H:i:s',time());
        $data['admin_id'] = $this->_login['admin_id'];
        $status  = $this->global->vedioInterface(array('content'=>$data['content']),$this->config['announcement_url']);
        if($status && ($status['ret']==1)){
            $data['status'] = $status['ret'];
        }else{
            $data['status'] = 2;
        }
        //记录增加公告操作日志
        $data['content'] = urldecode($data['content']);
        $state = $this->_announcementModel->insert($data);
        if($state && ($status['ret']==1)){
            echo json_encode('操作成功');
        }else{
            echo json_encode('操作失败');
        }
    	
    }   
} 