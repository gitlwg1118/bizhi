<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\emoji\controller;

use think\facade\Db;
use think\Image;
use cmf\controller\RestBaseController;
use think\Validate;

class WxappController extends RestBaseController
{
    protected $type = ["1" => "emoticon", "2" => "headimg", "3" => "wallpaper"];
    protected $lanmu = ["0" => "热门表情包", "1" => "热门头像", "2" => "热门壁纸"];
    // 首页
    public function index()
    {
        $data = $this->request->param();
        //热门列表
        $hot = Db::name('emoji')
          ->where('type',$data['type'])
          ->order('id desc')
          ->limit(8)
          ->select();
        $hotHead = Db::name('emoji')
          ->where('type',$data['type']+1)
          ->order('id desc')
          ->limit(8)
          ->select();
        // $hotWall = Db::name('emoji')
        //   ->where('type',$data['type']+2)
        //   ->order('id desc')
        //   ->limit(4)
        //   ->select();
        foreach ($this->lanmu as $k=>$v){
            if($k == 0){
                $hotList[$k] = [
                  'type'   => $k+1,
                  'title'  => $v,
                  'data'   => $hot,
                  'order'  => 'sort desc, click desc'
                ];
            }elseif($k == 1){
                $hotList[$k] = [
                  'type'   => $k+1,
                  'title'  => $v,
                  'data'   => $hotHead,
                  'order'  => 'sort desc, id desc'
                ];
            }
            // else{
            //     $hotList[$k] = [
            //       'type'   => $k+1,
            //       'title'  => $v,
            //       'data'   => $hotWall,
            //       'order'  => 'sort desc, id desc'
            //     ];
            // }
        }
        
        //最近更新
        $num_rec_per_page=18;
        if (isset($data["page"])) { $page  = $data["page"]; } else { $page=1; }; 
        $start_from = ($page-1) * $num_rec_per_page; 
        //if (isset($data["category_id"])) { $data["category_id"]  =$data["category_id"]; } else { $data["category_id"]=0; }; 
        $map['type'] = $data['type'];
        //$map['cat_id'] = $data['category_id'];
        $count = Db::name('emoji')->where($map)->count();
        
        $List = Db::name('emoji')
          ->where($map)
          ->order('id', 'desc')
          ->limit($start_from,$num_rec_per_page)
          ->select();
        $newList['list'] = $List;
        $newList['current_page'] = (int)$page;
        $newList['next_page'] = $page+1;
        $newList['totla'] = $num_rec_per_page;
        $newList['limit'] = $num_rec_per_page;
        
        //热门词
        $hotSearch['list'] = ["抖音","沙雕","可爱","套路","晚安","亲亲"];
        //navBanner
        $navBanner = Db::name('emoji_nav')
          ->where('type',2)
          ->order('id', 'desc')
          ->limit(3)
          ->select();
        foreach ($navBanner as $k=>$v){
            $bnv[$k] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'flag' => $v['flag'],
                'nickname' => $v['nickname'],
                'desc' => htmlspecialchars_decode($v['ndesc']),
                'image' => $v['image'],
                ];
        }        
        //indexNav
        $indexNav = Db::name('emoji_nav')
          ->where('type',1)
          ->order('id', 'desc')
          ->limit(4)
          ->select();
        foreach ($indexNav as $k=>$v){
            $inv[$k] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'flag' => $v['flag'],
                'nickname' => $v['nickname'],
                'desc' => htmlspecialchars_decode($v['ndesc']),
                'image' => $v['image'],
                ];
        }
        $lunbo = [0 => 'https://cdn.hotemoji.cn/uploads/20200703/14a19dd670df764c346e66d61845459b.jpg'];
        $this->success("ok", ['hotList'=>$hotList,'hotSearch'=>$hotSearch,'indexNav'=>$inv,'navBanner'=>$bnv,'groupList'=>$hotList,'newList'=>$newList,'lunbo'=>$lunbo]);
    }
    
    public function getCategory(){
        $data = $this->request->param();
        $q = array_search($data['cat_type'],$this->type);
        $datas = Db::name('emoji_category')
          ->where('cat_type', $q)
          ->select();
        $this->success("ok", $datas);
        
    }
    
    public function list(){
        $data = $this->request->param();
        $num_rec_per_page=18;
        
        if (isset($data["page"])) { $page  = $data["page"]; } else { $page=1; }; 
        $start_from = ($page-1) * $num_rec_per_page; 
        if (isset($data["category_id"])) { $data["category_id"]  =$data["category_id"]; } else { $data["category_id"]=0; }; 

        if($data["category_id"] == 0){
            $map['type'] = $data['type'];
        }else{
            $map['type'] = $data['type'];
            $map['cat_id'] = $data['category_id'];
        }
        $count = Db::name('emoji')->where($map)->count();
        if(isset($data['name'])){
          $list = Db::name('emoji')
           ->where($map)
           ->order('id', 'desc')
           ->whereLike('edesc','%'.$data['name'].'%')
           ->limit($start_from,$num_rec_per_page)
           ->select();
        }else{
          $list = Db::name('emoji')
           ->order('id', 'desc')
           ->where($map)
           ->limit($start_from,$num_rec_per_page)
           ->select();  
        }

        if(empty(json_decode($list))){
            print_r($list);exit;
        }else{
            $this->success("ok", ['list' => $list,'limit'=>$num_rec_per_page,'current_page'=>(int)$page,'next_page'=>$page+1,'totla'=>$num_rec_per_page]);
        }
        
    }
    
    public function detail(){
        $data = $this->request->param();
        $datas = Db::name('emoji')
             ->where('id',$data['id'])
             ->find();
        $this->success("ok",$datas);
    }
    
    public function getInfo(){
        $data = $this->request->param();
        $datas = Db::name('emoji')
             ->where('id',$data['id'])
             ->find();
        $this->success("ok",$datas);
    }
    
    public function collect(){
        $data = $this->request->param();
        $num_rec_per_page=18;
        $start_from = $data['page'];
        $token =  $this->request->header('token');
        $uid = Db::name("user_token")
            ->where('token', $token)
            ->value('user_id');
        $ids = Db::name("emoji_collect")
            ->where('userid', $uid)
            ->field('emojiid')
            ->select();
        $ids =array_column(json_decode($ids),'emojiid');
        $count = Db::name('emoji')->where('id','in',$ids)->count();
        $list = Db::name('emoji')
          ->where('id','in',$ids)
          ->order('id', 'desc')
          ->limit($start_from,$num_rec_per_page)
          ->select();
        $this->success("success", ['list' => $list,'limit'=>$num_rec_per_page,'page'=>(int)$start_from+1,'totla'=>$count]);
    }
    
    public function getCollect(){
        $data = $this->request->param();
        $res = ['userid'=>$data['userId'],'emojiid'=>$data['emoticonId']];
        $re = Db::name('emoji_collect')
             ->where($res)
             ->find();
        if($re){
            $this->success("success");
        }else{
            $this->error("error");
        }
    }
    
    public function addCollect(){
        $data = $this->request->param();
        $re = Db::name('emoji_collect')
          ->data([
                'userid' =>$data['userId'],
                'emojiid' =>$data['emoticonId']
              ])
          ->insert();
        if($re == 1){
            $this->success("success", $re);
        }else{
            $this->success("error");
        }
        
    }
    
    public function addShare(){
        $data = $this->request->param();
        $re = Db::name('emoji')->where('id',$data['id'])->find();
        $this->success('success',$re);
    }
    
    
}
