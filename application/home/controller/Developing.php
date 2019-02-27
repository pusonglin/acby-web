<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/19
 * Time: 16:39
 */

namespace app\home\controller;


class Developing extends Common
{
    public function noPage(){
        return $this->fetch('noPage');
    }
}