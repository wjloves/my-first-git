<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 * 主播
 */

header("content-type:text/html;charset=utf-8");
class HostController extends BaseController{
    public function __construct(){
         parent::__construct();
         $this->_hostauditModel     = FrontHostAuditModel::getInstance();
         $this->_frontuserModel     = FrontUserModel::getInstance();
         $this->_frontliveModel     = FrontLiveModel::getInstance();
         $this->_EndhostinfoModel   = EndHostInfoModel::getInstance();
         $this->_hoststatModel      = EndHostStatModel::getInstance();
         $this->_consumeModel       = FrontConsumeModel::getInstance();
         $this->_frontgoodsModel    = FrontGoodsModel::getInstance();
         $this->_frontconsumeModel  = FrontConsumeModel::getInstance();
         $this->_fronttagModel      = FrontTagModel::getInstance();
         $this->_frontanchortagModel= FrontAnchorTagModel::getInstance();   
         $this->_endadminModel      = EndAdminModel::getInstance();
         $this->_hostratingModel    = EndHostRatingModel::getInstance();
    }
    /**
     * [defaultAction 主播业绩]
     * 
     */
    public function defaultAction(){
        //判断权限
        if(!in_array(13,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $data = MemberController::lastTime();
        //分页查询与条件
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'ctime';
        }
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
                $keyword = addslashes($_GET['keyword']);
                $this->search('',$keyword); 
        }
        if(isset($_GET['host_level']) && !empty($_GET['host_level'])){
             $this->search($_GET['host_level']);
        }
        Search::getCondition($this->_hoststatModel);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $count  = $this->_hoststatModel->fields(array('count(host_id) as nums'))->getOne();
        $nums   = $count['nums'];

        //列表数据查询
        MemberController::lastTime($data);
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
                $keyword = addslashes($_GET['keyword']);
                $this->search('',$keyword); 
        }
        if(isset($_GET['host_level']) && !empty($_GET['host_level'])){
             $this->search($_GET['host_level']);
        }
        Search::getCondition($this->_hoststatModel);
        $list = $this->_hoststatModel->limit($offset,$page_size)->getAll();
        foreach ($list as $key => $value) {

              /*获取主播类型*/
              $list[$key]['tag'] = '';
              $type = unserialize($value['host_type']);
              if(is_array($type)){
                $NewTypeArr = array();
                foreach ($type as $k => $val) {
                      $tag =  $this->_fronttagModel->eq(array('tag_id'=>$val))->getOne();
                      if($tag){
                          $NewTypeArr[]  = $tag['name'];
                      }
                }
              }
              $list[$key]['tag'] = implode(',',$NewTypeArr);
              /*获取主播昵称*/
              $redis = new redis();
              $redis->connect($this->config['redis_ip'],$this->config['redis_port']);
              $list[$key]['nickname'] = $redis->hget('huser_info:'.$value['host_id'],'nickname');
              $redis->close();
              if(empty($list[$key]['nickname'])){
                    $user = $this->_frontuserModel->fields(array('nickname'))->eq(array('uid'=>$value['host_id']))->getOne();
                    $list[$key]['nickname'] = @$user['nickname'];
              }
        }
      //主播类型
      //  $data['tags']  = $this->_fronttagModel->getAll();
        $data['list']  = $list;
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
      
        include_once("./tpl/host-stat.html");
    }

    /**
     * [search 过滤查询]
     * @param  string $level   [级别查询]
     * @param  string $keyword [关键字查询]
     * @return [Object]        [SQL对象]
     */
    public function  search($level='',$keyword=''){
        if(!empty($level)){
            $this->_hoststatModel->ge(array('host_level'=>$level))->le(array('host_level'=>($level+4)));
        }
        if(!empty($keyword)){
          if(preg_match("/^\d*$/",$keyword)){
                $this->_hoststatModel->like(array('host_id'=>$keyword));
          }else{
            $users_id = $this->_frontuserModel->fields(array('uid'))->eq(array('roled'=>3))->like(array('nickname'=>$keyword))->getAll();
            if($users_id){
                $uids = array();
                foreach ($users_id  as  $v) {
                    $uids[] = $v['uid'];
                }
              $this->_hoststatModel->in(array('host_id'=>$uids));
            }
          }
        }
        return $this->_hoststatModel;
    }

    /**
     * [summaryAction 时间汇总]
     * @return [type] [description]
     */
    public function  summaryAction(){
        $data = MemberController::lastTime();
        $_GET['search']['order'] = 'SUM('.$_GET['search']['order'].')';
        $this->search($_GET['host_level']);
        
        Search::getCondition($this->_hoststatModel);
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
                $keyword = addslashes($_GET['keyword']);
                $this->search('',$keyword); 
        }
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $hosts    = $this->_hoststatModel->fields(array("sum(host_company_income) as company_sum,sum(host_income) as host_sum,host_id,host_shares,host_level,host_type,ctime,nickname,username,dml_flag"))->group(" host_id ")->getAll();
        if($hosts){
          foreach ($hosts as $key => $value) {
              $NewArr[$value['host_id']] = array(
                    "host_income"=>0,
                    "host_shares"=>'',
                    "host_company_income"=>0,
                    "host_id"  =>$value['host_id'],
                    "username" =>$value['username'],
                    "nickname" =>$value['nickname']
              );
              if(isset($NewArr[$value['host_id']])){
                  $type = unserialize($value['host_type']);
                  if(is_array($type)){
                      $NewTypeArr = array();
                      foreach ($type as $key => $val) {
                            $tag =  $this->_fronttagModel->eq(array('tag_id'=>$val))->getOne();
                            if($tag){
                                $NewTypeArr[]  = $tag['name'];
                            }
                      }
                  }
                  $tag_arr  = implode(',',$NewTypeArr);
                  $NewArr[$value['host_id']]['tag']                     =  $tag_arr;
                  $NewArr[$value['host_id']]['host_level']              =  $value['host_level'];
                  $NewArr[$value['host_id']]['host_income']             =  $value['host_sum'];
                  $NewArr[$value['host_id']]['host_shares']             =  $value['host_shares'];
                  $NewArr[$value['host_id']]['host_company_income']     =  $value['company_sum'];
              }
          }  
        } 

        //数据分页
        $chunk_arr = @array_chunk($NewArr,$page_size);
        $result    = $chunk_arr[($page-1)];
        $nums   = count($NewArr);   
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);

        $start_time = isset($data['ge'])?$data['ge']:'0000-00-00 00:00:00';
        $end_time   = isset($data['le'])?$data['le']:date("Y-m-d H:i:s");
        $search_time = $start_time.' --- '.$end_time;
        include_once("./tpl/host-summary.html");
    }

    /**
     * [auditAction 签约审核]
     * @author [morgan] 
     * @date(2014/11/03)
     */
    public function auditAction(){
        //判断权限
        if(!in_array(18,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $uids = array();
        Search::getCondition($this->_hostauditModel);
        $uids = $this->audit_search($this->_frontuserModel);
       
        if($uids){
             $this->_hostauditModel->in(array('host_id'=>$uids));
        }
        $host_rating = $this->_hostratingModel->getAll();
        $data['host_rating'] = $this->global->towToOne($host_rating);
        //查看是否是通过关键字搜索   如果搜索返回为空 直接显示
        if(isset($_GET['keyword']) && !empty($_GET['keyword']) && empty($uids)){
           
            include_once("./tpl/host-audit.html");
            return;
        }
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $list   = $this->_hostauditModel->eq(array('is_del'=>1))->order('status asc,audit_time desc')->limit($offset,$page_size)->getAll();
        Search::getCondition($this->_hostauditModel);

        $count  = $this->_hostauditModel->fields(array('count(auid) as nums'))->eq(array('is_del'=>1,'status'=>0))->getOne();

        $nums   = $count['nums'];
        foreach($list as $key=>$val){
               $sql = "SELECT a.username,a.nickname,a.birthday,b.realname,b.phone,b.qq FROM `video_user` a LEFT JOIN `video_user_extends` b on a.uid=b.uid where a.uid=".$val['host_id'];
               $user = $this->_frontuserModel->getOne($sql);
               if($val['editor']){
                    $editor = $this->_endadminModel->fields(array('name'))->eq(array('admin_id'=>$val['editor']))->getOne();
                    $list[$key]['editor_name']  = $editor['name'];
               }
               
               if(!empty($user)){
                      $list[$key] = array_merge($list[$key],$user);        
               }else{
                    unset($list[$key]);
               }
        }
   
        $data['list']  = $list;
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        include_once("./tpl/host-audit.html");
    }

    /**
     * [audit_search description]
     * @param  string $model [description]
     * @return [type]        [description]
     */
    public function audit_search($model=''){
          if(!$model){
             return false;
          }
          $NewArr = array();
          if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
                $keyword = addslashes($_GET['keyword']);
                $uid = $model->fields(array("uid"))->like(array("nickname"=>$keyword,"username"=>$keyword,"uid"=>$keyword))->getAll();
                if(!empty($uid)){
                     foreach ($uid as  $value) {
                            $NewArr[] = $value['uid'];
                     }
                }
          }
          return $NewArr;
    }

    /**
     * [editAction AJAX编辑主播信息]
     * @author morgan
     * @date(2014/11/06)
     */
    public function ajaxEditAction(){
            //获取POST数据
            $data['status']     = intval($_POST['status']);
            $data['audit_time'] = date('Y-m-d H:i:s',time());
            $contion['uid']     = intval($_POST['uid']);
            $auid               = intval($_POST['auid']);
            $data['host_rating']        = intval($_POST['host_rating']);
            $data['editor']     = $this->_login['admin_id'];
            //查询用户信息
            $user = $this->_frontuserModel->eq(array('uid'=>$contion['uid']))->getOne();
            
            if( $data['status'] == 1 ){
                 $state = $this->global->vedioInterface($contion,$this->config['add_host_url']);
                 if($state['ret']===1){
                        $is_had = $this->_EndhostinfoModel->eq(array('uid'=>$user['uid']))->getOne();
                        //增加主播信息至vbos_host_info表
                        $tags = $this->_fronttagModel->getOne('SELECT b.`name`,b.`tag_id` FROM `video_anchor_tag`  a LEFT JOIN video_tag  b  on a.tag_id = b.tag_id WHERE a.uid='.$contion['uid']);
                        $constellation = $this->global->getConstellation(@$user['birtyday']);
                        $contion = array(
                                'uid'           => $user['uid'],
                                'nick'          => $user['nickname'],
                                'host_type'     => base64_encode(serialize($tags)),
                                'host_rating'   => $data['host_rating'],
                                'host_level'    => $user['lv_exp'],
                                'birtyday'      => @$user['birtyday'] ? (!empty($user['birtyday'])):'00-00-00 00:00:00',
                                'constellation' => $constellation ? $constellation : '没星座',
                                'ctime'         => date('Y-m-d H:i:s',time()),
                                'dml_flag'      => 1
                        );
                        if(!$is_had){
                            $insert_id = $this->_EndhostinfoModel->insert($contion);
                        }else{
                            $insert_id = $this->_EndhostinfoModel->update($contion,' uid = '.$user['uid']);
                        }
                 }else{
                      echo json_encode("失败");
                      return;
                 }
            }
          
            //修改审核信息状态
            $result    = $this->_hostauditModel->update($data,' auid = '.$auid);
            if( $result || $insert_id ){
                echo json_encode("成功");
            }else{
                echo json_encode("失败");
            }
    }

    /**
     * [ajaxDelAction 取消主播资格]
     * @param Int   $uid   [主播ID]
     * @param Array $state [取消主播接口返回数据]
     * @author morgan <[morgan@weststarinc.co]>
     */
    public function ajaxDelAction(){
            $uid = intval($_POST['id']);
            $state = $this->global->vedioInterface(array('uid'=>$uid),$this->config['del_host_url']);
            if( $state['ret']===1 ){
                $contion['dml_flag'] = 3;
                $contion['dml_time'] = date('Y-m-d H:i:s',time());
                $contion['editor']   = $this->_login['admin_id'];
                $delete_id = $this->_EndhostinfoModel->update($contion,' uid='.$uid);
            }else{
                echo json_encode("操作失败");
                return;
            }
            if($delete_id && ($state['ret']===1)){
                echo json_encode('操作成功');
            }elseif($state['ret'] == 1 && empty($delete_id)){
                $this->global->vedioInterface(array("uid"=>$uid),$this->config['add_host_url']);
                echo json_encode('操作失败');
            }else{
                echo json_encode('操作失败');
            }
    }

    /**
     * [listAction 主播列表]
     * @author [morgan] 
     * @date(2014/11/03)
     */
    public function listAction(){
    
        //判断权限
        if(!in_array(19,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $keyword = isset($_GET['keyword'])?addslashes($_GET['keyword']):'';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        /*搜索条件*/
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'uid';
        }
        if(!empty($_GET['host_type'])){
            $host_type = intval($_GET['host_type']);
        }

        Search::getCondition($this->_EndhostinfoModel);
        /*搜索关键字*/
        $this->keyword($keyword,$this->_EndhostinfoModel);
        $list   = $this->_EndhostinfoModel->neq(array('dml_flag'=>3))->limit($offset,$page_size)->getAll();

        /*搜索条件*/
        Search::getCondition($this->_EndhostinfoModel);
        /*搜索关键字*/
        $this->keyword($keyword,$this->_EndhostinfoModel);
        $count  = $this->_EndhostinfoModel->neq(array('dml_flag'=>3))->fields(array('count(uid) as nums'))->getOne();
        $nums   = $count['nums'];
        foreach($list as $key=>$val){
                //查看用户是否存在  存在：合并信息   不存在:删除此信息
                $user   = $this->_frontuserModel->fields(array('nickname,lv_exp'))->eq(array("uid"=>$val['uid']))->getOne();
                if(!empty($user)){
                   $list[$key] = array_merge($list[$key],$user);
                   //更新主播昵称和等级
                   if( ($val['nick'] != $user['nickname']) || ($val['host_level'] !=$user['lv_exp']) ){
                      $this->_EndhostinfoModel->update(array("nick"=>$user['nickname'],"host_level"=>$user['lv_exp'])," uid = ".$val['uid']);
                   }
                }else{
                   unset($list[$key]);
                   continue;
                }
                //过滤查询条件
                if(!empty($val['host_type'])){
                    $tags = unserialize(base64_decode($val['host_type']));
                    if( !empty($host_type) && (!in_array($host_type,$tags)) ){
                        unset($list[$key]);
                        continue;
                    }
                }
                
                //获取主播类型
                $list[$key]['tag'] = unserialize(base64_decode($val['host_type']));
                //查询主播是否在线
                $is_online = $this->global->vedioInterface(array('uid'=>$val['uid']),$this->config['is_online']);
                if($is_online&&($is_online['ret']==1)){
                     $list[$key]['is_online'] = $is_online['loc'];
                }
                //查询直播累计分钟数 
                $redis = new redis();
                $redis->connect($this->config['redis_ip'],$this->config['redis_port']);
                $_times =  $redis->get('live_total_time:'.$val['uid']);
                $redis->close();
                if(!empty($_times)){
                      $list[$key]['minutes'] = $this->global->sec2time($_times);
                }else{
                      $list[$key]['minutes'] = 0;
                } 
                
                //最后直播时间
                $last_time = $this->_frontliveModel->eq(array('uid'=>$val['uid']))->order('start_time desc')->limit(0,1)->getOne();
                //最后登录时间
                $sql = "SELECT a.logined,b.area FROM `video_user` a LEFT JOIN `video_area` b on a.city=b.code where a.uid=".$val['uid'];
                $area   = $this->_frontuserModel->getOne($sql);
                if(!empty($area)){
                  $list[$key] = array_merge($list[$key],$area);
                }
                
                $list[$key]['lastTime'] = '';
                if(!empty($last_time)){
                    $list[$key]['lastTime'] = $last_time['start_time'];
                }
        }
        $data['tags']  = $this->_fronttagModel->getAll();
        $data['list']  = $list;

        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        include_once("./tpl/host-list.html");
    }

    /**
     * [search 搜索]
     * @return [object] [对象]
     */
    public function keyword($keyword,$model){
        if(!empty($keyword)){
            $model->like(array('uid'=>$keyword,'nick'=>$keyword));
        }
        if(isset($_GET['host_level']) && !empty($_GET['host_level'])){
            $level = intval($_GET['host_level']);
            $model->ge(array('host_level'=>$level))->le(array('host_level'=>($level+4)));
         }
        //外部调用此页面  ---  来自主播日报表
        if(isset($_GET['ext']) && !empty($_GET['ext'])){
            $hosts = explode(',',base64_decode($_GET['ext']));
            $model->in(array("uid"=>$hosts)); 
        }
        return $model;
    }

    
    

    /**
     * [detailsAction 主播详情]
     * @author [morgan] 
     * @date(2014/11/05)
     */
    public function  detailsAction(){
         //判断权限
        if(!in_array(20,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        if(isset($_GET['dosubmit'])){
            $uid = intval($_GET['uid']);
            $list = $this->_frontuserModel->eq(array('uid'=>$uid,'roled'=>3))->getOne();
            if(!empty($list)){
                //查询主播直播次数
                $live_num = $this->_frontliveModel->fields(array('count(id) as num, sum(duration) as total_duration'))->eq(array('uid'=>$uid))->getOne();
                if($live_num){
                    $list['total_duration'] = $this->global->sec2time($live_num['total_duration']);
                    $list['live_num']      = $live_num['num'];
                }
                //查询主播是否在线
                $is_online = $this->global->vedioInterface(array('uid'=>$uid),$this->config['is_online']);
                if($is_online&&($is_online['ret']==1)){
                     $list['is_online'] = $is_online['loc'];
                }
                $sql = "SELECT a.username,b.area FROM `video_user` a LEFT JOIN `video_area` b on a.city=b.code where a.uid=".$uid;
                $user  = $this->_frontuserModel->getOne($sql);
                if($user){
                   $list = array_merge($list,$user);
                }
                //查询主播收入记录
                $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
                $page_size = 20;
                $offset = $page_size*($page-1);
                $consume_list = $this->_consumeModel->eq(array('rec_uid'=>$uid))->order('id desc')->limit($offset,$page_size)->getAll();
                $count = $this->_consumeModel->fields(array('count(id) as nums'))->eq(array('rec_uid'=>$uid))->getOne();
                $nums = $count['nums'];
                foreach ($consume_list as $key => $value) {
                       $goods = $this->_frontgoodsModel->fields(array('name'))->eq(array('gid'=>$value['gid']))->getOne();
                       $consume_list[$key]['goods_name'] = $goods['name'];
                       $user  = $this->_frontuserModel->fields(array('nickname'))->eq(array('uid'=>$value['send_uid']))->getOne();
                       $consume_list[$key]['nickname'] = $user['nickname'];

                }
               
                $data['list'] = $list;
                $data['consume_list'] = $consume_list;
                $data['pages'] = $this->global->ajax_pages($page_size,$nums,5,$page,'"consume"');
            }else{
                 $error = '没有此用户，或此用户不是主播';
            }
        }
        include_once("./tpl/host-details.html");
    }

    /**
     * [ajax ajax获取主播信息]
     * @author [morgan]
     * @date(2014/11/05) 
     */
    public function  ajaxAction(){
            $uid  = intval($_POST['uid']);
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $type = $_POST['type'];
            $page_size = 20;
            $offset = $page_size*($page-1);
            if($type=='"consume"'){
                $list = $this->_frontconsumeModel->eq(array('rec_uid'=>$uid))->order('id desc')->limit($offset,$page_size)->getAll();
                foreach ($list as $key => $value) {
                   $goods = $this->_frontgoodsModel->eq(array('gid'=>$value['gid']))->getOne();
                   $user  = $this->_frontuserModel->fields(array('nickname'))->eq(array('uid'=>$value['send_uid']))->getOne();
                   $list[$key]['nickname']   = $user['nickname'];
                   $list[$key]['goods_name'] = $goods['name'];
                   $list[$key]['points']     = round(($value['points']/10),2);
                }
                $count = $this->_frontconsumeModel->fields(array('count(id) as nums'))->eq(array('rec_uid'=>$uid))->getOne();
            }else{
                $list  = $this->_frontliveModel->eq(array('uid'=>$uid))->order("start_time desc")->limit($offset,$page_size)->getAll();
                foreach($list as $key => $val){
                        $end_time = ($val['duration']+strtotime($val['start_time']));
                        $list[$key]['end_time'] = date('Y-m-d H:i:s',$end_time);
                        $list[$key]['duration'] = $this->global->sec2time($val['duration']);
                        $list[$key]['points']   = round(($val['points']/10),2);
                }
                $count = $this->_frontliveModel->fields(array('count(id) as nums'))->eq(array('uid'=>$uid))->getOne();
            }
            $nums = $count['nums'];

            $pages = $this->global->ajax_pages($page_size,$nums,5,$page,$type);
            $data['pages'] = $pages;
            $data['num'] = count($list);
            $data['list'] = $list;
            echo json_encode($data);
    }

    /**
     * [ajaxEditTypeAction AJAX修改主播类型]
     * @return [type] [description]
     */
    public  function  ajaxEditTypeAction(){
           $uid  = intval($_POST['uid']);
           $tags = $_POST['data'];
           $is_change = $this->global->vedioInterface(array('uid'=>$uid,'tags'=>implode(',',$tags)),$this->config['edit_host_type']);

          if($is_change&&($is_change['ret']==1)){
                $tag_Arr = array();
                foreach ($tags as $key => $value) {
                    $tag_Arr[$key] = $this->_fronttagModel->eq(array('tag_id'=>$value))->getOne();
                }
                $contion['host_type'] = base64_encode(serialize($tags));
                $contion['dml_time'] = date('Y-m-d H:i:s',time());
                $contion['editor']   = $this->_login['admin_id'];
                $this->_EndhostinfoModel->update($contion, ' uid= '.$uid );
                echo json_encode("编辑成功");
          }else{
              echo json_encode("编辑失败");
          }
    }


    /**
     * [dailyAction 主播日报表]
     * @param Array  $data [查询日期]
     * @param String $le [截止时间]
     * @param String $ge [起始时间]
     * @param Array  $new_hosts_id [新开播主播ID]
     * @param Array  $today_hosts_id [当天开播主播ID]
     * @author Morgan
     * date  2015/03/23
     */
    public function dailyAction(){
         //判断权限
          if(!in_array(45,$this->_priv_arr)){
                  echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                  return;   
          }
          $data = $_GET['data'];
          if(empty($data)){
              $data['time'] = date("Y-m-d");
          }
          $le = $data['time']." 23:59:59";
          $ge = $data['time']." 00:00:00";
          $new_hosts_id   = array();
          $today_hosts_id = array();

          //当日申请主播数据
          $audit = $this->_hostauditModel->fields(array('count(host_id) as nums'))->eq(array("status"=>0,"is_del"=>1))->le(array("ctime"=>$le))->ge(array("ctime"=>$ge))->getOne();
         
          //主播总数
          $host  = $this->_EndhostinfoModel->fields(array('count(uid) as host_nums'))->neq(array('dml_flag'=>3))->getOne();
          //当日开播总数
          $result = $this->_frontliveModel->getTodayAirTime();
          $data = array(
                "new_host"=>0,
                "today_host"=>0,
                "audit"=>$audit['nums']?$audit['nums']:0,
                "host"=>$host['host_nums']?$host['host_nums']:0,
                "time"=>$data['time']
          );
          //查看新开播和当前时间开播数量
          if($result){
              $new_host = 0;
              foreach ($result as $key => $value) {
                  $uid =  $this->_frontliveModel->fields(array('uid'))->eq(array("uid"=>$value['uid']))->le(array("start_time"=>$ge))->getOne();
                  if(!$uid){
                    $new_hosts_id[] = $value['uid']; 
                    $new_host++;
                  }
                    $today_hosts_id[] = $value['uid'];
              }
              $data['new_host'] = $new_host;
              $data['today_host'] = count($result);
          }
         
          //获取当日开播主播的ID，以便进行查询
          if($data['new_host']){
              $data['new_hosts_id'] = base64_encode(implode(',',$new_hosts_id));
          }
          if($data['today_host']){
              $data['today_hosts_id'] = base64_encode(implode(',',$today_hosts_id));
          }
          $data['le'] = $le;
          $data['ge'] = $ge;
          include_once("./tpl/host-daily.html");
    }

    /**
     * [statisAction 主播直播记录统计查询]
     * @author raby
     * date   2015/03/23
     */
    public function statisAction(){
        //查询搜索条件  （未封装）
        //用户输入数据处理
        if(empty($_GET['search']['created'])){
            $_GET['search']['created'] = 'start_time';
        }
        if(empty($_GET['search']['created']['ge'])){
            $start_time = '1970-01-01';
        }else{
            $start_time = $_GET['search']['created']['ge'];
        }
        if(empty($_GET['search']['created']['le'])){
            $end_time = date('Y-m-d',time());
        }else{
            $end_time = $_GET['search']['created']['le'];
        }
        if(empty($_GET['search']['uid'])){
            $w_uid = '';
        }else{
            $w_uid = ' and a.uid='.$_GET['search']['uid'].'';
        }
        $start_time = $start_time.' 00:00:00';
        $end_time = $end_time.' 23:59:59';

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);

        //左连接，查询昵称,统计时间
        $sql = "select a.uid,b.nickname,sum(a.duration) as totalduration  from video_live_list a left join video_user b on a.uid=b.uid
                where start_time>='".$start_time."' and start_time<='".$end_time."' ".$w_uid."
                group by uid 
                limit $offset,$page_size;";
        $data['list']  = $this->_frontliveModel->getAll($sql);

        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['totalduration'] = $this->global->sec2time($value['totalduration']);

            //用户有效天数：用户直播当天当次时间大于2小时，天数+11
            //>2小时间的,按日期去重复后，再统计天数.   跨天才满2小时的，不计入有效天数，也就是只有直播开始时间22;00:00之前才有可能满足条件。同时要直接时长达到2小时（7200s）
            $temp = $this->_frontliveModel->fields(array("distinct DATE_FORMAT(start_time,'%Y-%m-%d')","count(uid) as num","uid"))->le(array("start_time"=>$end_time))->ge(array("start_time"=>$start_time))
                ->ge(array("duration"=>7200))->le(array("start_time"=>"DATE_FORMAT(start_time,'%Y-%m-%d 22:00:00')"))->eq(array("uid"=>$value['uid']))->getAll();
            $data['list'][$key]['num'] = $temp[0]['num'];
        }
        //统计指定时间内，用户记录数
        if($w_uid){
            $count  = $this->_frontliveModel->fields(array("COUNT(DISTINCT uid) as nums"))->ge(array("start_time"=>$start_time))->le(array("start_time"=>$end_time))->eq(array('uid'=>$_GET['search']['uid']))->getOne();
        }else{
            $count  = $this->_frontliveModel->fields(array("COUNT(DISTINCT uid) as nums"))->ge(array("start_time"=>$start_time))->le(array("start_time"=>$end_time))->getOne();
        }
        $nums   = $count['nums'];
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);

        include_once("./tpl/host-statis.html");
    }

} 

