<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/21
 * Time: 16:16
 */

namespace app\api\controller;


use think\Db;
use think\facade\Request;

class Com extends Common
{

    //检测版本号
    public function checkVersion()
    {
        check_sign(array('flag'));
        $post = Request::param();
        if (in_array($post['flag'], array(1, 2))) {
            $map = [
                ['type', '=', $post['flag']]
            ];
            $info = get_find('sys_app_version', $map, 'version,is_must,remark,url', 'id desc');
            res_api('ok', $info);
        } else {
            res_api('参数错误');
        }
    }

    //App顶部列表
    public function getNavMenu()
    {
        check_sign();
        $list = get_cate_child_list('party_news', true);
        res_api('ok', $list);
    }

    //评价新闻/评论
    public function submitComments()
    {
        check_token();
        $post = Request::post();
        global $loginId;
        $cur = Db::name('member')->where('id', '=', $loginId)->field('id,nickname,headimg')->find();
        if (empty($cur)) {
            res_api('用户信息异常');
        }
        $content = isset($post['content']) ? $post['content'] : 0;
        $id = isset($post['id']) ? $post['id'] : 0;
        $time = time();
        if ($post['is_pl'] == 2) {//回复
            if (!is_numeric($post['pl_id']) || $post['pl_id'] < 1) {
                echo json_encode(100);
            } else {
                $comment = Db::name('comments')->where(array('id' => $post['pl_id']))->find();
                if (empty($comment)) {
                    echo json_encode(100);
                } else {
                    $new = array(
                        'news_id' => $id,
                        'pl_id' => $post['pl_id'],
                        'plz_id' => $loginId,
                        'bplz_id' => $comment['plz_id'],
                        'type' => 2,
                        'content' => $content,
                        'create_time' => $time
                    );
                    $res = Db::name('comments')->insertGetId($new);
                    if ($res) {
                        $plz_name = get_find('member', array('id' => $loginId), 'nickname,headimg');
                        $data = array(
                            "com_id" => $res,
                            "user_id" => $loginId,
                            'plz_name' => $plz_name['nickname'],
                            'headimg' => $plz_name['headimg'],
                            "content" => $post["content"],
                            'name' => $post['name'],
                            "create_time" => date('Y-m-d', $time)
                        );
                        res_api('ok', $data);
                    } else {
                        res_api('评论失败');
                    }
                }
            }
        } else {//评论
            $new = array(
                'pl_id' => 0,
                'bplz_id' => 0,
                'news_id' => $id,
                'plz_id' => $loginId,
                'content' => $content,
                'type' => 1,
                'create_time' => $time
            );
            $res = Db::name('comments')->insertGetId($new);
            if ($res) {
                $plz_name = get_find('member', array('id' => $loginId), 'nickname,headimg');
                $data = array(
                    "com_id" => $res,
                    "user_id" => $loginId,
                    'plz_name' => $plz_name['nickname'],
                    "content" => $post["content"],
                    "create_time" => date('Y-m-d', $time)
                );
                res_api('ok', $data);
            } else {
                res_api('评论失败');
            }
        }
    }

    //点赞
    public function setZan()
    {
        check_token();
        $post = Request::post();
        global $loginId;
        if (!$loginId) {
            res_api('用户信息异常');
        }
        $map = [
            ['pid', '=', $post['pid']],
            ['flag', '=', $post['flag']],
            ['uid', '=', $loginId]
        ];
        $check = Db::name('library_news_zan')->where($map)->value('id');
        if ($check) {
            res_api('您已经点过赞了');
        } else {
            $time = time();
            $data = array(
                'pid' => $post['pid'],
                'flag' => $post['flag'],
                'uid' => $loginId,
                'create_time' => $time
            );
            Db::startTrans();
            $flag = false;
            $re = Db::name('library_news_zan')->insertGetId($data);
            if ($re) {
                $res = Db::name('library_news')->where('id', '=', $post['pid'])->setInc('zan_num');
                if ($res !== false) {
                    $flag = true;
                }
            }
            if ($flag) {
                Db::commit();
                res_api('ok');
            } else {
                Db::rollback();
                res_api(E_WLFM);
            }
        }

    }

    //收藏
    public function setCollect()
    {
        check_token();
        $post = Request::post();
        global $loginId;
        if (!$loginId) {
            res_api('用户信息异常');
        }
        $map = [
            ['pid', '=', $post['pid']],
            ['flag', '=', $post['flag']],
            ['uid', '=', $loginId]
        ];
        $check = Db::name('collect')->where($map)->value('id');
        if ($check) {
            Db::startTrans();
            $flag = false;
            $re = Db::name('collect')->where($map)->delete();
            if ($re) {
                $res = Db::name('collect')->where('id', '=', $post['pid'])->setDec('collect_num');
                if ($res !== false) {
                    $flag = true;
                }
            }
            if ($flag) {
                Db::commit();
                res_api('ok');
            } else {
                Db::rollback();
                res_api(E_WLFM);
            }
        } else {
            $time = time();
            $data = array(
                'pid' => $post['pid'],
                'flag' => $post['flag'],
                'uid' => $loginId,
                'create_time' => $time
            );
            Db::startTrans();
            $flag = false;
            $re = Db::name('collect')->insertGetId($data);
            if ($re) {
                $res = Db::name('collect')->where('id', '=', $post['pid'])->setInc('collect_num');
                if ($res !== false) {
                    $flag = true;
                }
            }
            if ($flag) {
                Db::commit();
                res_api('ok');
            } else {
                Db::rollback();
                res_api(E_WLFM);
            }
        }
    }

    //发现列表
    public function discover()
    {
        check_sign(array('page'));
        $post = Request::post();
        $page = isset($post['page']) ? $post['page'] : 1;
        $list = Db::name('discover_news')
            ->where('status', '=', 1)
            ->field('title,url,cover')
            ->page($page, 20)
            ->order('sort desc,create_time desc')
            ->select();
        if (!empty($list)) {
            foreach ($list as &$v) {
                $v['cover'] = get_thumb_img($v['cover'], 400, 190);
            }
        }
        res_api('ok', $list);
    }

}