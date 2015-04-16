<?php
header("content-type:text/html;charset=utf-8");
class  GlobalClass{
    /**公用方法**/
    /**
    * [ajax_pages 分页]
    * @param  [type] $page_size    [显示条数]
    * @param  [type] $nums         [总条数]
    * @param  [type] $sub_pages    [页面显示页数]
    * @param  [type] $pageCurrent  [当前页数]
    * @param  [type] $type 		[类型]
    * @return [string]             [返回分页字符串]
    */
    public function ajax_pages($page_size,$nums,$sub_pages,$pageCurrent,$type){
        $subPageCss2Str="";
        $pages = ceil($nums/$page_size);
        if(!$pages){
            return '';
        }
        if($pageCurrent > 1) {
            $subPageCss2Str.="<li><a class='first' href='javascript:ajax_pages(".($pageCurrent-1).",".$type.");'><</a> ";
        }else {
            $subPageCss2Str.="<li><a class='first'><</a><li> ";
        }
        $a=$this->construct_num_Page($pages,$pageCurrent,$sub_pages);
        for($i=0;$i<count($a);$i++){
            $s=$a[$i];
            if($s == $pageCurrent ){
                $subPageCss2Str.="<li><a class='current'>".$s."</a></li>";
            }else{
                $subPageCss2Str.="<li><a href='javascript:ajax_pages($s,".$type.");'>".$s."</a></li>";
            }
        }

        if($pageCurrent < $pages){
            $subPageCss2Str.=" <li><a href='javascript:ajax_pages(".($pageCurrent+1).",".$type.")'>></a> </li>";
        }else {
            $subPageCss2Str.="<li><a class='last'>></a></li>";
        }
        $subPageCss2Str.="<div>共<span id='page_nums'>".$pages."</span>页&nbsp;&nbsp;&nbsp;&nbsp;跳转到<input type='text' id='gotopage'>页<input type='button' id='goto-btn' value='确定' onclick='ajaxjump(1,".$type.");'></div>";
        return  $subPageCss2Str;
    }

    /**
    * [construct_num_Page AJAX分页辅助方法]
    * @param  [type] $pages       [总页数]
    * @param  [type] $pageCurrent [当前页数]
    * @param  [type] $sub_pages   [页面显示页数]
    * @return [array]
    * @author [morgan]
    * @date(2014/11/04)
    */
    public function construct_num_Page($pages,$pageCurrent,$sub_pages){
        if($pages < $sub_pages){
            $current_array=array();
            for($i=0;$i<$pages;$i++){
                $current_array[$i]=$i+1;
            }
        }else{
            $current_array=array();
            for($i=0;$i<$sub_pages;$i++){
                $current_array[$i]=$i;
            }
            if($pageCurrent <= 3){
                for($i=0;$i<count($current_array);$i++){
                    $current_array[$i]=$i+1;
                }
            }elseif ($pageCurrent <= $pages && $pageCurrent > $pages - $sub_pages + 1 ){
                for($i=0;$i<count($current_array);$i++){
                    $current_array[$i]=($pages)-($sub_pages)+1+$i;
                }
            }else{
                for($i=0;$i<count($current_array);$i++){
                    $current_array[$i]=$pageCurrent-2+$i;
                }
            }
        }
        return $current_array;
    }

    /**
    * [pages 分页]
    * @param  [type] $page_size    [显示条数]
    * @param  [type] $nums         [总条数]
    * @param  [type] $sub_pages    [页面显示页数]
    * @param  [type] $pageCurrent  [当前页数]
    * @param  [type] $url          [指定url]
    * @return [string]             [返回分页字符串]
    */
    public function pages($page_size,$nums,$sub_pages,$pageCurrent,$url=''){
        $urlrule = $this->url_par($pageCurrent,$url);
        $subPageCss2Str="";
        $pages = ceil($nums/$page_size);
        if(!$pages){
            return '';
        }
        if($pageCurrent > 1) {
            $subPageCss2Str.="<li><a class='first' href='".$this->url_par(ceil($pageCurrent-1), $urlrule)."'><</a> ";
        }else {
            $subPageCss2Str.="<li><a class='first'><</a><li> ";
        }
        $a=$this->construct_num_Page($pages,$pageCurrent,$sub_pages);
        for($i=0;$i<count($a);$i++){
            $s=$a[$i];
            if($s == $pageCurrent ){
                $subPageCss2Str.="<li><a class='current'>".$s."</a></li>";
            }else{
                $subPageCss2Str.="<li><a href='".$this->url_par($s, $urlrule)."'>".$s."</a></li>";
            }
        }

        if($pageCurrent < $pages){
            $subPageCss2Str.="<li><a href='".$this->url_par(($pageCurrent+1), $urlrule)."'>></a> </li>";
        }else {
            $subPageCss2Str.="<li><a class='last'>></a></li>";
        }
        $subPageCss2Str.='<div>共<span id="page_nums">'.$pages.'</span>页&nbsp;&nbsp;&nbsp;&nbsp;跳转到<input type="text" id="gotopage">页<input type="button" id="goto-btn" value="确定" onclick="jump();"></div>';
        return  $subPageCss2Str;
    }

    /**
    * URL路径解析，pages 函数的辅助函数
    * @param $par 传入需要解析的变量 默认为，page={$page}
    * @param $url URL地址
    * @return URL
    */
    public function url_par($par, $url = ''){
        $par = 'page='.$par;
        if($url == '')
            $url = $this->get_url();
        $pos = strpos($url, '?');
        if($pos === false){
            $url .= '?'.$par;
        }else{
            $querystring = substr(strstr($url, '?'), 1);
            parse_str($querystring, $pars);
            $query_array = array();
            foreach($pars as $k=>$v){
                $query_array[$k] = $v;
            }
            if (isset($query_array['page']))
                unset($query_array['page']);
            $querystring = http_build_query($query_array).'&'.$par;
            $url = substr($url, 0, $pos).'?'.$querystring;
        }
        return $url;
    }


    /**
    * [get_url 获取当前页面完整URL地址]
    * @return [string] [URL地址]
    */
    public function get_url(){
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $this->safe_replace($_SERVER['PHP_SELF']) : $this->safe_replace($_SERVER['SCRIPT_NAME']);
        $path_info = isset($_SERVER['PATH_INFO']) ? $this->safe_replace($_SERVER['PATH_INFO']) : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $this->safe_replace($_SERVER['REQUEST_URI']) : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.safe_replace($_SERVER['QUERY_STRING']) : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }


    /**
    * [safe_replace 安全过滤函数]
    * @param  [type] $string [PHP_SELF]
    * @return [type]
    */
    public function safe_replace($string){
        $string = str_replace('%20','',$string);
        $string = str_replace('%27','',$string);
        $string = str_replace('%2527','',$string);
        $string = str_replace('*','',$string);
        $string = str_replace('"','&quot;',$string);
        $string = str_replace("'",'',$string);
        $string = str_replace('"','',$string);
        $string = str_replace(';','',$string);
        $string = str_replace('<','&lt;',$string);
        $string = str_replace('>','&gt;',$string);
        $string = str_replace("{",'',$string);
        $string = str_replace('}','',$string);
        return $string;
    }

    //调用视频接口
    /*
    * $route    Sting   接口路由
    * $data     Array   请求参数 eg:array(
            'uid'=> '10000',
            'points'=>'10',
     );
    * @return    Array   注释：如果有值返回数组，否则返回布尔值-FALSE
    * Auther    morgan
    * Date      2014/11/03
    */
    function  vedioInterface($data,$url,$method="GET"){
            foreach ($data as $key => $value) {
            $url.=$key.'='.$value.'&';
            }
           
            $result = file_get_contents($url);
            if($result){
                return json_decode($result,true);
            }
            return false;
    }

    /**
    *getConstellation 根据出生生日取得星座
    *@param String $brithday 用于得到星座的日期 格式为yyyy-mm-dd
    *@param Array $format 用于返回星座的名称
    *@return String
    */
    public function getConstellation($birthday, $format=null){
            $pattern = '/^d{4}-d{1,2}-d{1,2}$/';
            if (!preg_match($pattern, $birthday, $matchs)){
                return null;
            }
            $date  =  explode('-', $birthday);
            $year  =  $date[0];
            $month =  $date[1];
            $day   =  $date[2];
            if ($month <1 || $month>12 || $day < 1 || $day >31) {
                return null;
            }
            //设定星座数组
            $constellations = array(
                  '摩羯座', '水瓶座', '双鱼座', '白羊座', '金牛座', '双子座',
                  '巨蟹座','狮子座', '处女座', '天秤座', '天蝎座', '射手座',);
            //或 ‍‍$constellations = array(
                  // 'Capricorn', 'Aquarius', 'Pisces', 'Aries', 'Taurus', 'Gemini',
                  // 'Cancer','Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius',);
            //设定星座结束日期的数组，用于判断
            $enddays = array(19, 18, 20, 20, 20, 21, 22, 22, 22, 22, 21, 21,);
            //如果参数format被设置，则返回值采用format提供的数组，否则使用默认的数组
            if ($format != null){
                $values = $format;
            }else{
                $values = $constellations;
            }
            //根据月份和日期判断星座
            switch ($month){
                case 1:
                  if ($day <= $enddays[0]){
                    $constellation = $values[0];
                  }else{
                    $constellation = $values[1];
                  }
                  break;
                case 2:
                  if ($day <= $enddays[1]){
                    $constellation = $values[1];
                  }else{
                    $constellation = $values[2];
                  }
                  break;
                case 3:
                  if ($day <= $enddays[2]){
                    $constellation = $values[2];
                  } else{
                    $constellation = $values[3];
                  }
                  break;
                case 4:
                  if ($day <= $enddays[3]){
                    $constellation = $values[3];
                  }else{
                    $constellation = $values[4];
                  }
                  break;
                case 5:
                  if ($day <= $enddays[4]){
                    $constellation = $values[4];
                  } else{
                    $constellation = $values[5];
                  }
                  break;
                case 6:
                  if ($day <= $enddays[5]){
                    $constellation = $values[5];
                  }else{
                    $constellation = $values[6];
                  }
                  break;
                case 7:
                  if ($day <= $enddays[6]){
                    $constellation = $values[6];
                  }else{
                    $constellation = $values[7];
                  }
                  break;
                case 8:
                  if ($day <= $enddays[7]){
                    $constellation = $values[7];
                  }else{
                    $constellation = $values[8];
                  }
                  break;
                case 9:
                  if ($day <= $enddays[8]){
                    $constellation = $values[8];
                  }else{
                    $constellation = $values[9];
                  }
                  break;
                case 10:
                  if ($day <= $enddays[9]){
                    $constellation = $values[9];
                  } else{
                    $constellation = $values[10];
                  }
                  break;
                case 11:
                  if ($day <= $enddays[10]){
                    $constellation = $values[10];
                  }else{
                    $constellation = $values[11];
                  }
                  break;
                case 12:
                  if ($day <= $enddays[11]){
                    $constellation = $values[11];
                  } else{
                    $constellation = $values[0];
                  }
                  break;
            }
            return $constellation;
    }

    /**
     * [sec2time 时间转换]
     * @param  [int]  $times     [非时间戳格式秒数]
     * @return string $result     [字符串格式]
     * @author morgan
     * @date    2014-12-26
     */
    public function sec2time($times){
        $result = '';
        if ($times>0) {
                  $hour = floor($times/3600);
                  $minute = floor(($times-3600 * $hour)/60);
                  $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
                  if($hour){
                    $result .= $hour.'小时';
                  }
                  if($minute){
                    $result .= $minute.'分';
                  }
                  if($second){
                    $result .= $second.'秒';
                  }
                  
        }
        return $result;
    }

   /**
    * [import_excel 加载导出excel程序]
    * @param  [type] $import_arr [数据]
    * @param  [type] $title      [excel title]
    * @author  morgan 
    * date  2015/04/10
    */
    public function import_excel($import_arr,$title){
      $xmlexcel = new XmlExcel();
      $xmlexcel->setDefaultWidth(100);
      $xmlexcel->setDefaultAlign("center");
      $xmlexcel->setDefaultHeight(18);

      $xmlexcel->addHead($title);
      foreach ($import_arr as $val) {

          $xmlexcel->addRow($val);
      }
      $import_name=date('Y-m-d_H_i_s',time());
      $xmlexcel->export($import_name);
      die;
    }

    /**
     * [array_multi2single 多维数据转一维]
     * @param  [type] $array [description]
     * @return [type]        [description]
     * @author morgan
     * data 2015/04/10
     */
    public function array_multi2single($array){
        static $result_array=array();
        foreach($array as $value){
            if(is_array($value)){
                array_multi2single($value);
            }else{
                $result_array[]=$value;
            }
        }
        return $result_array;
    }
    /**
     * [$array 获取评级类型  并由二维数组转一维数组]
     * @var array
     * return array()
     */
    public function towToOne($array=array()){
        foreach ($array as $key => $value) {
              $NewArr[$value['auid']] = $value['rating_name'];
        }
        return $NewArr;
    }
}