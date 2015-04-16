<?php
/**
 * Created by PhpStorm.
 * User: cannyco
 * Date: 14-10-23
 * Time: 下午12:45
 * 主播
 */

header("content-type:text/html;charset=utf-8");
class HostincomeController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->_videoconfigModel      = FrontConfModel::getInstance();
        $this->_withdrawalrulesModel      = FrontWithdrawalRulesModel::getInstance();
    }

    /**
     * [mainAction 主播提成]
     * @author cannyco
     * 2015/04/10
     */
    public function mainAction(){
        //判断权限
        if(!in_array(57, $this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        //主播类型，从配置表里面读
        $data['host_type']  = $this->config['host_type'];
        //读取下配置的公司提成
        $configPercentInfo = $this->_videoconfigModel->eq(array("name" => 'ticheng_company_percent'))->getOne();
        $data['company_percent'] = $configPercentInfo['value']?$configPercentInfo['value']:'0.2';
        //var_dump($data['company_percent']);exit;
        include_once("./tpl/host-income.html");
    }

    /**
     * [mainAction 设置主播提成]
     * @author morgan
     * 2015/04/10
     */
    public function SetAction()
    {
        //获取下当前是哪类主播
        $hostType = $_GET['type'];
        //主播类型列表
        $data['host_type']  = $this->config['host_type'];
        //获取下这类主播的配置信息
        $curWithdrawalResult = $this->_withdrawalrulesModel->eq(array("anchortype" => $hostType))->getAll();
        //var_dump($curWithdrawalResult);exit;
        if (!$curWithdrawalResult) {
            echo "<script>alert('配置信息为空！');location.href='./index.php?do=hostincome-main';</script>";exit;
        }
        $data['withdrawal'] = $curWithdrawalResult;
        include_once("./tpl/host-income-set.html");
    }

    /**
     * [mainAction 设置公司提成比例]
     * @author morgan
     * 2015/04/10
     */
    public function SetCPAction()
    {
        //获取下参数
        $percent = $_POST['company_percent']?$_POST['company_percent']:'';
        if (!$percent) {
            echo "<script>alert('比例值不能为空！');location.href='./index.php?do=hostincome-main';</script>";exit;
        }
        if (!is_float($percent) && $percent > 1) {
            echo "<script>alert('比例不能大于1,并且要是小数！');location.href='./index.php?do=hostincome-main';</script>";exit;
        }

        $insertArr = array('value' => $percent);
        $a = $this->_videoconfigModel->update($insertArr, "name = 'ticheng_company_percent'");
        if (!$a) {
            echo "<script>alert('更新失败！');location.href='./index.php?do=hostincome-main';</script>";exit;
        }
        echo "<script>alert('更新成功！');location.href='./index.php?do=hostincome-main';</script>";exit;
    }

    /**
     * [SethpAction 设置主播提成设置]
     * @author morgan
     * 2015/04/10
     */
    public function SetHPAction()
    {
        //其它数据不用处理，直接时长这里要乘以
        $postdata = $_POST;
        $pid = $postdata['id']?$postdata['id']:0;
        $duration = $postdata['duration']?$postdata['duration']:0;
        $minincome = $postdata['minincome']?$postdata['minincome']:0;
        $maxincome = $postdata['maxincome']?$postdata['maxincome']:0;
        $rpercentage = $postdata['rpercentage']?$postdata['rpercentage']:0;
        //因为1，8，16三条记录有部分数据是为0的，但是又为了防止另外的数据不能设置为0，故不处理1，8，16三条记录的数据
        /*if ($pid != 1 && $pid != 8 && $pid != 16) {
            if (!$duration || !$minincome || !$maxincome || !$rpercentage) {
                echo "<script>alert('有参数设置为0了');location.href='./index.php?do=hostincome-main';</script>";exit;
            }
        }*/
        $data = array(
            'duration' => $duration*60,
            'minincome' => $minincome,
            'maxincome' => $maxincome,
            'rpercentage' => $rpercentage,
        );

        $withdrawalResult = $this->_withdrawalrulesModel->update($data, "id = '" . $pid . "'");
        if (!$withdrawalResult) {
            echo json_encode(array('code' => 0, 'message' => '更新失败!'));exit;
        }
        echo json_encode(array('code' => 0, 'message' => 'success!'));exit;
    }
}