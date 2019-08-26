<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\admin\model\Market as MarketModel;

class Market extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $list = MarketModel::with([
            'member' => function($query){
                $query->field('id,nick_name');
            },
            'city' => function($query){
                $query->field('id,name_zh');
            },
            'category' => function($query){
                $query->field('id,category_name');
            },
            'quanzi' => function($query){
                $query->field('id,quanzi_name');
            },
            'kaolaAdmin' => function($query){
                $query->field('id,user_number');
            }
        ])->where('is_delete', 0)->paginate(1);
//        halt($list);
        $this->assign([
            'total'  => $list->total(),
            'list' => $list
        ]);
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        echo 222;die;
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if($this->request->isPost()){


        } else {
            $info = MarketModel::with([
                'member' => function($query){
                    $query->field('id,nick_name');
                },
                'city' => function($query){
                    $query->field('id,name_zh');
                },
                'category' => function($query){
                    $query->field('id,category_name');
                },
                'quanzi' => function($query){
                    $query->field('id,quanzi_name');
                },
                'kaolaAdmin' => function($query){
                    $query->field('id,user_number');
                }
            ])->where(['id' => $id, 'is_delete' => 0])->find();
            if(!$info){
                $this->error('二手资讯不存在！');
            }
            $citys = model('City')->where('is_valid', 1)->field('id,name')->select();
            $districts = model('District')->where(['is_valid' => 1, 'city_id' => $info->city_id])->field('id,name')->select();
            $categories = model('MarketCategory')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,category_name')->select();
            $quanzi = model('MarketQuanzi')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,quanzi_name')->select();
            $property = MarketModel::getPropertyList();
            $oldnew = MarketModel::getOldnewList();
            $guarantee = MarketModel::getGuaranteeList();
            $dealArea = MarketModel::getDealAreaList();
            $delivery = MarketModel::getDeliveryList();
            $status = MarketModel::getStatusList();
            $isTop = MarketModel::getIsTopList();
            $tags = model('MarketTag')->where(['is_valid' => 1, 'is_delete' => 0])->field('id,tag_name')->select();
            $info->imageList = explode(',', $info->images);
            return $this->fetch('market/edit', [
                'info' => $info,
                'citys' => $citys,
                'districts' => $districts,
                'categories' => $categories,
                'quanzi' => $quanzi,
                'property' => $property,
                'oldnew' => $oldnew,
                'guarantee' => $guarantee,
                'dealArea' => $dealArea,
                'delivery' => $delivery,
                'status' => $status,
                'isTop' => $isTop,
                'tags' => $tags
            ]);
        }
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
