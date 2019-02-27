<?php
//显示系统地址列表
function show_address_list($list){
    $str = '<ul>';
    if(empty($list)){
        $str .= '<li class="nodata center">暂无数据！</li>';
    }else{
        foreach($list as $v){
            if(isset($v['name'])){
                $ico = isset($v['_child'])?' fa-angle-double-right':' fa-leaf';
                $str .='<li class="del-remove-'.$v['id'].'" id="'.$v['id'].'" data-pid="'.$v['pid'].'">';
                $str .='    <span>';
                $str .='        <i class="fa '.$ico.'"></i>';
                $str .='        <input type="text" class="center textbox_50" value="'.$v['sortnum'].'" name="sort" data-tb="sys_address" data-id="'.$v['id'].'" placeholder="排序" />';
                $str .='        <label><input class="right checkIds" value="'.$v['id'].'"  type="checkbox"/><b class="check_list"></b>'.$v['name'].'</label>';
                $str .='    </span>';
                if(isset($v['_child'])){$str .= show_address_list($v['_child']);}
                $str .='</li>';
            }
        }
    }
    $str .='</ul>';
    return $str;
}

//常规栏目列表
function show_category_list($list,$right,$doUrl,$delUrl){
    $str = '<ul>';
    if(empty($list)){
        $str .= '<li class="nodata center">暂无数据！</li>';
    }else{
        foreach($list as $v){
            if(isset($v['name'])){
                $ico = isset($v['_child'])?' fa-angle-double-right':' fa-leaf';
                $str .='<li class="del-remove-'.$v['id'].'" id="'.$v['id'].'" data-pid="'.$v['pid'].'">';
                $str .='    <span>';
                $str .='        <i class="fa '.$ico.'"></i>';
                $str .='        <input type="text" class="center textbox_50" value="'.$v['sortnum'].'" name="sort" data-tb="sys_category" data-id="'.$v['id'].'" placeholder="排序" />';
                $str .='        <label><input class="right checkIds" value="'.$v['id'].'"  type="checkbox"/><b class="check_list"></b>'.$v['name'];if($v['code']){$str .= '【'.$v['code'].'】';}$str.='</label>';
                $str .='        <a data-href="'.$doUrl.'?pid='.$v['id'].'" title="添加子分类" class="layer_open_iframe fa fa-plus ';if($right['doCategory']!=1){$str .= 'btn-disable'; }$str .='"></a>';
                $str .='        <a data-href="'.$doUrl.'?id='.$v['id'].'" title="编辑此分类" class="layer_open_iframe fa fa-edit ';if($right['doCategory']!=1){$str .= 'btn-disable';}$str .='"></a>';
                $str .='        <a href="javascript:void(0)" data-url="'.$delUrl.'" data-id="'.$v['id'].'" class="fa fa-trash-o btn-del ';if($right['delCategory']!=1){$str .= 'btn-disable';}$str .= '" title="删除此分类"></a>';
                $str .='    </span>';
                if(isset($v['_child'])){$str .= show_category_list($v['_child'],$right,$doUrl,$delUrl);}
                $str .='</li>';
            }
        }
    }
    $str .='</ul>';
    return $str;
}


function search($match){
    $match = str_replace("<", "@", $match[1]);
    return $match;
}