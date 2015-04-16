<?php
/**
 * Created by PhpStorm.
 * User: morgan
 * Date: 15-01-29
 * Time: 下午15:45
 */


class LogController extends BaseController{
	public function __construct(){
        parent::__construct();
        $this->_usrlogModel  = EndActionLogModel::getInstance();
        $this->_endadminModel = EndAdminModel::getInstance();
    }

    public function defaultAction(){
   
        //判断权限
        // if(!in_array(38,$this->_priv_arr)){
        //         echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
        //         return;   
        // }
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        Search::getCondition($this->_usrlogModel);
        $list  = $this->_usrlogModel->order("created desc")->limit($offset,$page_size)->getAll();

        foreach ($list as $key => $value) {

            $user = $this->_endadminModel->fields(array('name'))->eq(array('status'=>0,'admin_id'=>$value['admin_id']))->getOne();
           
            $list[$key]['name'] = $value['admin_id'];

            if($user){
                $list[$key]['name'] = $user['name'];
            }
        }
        Search::getCondition($this->_usrlogModel);
        $count  = $this->_usrlogModel->fields(array('count(id) as nums'))->getOne();
        $nums   = $count['nums'];
        $data['list']  = $list;      
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        //获取客服列表
        $data['custom'] = $this->_endadminModel->fields(array('admin_id,name'))->eq(array('status'=>0))->getAll();
        $data['action_type']  = $this->config['action_type'];
        include_once("./tpl/log-list.html");
    }

    
} 