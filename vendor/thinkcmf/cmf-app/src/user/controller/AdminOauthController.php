<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use think\facade\Db;
use app\user\model\ThirdPartyUserModel;
use cmf\controller\AdminBaseController;

class AdminOauthController extends AdminBaseController
{
    protected $isvip = [0=>'否',1=>'是'];
    /**
     * 后台第三方用户列表
     * @adminMenu(
     *     'name'   => '第三方用户',
     *     'parent' => 'user/AdminIndex/default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '第三方用户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('user_admin_oauth_index_view');

        if (!empty($content)) {
            return $content;
        }
            $lists = ThirdPartyUserModel::field('a.*,u.user_nickname,u.sex,u.avatar,u.isvip')
            ->alias('a')
            ->join('user u', 'a.user_id = u.id')
            ->where("status", 1)
            ->order("create_time DESC")
            ->paginate(20);
        // 获取分页显示
            $page = $lists->render();
            $this->assign('lists', $lists);
            $this->assign('page', $page);
             return $this->fetch();
        // 渲染模板输出
      
    }
    
    public function dingdan(){
        $content = hook_one('user_admin_oauth_dingdan_view');
        if (!empty($content)) {
            return $content;
        }
        $lists = ThirdPartyUserModel::field('a.third_party,a.nickname,u.*,s.avatar')
            ->alias('a')
            ->join('emoji_pay u', 'a.openid = u.openid')
            ->join('user s', 'a.user_id = s.id')
            ->where("status", 1)
            ->paginate(20);
        // 获取分页显示
            $page = $lists->render();
            $this->assign('lists', $lists);
            $this->assign('page', $page);
             return $this->fetch();
    }
    
    public function edit(){
        $content = hook_one('user_admin_oauth_edit_view');
        if (!empty($content)) {
            return $content;
        }
        $data = $this->request->param();
        $lists = ThirdPartyUserModel::field('u.id,u.isvip')
            ->alias('a')
            ->join('user u', 'a.user_id = u.id')
            ->where("a.id", $data['id'])
            ->find();
        $this->assign('isvip', $this->isvip);
        $this->assign('lists', $lists);
        return $this->fetch();
    }
    
    public function editPost(){
        if ($this->request->isPost()) {
            $data      = $this->request->param();
            $result    = Db::name('user')
             ->where('id',$data['id'])
             ->data(['isvip'=>$data['isvip']])
             ->update();
            if ($result == 0) {
                $this->error($result);
            }

            $this->success("修改成功！", url("adminOauth/index"));
        }
    }

    /**
     * 后台删除第三方用户绑定
     * @adminMenu(
     *     'name'   => '删除第三方用户绑定',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除第三方用户绑定',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $id = input('param.id', 0, 'intval');
            if (empty($id)) {
                $this->error('非法数据！');
            }

            ThirdPartyUserModel::where("id", $id)->delete();
            $this->success("删除成功！", url('AdminOauth/index'));
        }
    }


}
