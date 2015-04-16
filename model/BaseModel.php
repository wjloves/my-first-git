<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:51
 */

class BaseModel{
    const EQ =  '=';
    const NEQ = '<>';
    const LE = '<=';
    const GE = '>=';
    const LT = '<';
    const GT = '>';
    const IN = 'IN';

    protected static $_instance = null;
    protected $_db = null;
    protected $_tbl = null;
    protected $_fields = '*';
    protected $_where = array();
    protected $_order = '';
    protected $_limit = '';
    protected $_group = '';
    protected $_like  = '';


    private function __clone(){}
    private function __construct(){}

    public static function getInstance(){
        if(static::$_instance === null){
            static::$_instance = new static;
        }
        return static::$_instance;
    }

    public function flush(){
        $this->_where = array();
        $this->_fields = '*';
        $this->_limit = '';
        $this->_like   = '';
        return static::$_instance;
    }

    public function fields(array $fields){
        if(count($fields)>0)
            $this->_fields = implode(',',$fields);
        return static::$_instance;
    }

    public function order($order){
        if(is_array($order) && count($order)>0)
            $this->_order = implode(',',$order);
        elseif(is_string($order))
            $this->_order = $order;
        return static::$_instance;
    }
    public function group($group){
        if(is_string($group))
            $this->_group = $group;
        return static::$_instance;
    }
    protected function _setWhere(array $where,$op){
        $cnt = count($where);
        if($cnt>0){
            foreach($where as $_f=>$_v){
                if(is_numeric($_v)){
                    $this->_where[] = $_f.$op.$_v;
                }elseif(is_string($_v)){
                    $this->_where[] = $_f."$op'".addslashes($_v)."'";
                }elseif(is_array($_v)){
                    $this->_where[] = $_f." $op (".implode(',', $_v).")";
                }else{
                    @trigger_error($where[$_f].' is error',E_USER_NOTICE);
                }
            }
        }
    }

    public function like(array $where){
        $i = 0;
        foreach ($where as $key => $value) {
                $this->_like .= $key." LIKE '%".$value."%'";
                if((count($where)-1)>$i){
                    $this->_like .= ' or ';
                    $i++;
                }
        }
        return static::$_instance;
    }

    public function eq(array $where){
        $this->_setWhere($where,self::EQ);
        return static::$_instance;
    }

    public function neq(array $where){
        $this->_setWhere($where,self::NEQ);
        return static::$_instance;
    }

    public function lt(array $where){
        $this->_setWhere($where,self::LT);
        return static::$_instance;
    }

    public function le(array $where){
        $this->_setWhere($where,self::LE);
        return static::$_instance;
    }

    public function gt(array $where){
        $this->_setWhere($where,self::GT);
        return static::$_instance;
    }

    public function ge(array $where){
        $this->_setWhere($where,self::GE);
        return static::$_instance;
    }

    public function in(array $where){
        $this->_setWhere($where,self::IN);
        return static::$_instance;
    }

    public function limit($offset=0,$num=null){
        if($offset>=0 && is_numeric($offset)){
            $this->_limit = ' limit '.$offset;
            if($num>0 && is_numeric($num))
                $this->_limit .= ','.$num;
        }
        return static::$_instance;
    }

    private function _getSql(){
        $sql = '';
        if(is_array($this->_fields)>0)
            $this->_fields = implode(',',$this->_fields);
        $sql .= "SELECT {$this->_fields} FROM ".$this->_tbl;
        $where = implode(' and ',$this->_where);
        if(strlen($where)>1)
            $sql .= ' where '.$where.' ';
        if( (strlen($this->_like)>1) and (strlen($where)<1) ){
            $sql .= ' where '.$this->_like.' ';
        }elseif( (strlen($this->_like)>1) and (strlen($where)>1)){
             $sql .= ' and '.$this->_like.' ';
        }

        if(strlen($this->_group))
            $sql .= ' group by '.$this->_group;

        if(strlen($this->_order))
            $sql .= ' order by '.$this->_order;
        $sql .= $this->_limit;
        return $sql;
    }

    public function getAll($sql=null){
        if(!empty($sql))
            return $this->_db->get_all($sql);

        $rs = $this->_db->get_all($this->_getSql());
        $this->flush();
        return $rs;
    }

    public function getCnt($sql=null){
        //count($table,$field="*", $where="")
        $where = implode(' and ',$this->_where);
        //$this->flush();
        return $this->_db->count($this->_tbl,1,$where);
    }

    public function getOne($sql=null){
        if(!empty($sql))
            return $this->_db->get_one($sql);
        $rs = $this->_db->get_one($this->_getSql());
        $this->flush();
        return $rs;
    }

    public function execute($sql=null){
        return $this->_db->execute($sql);
    }

    public function debug(){
        echo "<pre>";
        var_dump($this->_where);
        echo "<br />";
        var_dump($this->_limit);
    }

    public function insert($data=null){
        return $this->_db->insert($this->_tbl,$data);
    }

    public function insert_id($data=null){
        return $this->_db->insert_id();
    }

    public function  update($data,$where){
        return $this->_db->update($this->_tbl,$data,$where);
    }

    public  function delete($where){
        return $this->_db->delete($this->_tbl,$where);
    }

}