<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 */

header("content-type:text/html;charset=utf-8");
class LoginController{
    public function __construct(){
        $this->admin = EndAdminModel::getInstance();
    }
    /**
     * [defaultAction 登陆入口]
     * @author kid 
     * @update morgan 
     * date 2015/1/1
     */
    public function defaultAction(){
        /*if(LoginClass::check()){
            header("location:./index.php?do=index-index");
        }*/
        if(isset($_POST['name']) && isset($_POST['password']) && !empty($_POST['name'])  && !empty($_POST['password']) ){
                $name = addslashes($_POST['name']);
                $pwd  = addslashes($_POST['password']);
                $info = $this->admin->fields(array('passwd','admin_id'))->eq(array('name'=>$name))->getOne();

                // if($info['is_login'] == 1){
                //         echo "<script>alert('不能重复登录');location.href='./index.php?do=login';</script>";return ;
                // }
                
                if($info['passwd'] == md5($pwd)){
                    $this->admin->update(array('ltime'=>date('Y-m-d H:i:s' ,time())),' admin_id = '.$info['admin_id']);
                    // if(!$status){
                    //     echo "<script>alert('登录失败请重新登录');location.href='./index.php?do=login';</script>";return ;
                    // }
                    $login = array(
                        'name'=>$_POST['name'],
                        'admin_id'=>$info['admin_id'],
                        'ltime'=>time()
                    );
                    $_SESSION['login'] = serialize($login);
                    header("location:./index.php?do=index-index");
                }else{
                    echo "<script>alert('密码错误');location.href='./index.php?do=login';</script>";return ;
                }
        }else{
             include_once("./tpl/login.html");
        }
       
    }

    public function logoutAction(){
       // $info = unserialize($_SESSION['login']);
        //$this->admin->update(array('is_login'=>0),' admin_id = '.$info['admin_id']);
        $_SESSION['login']=null;
        unset($_SESSION);
        setcookie('priv','');
        setcookie('isreload','');
        setcookie('PHPSESSID','');
        setcookie('action','');
        header("location:./index.php?do=login");
    }

    /**
     * [codeAction 生成验证码]
     * @param string  $pwd [验证码随机值]
     * @author morgan 
     * date 2015/1/1
     */
    public function codeAction(){
          include_once("./lib/Captcha/Captcha.php");
          $Captcha = new CaptchaClass();
          $result = $Captcha->Generate();
          $pwd = $Captcha->Phrase();
          setcookie('VCODE',md5($pwd));
          return $result;
    }


    /**
     * [ajaxVcodeAction 切换验证码]
     * @author moran 
     * date 2015/1/1
     */
    public function  ajaxVcodeAction(){
          $vcode = addslashes($_POST['code']);
          if(isset($_COOKIE['VCODE'])){
                //验证码比较  不区分大小写
                if($_COOKIE['VCODE'] == md5(strtoupper($vcode))){
                    echo 1;
                }else{
                    echo 0;
                }
          }else{
                echo  "验证错误，请联系管理员";
          }
    }
} 