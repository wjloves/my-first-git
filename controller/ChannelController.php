<?php
/**
 * Created by Sublime.
 * User: morgan
 * Date: 14-12-12
 * Time: 上午11:50
 */

header("content-type:text/html;charset=utf-8");
class ChannelController extends BaseController{
	public function __construct(){
        parent::__construct();
        $this->_endchannelModel     = EndChannelModel::getInstance();
        $this->_endchannelstatModel = EndChannelStatModel::getInstance(); 
        $this->_endpersonalModel    = EndPersonalChannelStatModel::getInstance();
       
    }

    /**
     * [defaultAction 推广渠道]
     * @author morgan 
     * date   2014/12/12
     */
    public function defaultAction(){
        //判断权限
        if(!in_array(33,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $data['keyword']  = isset($_GET['keyword'])?addslashes($_GET['keyword']):'';
        /*搜索条件*/
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'created';
        }
        Search::getCondition($this->_endchannelstatModel);
        /*搜索关键字*/
        $this->keyword($data['keyword'],$this->_endchannelstatModel);
        $list  = $this->_endchannelstatModel->limit($offset,$page_size)->getAll();
        /*搜索条件*/
        Search::getCondition($this->_endchannelstatModel);
        /*搜索关键字*/
        $this->keyword($data['keyword'],$this->_endchannelstatModel);
        $count  = $this->_endchannelstatModel->fields(array('count(id) as nums'))->getOne();
        $nums   = $count['nums'];
        $data['list']  = $list;
     
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
       
        $data['channel_type']  = $this->config['channel_type'];
        include_once('./tpl/channel.html');
    }

    /**
     * [search 搜索]
     * @return  object
     * @author  morgan
     * date   2014/12/12
     */
    public function keyword($keyword,$model){
        if($keyword){
               $model->like(array('name'=>$keyword));
        }
        return $model;
    }

    /**
     * [ajaxChannelAction AJAX新增渠道]
     * @param Array      $data                     [新增数据组]
     * @author morgan 
     * date   2014/12/12
     */
    public function  ajaxChannelAddAction(){
            //接收数据
            $data            = json_decode($_POST['data'],true);
            $data['author']  = $this->_login['admin_id'];
            $data['created'] = date('Y-m-d H:i:s',time());
            $status = $this->_endchannelModel->insert($data);
            if($status){
                  echo json_encode("生成成功");
            }else{
                  echo json_encode("生成失败");
            }
    }

    public function  listAction(){
        Search::getCondition($this->_endchannelstatModel);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $list = $this->_endchannelstatModel->getAll();
        $_ge  = isset($_GET['search']['ctime']['ge'])?$_GET['search']['ctime']['ge']:'0000-00-00 00:00:00';
        $_le  = isset($_GET['search']['ctime']['ge'])?$_GET['search']['ctime']['ge']:date('Y-m-d H:i:s',time());
        $NewArr = array();
        if($list){
          foreach ($list as $key => $value) {
              $NewArr[$value['cid']] = array(
                          'cid' =>$value['cid'],
                          'amount'=>0,
                          'arrive'=>0,
                          'trigger'=>0,
                          'reg_num'=>0,
                          'reg_cost'=>0,
                          'reg_shares'=>0,
                          'total_recharge'=>0,
                          'total_money'=>0,
                          'total_bill'=>0,
                          'num'=>0

                );
              $NewArr[$value['cid']]['cid']     =  $value['cid'];
              if(isset($NewArr[$value['cid']])){
                  $NewArr[$value['cid']]['name']   =  $value['name'];
                  $NewArr[$value['cid']]['type']   =  $value['type'];
                  $NewArr[$value['cid']]['amount'] +=  $value['amount'];
                  $NewArr[$value['cid']]['arrive'] +=  $value['arrive'];
                  $NewArr[$value['cid']]['trigger'] +=  $value['trigger'];
                  $NewArr[$value['cid']]['reg_num'] +=  $value['reg_num'];
                  $NewArr[$value['cid']]['reg_cost'] +=  $value['reg_cost'];
                  $NewArr[$value['cid']]['reg_shares'] +=  $value['reg_shares'];
                  $NewArr[$value['cid']]['total_recharge'] +=  $value['total_recharge'];
                  $NewArr[$value['cid']]['total_money'] +=  $value['total_money'];
                  $NewArr[$value['cid']]['total_bill'] +=  $value['total_bill'];
                  $NewArr[$value['cid']]['num']++;
              }
          }  
          foreach ($NewArr as $k => $val) {
                @$NewArr[$k]['reg_shares'] = round(($NewArr[$k]['reg_shares']/$NewArr[$value['cid']]['num']),2);
                @$NewArr[$k]['total_bill'] = round(($NewArr[$k]['total_bill']/$NewArr[$value['cid']]['num']),2);
                unset($NewArr[$value['cid']]['num']);
          } 
        }
        //数据分页
        $chunk_arr = @array_chunk($NewArr,$page_size);
        $result    = @$chunk_arr[($page-1)];
        $nums      = count($NewArr);   
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        $data['page_nums'] = ceil($nums/$page_size);
        $search_time = $_ge.' --- '.$_le;
        $data['channel_type']  = $this->config['channel_type'];
        include_once("./tpl/channel-list.html"); 
    }

    /**
     * [managementAction 推广管理]
     * @author morgan
     * date 2014/12/13
     */
    public function   managementAction(){
        //判断权限
        if(!in_array(36,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $data['keyword']  = isset($_GET['keyword'])?addslashes($_GET['keyword']):'';
        /*搜索条件*/
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'created';
        } 
        Search::getCondition($this->_endchannelModel);
        /*搜索关键字*/
        $this->keyword($data['keyword'],$this->_endchannelModel);
        $list  = $this->_endchannelModel->eq(array('is_del'=>0))->limit($offset,$page_size)->getAll();
        /*搜索条件*/
        Search::getCondition($this->_endchannelModel);
        /*搜索关键字*/
        $this->keyword($data['keyword'],$this->_endchannelModel);
        $count  = $this->_endchannelModel->fields(array('count(id) as nums'))->eq(array('is_del'=>0))->getOne();
        $nums   = $count['nums'];
        $data['list']  = $list;
        
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        $data['page_nums'] = ceil($nums/$page_size);
        $data['channel_type']  = $this->config['channel_type'];
        include_once('./tpl/management.html');
    }

    /**
     * [ajaxChannelEditAction AJAX编辑推广信息]
     * @param Array $data [修改信息]
     * @param Int   $id   [业务ID]
     * @author morgan
     * date  2010/12/13
     */
    public function ajaxChannelEditAction(){
            $data            = json_decode($_POST['data'],true);
            $id              = intval($_POST['id']);
            $data['type']    = intval($data['type']);
            $status = $this->_endchannelModel->update($data," id=".$id);
            if($status){
                  echo json_encode("编辑成功");
            }else{
                  echo json_encode("编辑失败");
            }
    }

    /**
     * [ajaxChannelDelAction AJAX删除推广信息]
     * @param Int   $id   [业务ID]
     * @author morgan
     * date  2010/12/13
     */
    public function ajaxChannelDelAction(){
            $id = intval($_GET['id']);
            $status = $this->_endchannelModel->update(array("is_del"=>1)," id=".$id);
            if($status){
                  echo json_encode("删除成功");
            }else{
                  echo json_encode("删除失败");
            }
    }
    
    /**
     * [personalAction 个人推广]
     * @author morgan <[email]>
     */
    public function personalAction(){
            //判断权限
            if(!in_array(37,$this->_priv_arr)){
                    echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                    return;   
            }
            $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $page_size = 20;
            $offset = $page_size*($page-1);
            $data['keyword']  = isset($_GET['keyword'])?addslashes($_GET['keyword']):'';
            $data['invit']     = isset($_GET['invit'])?addslashes($_GET['invit']):'';
            $data['invite_num']  = array(
                                     "1"=>"1-100",
                                     "101"=>"101-200",
                                     "201"=>"201-500",
                                     "501"=>"501-1000",
                                     "1001"=>"1001-2000",
                                     "2001"=>"2001-5000"
                                   );
            /*搜索条件*/
            if(empty($_GET['search']['order'])){
                $_GET['search']['order'] = 'created';
            } 
            Search::getCondition($this->_endpersonalModel);
            /*搜索关键字*/
            $this->search($data['keyword'],$data['invit']);
            $list  = $this->_endpersonalModel->limit($offset,$page_size)->getAll();
            /*搜索条件*/
            Search::getCondition($this->_endpersonalModel);
            /*搜索关键字*/
            $this->search($data['keyword'],$data['invit']);
            $count  = $this->_endpersonalModel->fields(array('count(id) as nums'))->getOne();
            $nums   = $count['nums'];
            $data['list']  = $list;
            
            $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
            $data['page_nums'] = ceil($nums/$page_size);
            include_once('./tpl/channel-personal.html');
    }

    /**
     * [search 搜索]
     * @return  object
     * @author  morgan
     * date   2014/12/12
     */
    public function search($keyword,$invit){
        if($keyword){
               $this->_endpersonalModel->eq(array('uid'=>$keyword));
        }
        if($invit){
                switch ($invit) {
                    case '1':
                        $this->_endpersonalModel->ge(array('invite_num'=>1))->le(array('invite_num'=>100));
                        break;
                    case '101':
                        $this->_endpersonalModel->ge(array('invite_num'=>101))->le(array('invite_num'=>200));
                        break;
                    case '201':
                        $this->_endpersonalModel->ge(array('invite_num'=>201))->le(array('invite_num'=>500));
                        break;
                    case '501':
                        $this->_endpersonalModel->ge(array('invite_num'=>501))->le(array('invite_num'=>1000));
                        break;
                    case '1001':
                        $this->_endpersonalModel->ge(array('invite_num'=>1001))->le(array('invite_num'=>2000));
                        break;
                    case '2001':
                        $this->_endpersonalModel->ge(array('invite_num'=>2001))->le(array('invite_num'=>5000));
                        break;
                    default:
                       break;
                }
               
        }
        return $this->_endpersonalModel;
    }
} 