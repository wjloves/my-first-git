<?php
/**
 * Created by Sublime.
 * User: Morgan
 * Date: 14-11-03
 * Time: 下午18:11
 */
header("content-type:text/html;charset=utf-8");
class PrivController extends BaseController{

	public function __construct(){
        parent::__construct();
        $this->_endadminModel  = EndAdminModel::getInstance();
        $this->_baseC = new BaseController();
    }
	/**
	 * [defaultAction 权限列表]
	 * @author [morgan] 
	 * @date(2014/12/27)
	 */
    public function defaultAction(){
        //判断权限
        if(!in_array(39,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        if(isset($_POST['dosubmit'])){
            $uid = intval($_POST['conditions']);
            if(empty($uid)){ 
                echo "<script>alert('选择管理员');history.go(-1);</script>";return; 
            }
            $priv_arr = $_POST['priv_id'];
            $del = $this->_privuserModel->delete('admin_id ='.$uid);
            if($del){
                $sql = "INSERT INTO `vbos_priv_user`(`priv_id`,`admin_id`) values ";
                foreach ($priv_arr as $key => $value) {
                        $sql .= '('.$value;
                        $sql .= ','.$uid.'),';
                }
                $sql = substr_replace($sql,';',-1,1);
                $status = $this->_privuserModel->execute($sql);
                if($status){
                    $priv_arr = $this->_baseC->getPrivAction();
                    setcookie('priv',serialize($priv_arr));
                    echo "<script>alert('操作成功');location.href='./index.php?do=priv';</script>";
                }else{
                    
                    echo "<script>alert('操作失败');location.href='./index.php?do=priv';</script>";
                }
            }else{
                echo "<script>alert('操作失败');location.href='./index.php?do=priv';</script>";
            }
        }
        //获取顶级权限
        $list  = $this->_privModel->eq(array('parent'=>0))->getAll();
        foreach ($list as $key => $value) {
              $priv_list[$value['priv_name']] = $this->_privModel->eq(array('parent'=>$value['priv_id']))->getAll();
        }
        $priv_arr = $this->_priv_arr;

        //获取客服列表
        $data['custom'] = $this->_endadminModel->fields(array('admin_id,name'))->eq(array('status'=>0))->getAll();
        include_once("./tpl/priv-list.html");
    }

    /**
     * [ajaxPriv ajax添加权限]
     * @author [morgan]
     * @date(2014/12/27) 
     */
    public function  ajaxPrivAction(){
    		$data  = json_decode($_POST['data'],true);
            $state = $this->_privModel->insert($data);
    		if($state){
                echo json_encode("操作成功");
            }else{
                echo json_encode("操作失败");
            }
    }

    /**
     * [reBackAction AJAX回显权限]
     * @return [type] [description]
     */
    public  function ajaxRebackAction(){
            $uid    =    intval($_POST['id']);
            $result = $this->_privuserModel->fields(array('priv_id'))->eq(array('admin_id'=>$uid))->getAll();
            foreach ($result as $key => $value) {
                $priv_arr[] = $value['priv_id'];
            }
            echo json_encode($priv_arr);
    }
} 