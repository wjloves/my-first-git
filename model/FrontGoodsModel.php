<?php
/**
 * Created by PhpStorm.
 * User: morgan
 * Date: 14-10-30
 * Time: 下午12:51
 */

class FrontGoodsModel extends FrontDBModel{
    protected static $_instance = null;
    protected $_tbl = 'video_goods';

    /**
     * [getNameLike description]
     * @param  string $name [description]
     * @return [array or Boolean]       [description]
     * @author [morgan] <[email]>
     * @date(2014/11/03)
     */
    public function getNameLike($name=''){
        $result = $this->getOne('SELECT gid FROM video_goods where name like "%'.$name.'%"');
        return $result ? $result : false;
    }    
}