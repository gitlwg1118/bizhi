<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

require 'vendor/autoload.php';
use QL\QueryList;
use think\facade\Db;
use cmf\controller\AdminBaseController;

class CaiJiController extends AdminBaseController
{
    
    protected $type = ["1" => "表情包", "2" => "头像","3" => "壁纸"];
    
    // 采集
    public function Index()
    {
        $content = hook_one('admin_caiji_index_view');
        $cat = Db::name('emoji_category')
          ->field('id,name,cat_type')
          ->where('cat_type',1)
          ->select();
        $this->assign('cat', $cat);
        return $this->fetch();
    }
    
    public function do(){
        $datas    = $this->request->param();
        $doutula = "https://www.doutula.com/photo/list/?page=";
        $ql = QueryList::get($doutula.$datas['num']);
        $url = $ql->find('.img-responsive')->attrs('data-original');
        $name = $ql->find('.img-responsive')->attrs('alt');
        //采集doutula某页面所有的图片
        foreach( $url as $k=>$v){  
          if( $v !=null){
              $data[$k]['url'] = $v;
              $data[$k]['name'] = $name[$k];
          }
        }
       //print_r($data);exit;
        //存入数据库
        foreach ($data as $k=>$v){
                Db::name("emoji")->insert([
                'cat_id'      => $datas['cat_id'],
                'name'        => $v['name'],
                'title'       => $v['name'],
                'edesc'       => $v['name'],
                'authorId'    => 0,
                'width'       => 450,
                'height'      => 450,
                'type'        => 1,
                'click'       => rand(100,10000),
                'share'       => rand(100,10000),
                'status'      => 'normal',
                'src'         => $v['url'],
                'cdnurl'      => $v['url'],
                'createtime'  => time(),
        ]);
        }
        //打印结果
        $this->success("采集成功");
    }
    //最火表情包采集
    public function zuihuo(){
        $cat = Db::name('emoji_category')
          ->field('id,descs,cat_type')
          ->select();
        $arr = [];
        foreach ($cat as $key => $valve) {
            foreach ($cat as $k => $v){
                if($valve['cat_type'] == $v['cat_type']){
                    $arr[] = $v;
                }
            }
            $res[$valve['cat_type']] = $arr;
            $arr = [];
        }
        $this->assign('res', json_encode($res));
        $this->assign('type', $this->type);
        return $this->fetch();
    }
    public function czuihuo(){
        $data    = $this->request->param();
        $nun_data = count($data);
        if($nun_data > 1){
            $res = $this->postcurl($data);
            $total = 0;
            $a = array_column($res, 'list');
            foreach($res as $k=>$v){$total=$total+$v['total'];}
            $al = [];
            foreach ($a as $key=>$val){
                foreach ($val as $k=>$v){
                    $al = array_merge_recursive($al, $v);
                }
            }
            for($i = 0;$i < $total;$i++){
                    $all[$i] = [
                     'cat_id' => $data['category_id'],
                     'uniqid' => $al['uniqid'][$i],
                     'name' => $al['name'][$i],
                     'title' => $al['title'][$i],
                     'edesc' => $al['desc'][$i],
                     'authorId' => $al['authorId'][$i],
                     'width' => $al['width'][$i],
                     'height' => $al['height'][$i],
                     'type' => $al['type'][$i],
                     'click' => $al['click'][$i],
                     'share' => $al['share'][$i],
                     'status' => $al['status'][$i],
                     'src' => $al['cdnurl'][$i],
                     'cdnurl' => $al['cdnurl'][$i],
                     'createtime' => time(),
                 ];
            }
            session('caiji_data', $all);
            $this->success("获取成功！请开始采集");
        }elseif ($nun_data == 1) {
            $i = $data['sql_index'];
            $re = session('caiji_data');
            $num_se = 0;
            if(isset($re[$i])){
                $find = Db::name('emoji')->where('cdnurl',$re[$i]['cdnurl'])->count();
                if($find == 1){
                    $this->success("数据库已存在",'',$re[$i]);
                }else{
                    Db::name('emoji')->data($re[$i])->insert();
                    $num_se = $num_se+1;
                    $this->success("采集成功表情",'',$re[$i]);
                }
            }else{$this->error("采集完成",'');}
             
        }
        
    }
    
    public function postcurl($data){
        for($i = 1;$i <= $data['num'];$i++){
            $datas = [
                'page' => $i,
                'name' => $data['name'],
                'type' => $data['type'],
                'category_id' => $data['category_id'],
                'order' => $data['order'],
                'is_make' => $data['is_make'],
                ];
            $url = "https://www.hotemoji.cn/api//Wxapp/list";
            $header = array(
              "POST /api//Wxapp/list HTTP/1.1",
              "Host: www.hotemoji.cn",
              "Connection: keep-alive",
              "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36 MicroMessenger/7.0.9.501 NetType/WIFI MiniProgramEnv/Windows WindowsWechat",
              "content-type: application/json",
              "Referer: https://servicewechat.com/wxf8a1bdc59198beff/27/page-frame.html",
              "Accept-Encoding: gzip, deflate, br",
              );
            $datas  = json_encode($datas); 
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datas);
            curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            $re = json_decode($output,true);
            $res[$i] = $re['data'];
        }

        return $res;
    }
    
}
