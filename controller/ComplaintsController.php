<?php
/**
 * Created by Sublime.
 * User: morgan
 * Date: 14-12-10
 * Time: 上午11:30
 */

header("content-type:text/html;charset=utf-8");
class ComplaintsController extends BaseController{
	public function __construct(){
        parent::__construct();
        $this->_frontcomplainsModel = FrontComplaintsModel::getInstance();
        $this->_endadminModel  = EndAdminModel::getInstance();
        $this->_frontuserModel = FrontUserModel::getInstance();
        
    }

    /**
     * [defaultAction 系统消息日志表]
     * @author morgan 
     * date   2014/12/9
     */
    public function defaultAction(){
        //判断权限
        if(!in_array(27,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $data['keyword']      = isset($_GET['keyword'])?addslashes($_GET['keyword']):'';
        $data['custom_name']  = isset($_GET['custom_name'])?addslashes($_GET['custom_name']):'';
        //排序
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'created';
        }
        /*搜索条件*/
        Search::getCondition($this->_frontcomplainsModel);
        /*搜索关键字*/
        $this->keyword($data['keyword']);
        $list  = $this->_frontcomplainsModel->limit($offset,$page_size)->getAll();
        /*搜索条件*/
        Search::getCondition($this->_frontcomplainsModel);
        /*搜索关键字*/
        $this->keyword($data['keyword']);
        $count  = $this->_frontcomplainsModel->fields(array('count(id) as nums'))->getOne();
        $nums   = $count['nums'];
        $data['list']  = $list;
        foreach ($data['list'] as $key => $value) {
                $user = $this->_endadminModel->fields(array('name'))->eq(array('admin_id'=>$value['editors_id']))->getOne();
                $data['list'][$key]['editors'] = $user['name'];
        }
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
      
        //获取客服列表
        $data['custom'] = $this->_endadminModel->fields(array('admin_id,name'))->eq(array('status'=>0))->getAll();
        $data['mail_type']  = $this->config['mail_type'];
        include_once('./tpl/complaints-list.html');
    }

    /**
     * [search 搜索]
     * @return  object
     * @author  morgan
     * date   2014/12/9
     */
    public function keyword($keyword){
        if(!empty($keyword)){
            $this->_frontcomplainsModel->like(array('uid'=>$keyword,'content'=>$keyword));
        }
        return $this->_frontcomplainsModel;
    }

    /**
     * [ajaxGetOneAction description]
     * @return [type] [description]
     */
    public  function  ajaxGetOneAction(){
          $id = intval($_GET['id']);
          $result = $this->_frontcomplainsModel->eq(array("id"=>$id))->getOne();
          echo json_encode($result); 
    }

    /**
     * [ajaxMesaageAction AJAX处理用户投诉]
     * @param Int      $id                     [投诉ID]
     * @param String   $data['results']        [消息内容]
     * @param Int      $data['status']         [消息状态0：待处理 1：已处理]
     * @param Date     $data['edit_time']      [编辑时间]
     * @param Int      $data['editors_id']     [处理建议管理员ID]
     * @author morgan 
     * date   2014/12/9
     */
    public function  ajaxEditAction(){
            //接收数据
            $id                   = intval($_POST['id']);
            $data['results']      = addslashes($_POST['results']);
            $data['status']       = intval($_POST['status']);
            $data['edit_time']    = date('Y-m-d h:i:s',time());
            $data['editors_id']   = $this->_login['admin_id'];
            $status = $this->_frontcomplainsModel->update($data,' id = '.$id);
            if($status){
                  echo json_encode("编辑成功");
            }else{
                  echo json_encode("编辑失败");
            }
    }
    
} 