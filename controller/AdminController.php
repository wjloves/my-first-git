<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 */


class AdminController extends BaseController{
	public function __construct(){
        parent::__construct();
        $this->_endadminModel = EndAdminModel::getInstance();
        
    }

    public function defaultAction(){
        //判断权限
        if(!in_array(38,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
                $keyword = addslashes($_GET['keyword']);
                $this->_endadminModel->like(array("name"=>$keyword,"admin_id"=>$keyword)); 
        }
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $list  = $this->_endadminModel->eq(array('status'=>0))->order("ctime desc")->limit($offset,$page_size)->getAll();
        foreach ($list as $key => $value) {
            $newArr = array();
            $result =  $this->_endadminModel->getAll("SELECT a.priv_id,b.priv_name FROM `vbos_priv_user` a LEFT JOIN `vbos_priv` b on a.priv_id=b.priv_id where a.admin_id=".$value['admin_id']);
            if($result){
                foreach ($result as $k => $val) {
                    $newArr[] = $val['priv_name'];
                }
            }
            $list[$key]['priv'] = implode(',', $newArr);
        }
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
                $keyword = addslashes($_GET['keyword']);
                $this->_endadminModel->like(array("name"=>$keyword,"admin_id"=>$keyword)); 
        }
        $count  = $this->_endadminModel->fields(array('count(admin_id) as nums'))->getOne();
        $nums   = $count['nums'];
        $data['list']  = $list;      
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
       
        include_once("./tpl/admin-list.html");
    }



    /**
     * [ajaxDeleteAction AJAX删除后台用户]
     * @author morgan
     * date   2014-12-28   
     */
    public function ajaxEditAction(){
        $uid  = intval($_POST['id']);
        $type = intval($_POST['type']);
        if(empty($type)){
            $passwd = base64_decode($_POST['pw']) ; 
            $state  = $this->_endadminModel->update(array('passwd'=>md5($passwd)),' admin_id = '.$uid);
        }else{
            $state =  $this->_endadminModel->update(array('status'=>1),' admin_id = '.$uid);
        }
        
        if($state){
            echo json_encode("成功");
        }else{
            echo json_encode("失败");
        }
    }

    /**
     * [ajaxAddAction AJAX添加用户]
     * @author morgan 
     * date  2014-12-28
     */
    public function ajaxAddAction(){
            $data  = json_decode($_POST['data'],true);
            $data['passwd']  = md5(base64_decode($data['passwd']));
            $data['ctime']   = date('Y-m-d H:i:s',time());
            $data['status']  = 0;
            $is_had = $this->_endadminModel->eq(array('name'=>$data['name'],'status'=>0))->getOne();
            if($is_had){
                echo json_encode("用户名已存在");return;
            }
            $state  = $this->_endadminModel->insert($data);
            if($state){
                 echo json_encode("添加成功");
            }else{
                 echo json_encode("添加失败");
            }
    }
} 