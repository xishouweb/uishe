<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\WebOption;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WebOptionController extends Controller
{

    private $op_type = [
            [
                'name' => '导航栏显示分类',
                'value' => 'category',
            ],
            [
                'name' => '友情链接',
                'value' => 'links',
            ],
            [
                'name' => '轮播图',
                'value' => 'banner',
            ],
            [
                'name' => '首页配置',
                'value' => 'index',
            ],
        ];

    public function index()
    {

        return view('admin.weboption.index');
    }

    public function data(Request $request)
    {

        $op_type = $this->op_type;
        $data = [
            'code' => 0,
            'msg'   => '正在请求中...',
            'data'  => $op_type
        ];
        return response()->json($data);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $weboption)
    {
        $op_type = $weboption;
        switch ($op_type){
            case 'category':
                $categorys = Category::with('allChilds')->where('parent_id',0)->orderBy('sort','desc')->get();
                $all_categorys = Category::get();
                $data['categorys'] = $categorys;
                $data['all_categorys'] = array_column($all_categorys->toArray(), 'name', 'id');
                $data['op_type'] = $op_type;
                break;
            case 'links':

                break;
            case 'banner':

                break;
            case 'index':

                break;
        }
        return view('admin.weboption.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $weboption)
    {
        switch ($request->op_type){
            case 'category':
                DB::beginTransaction();
                $del_res = WebOption::where('op_type', 'category')->delete();
                if($del_res){
                    $add_res = WebOption::insert($request->data);
                    if($add_res){
                        DB::commit();
                        return response()->success('修改成功');
                    }
                }
                DB::rollBack();
                return response()->error(500, '修改失败');
                break;
        }
//        return redirect(route('admin.news'))->withErrors(['status'=>'系统错误']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->get('ids');
        if (empty($ids)){
            return response()->json(['code'=>1,'msg'=>'请选择删除项']);
        }
        foreach (News::whereIn('id',$ids)->get() as $model){
            //清除中间表数据
            $model->tags()->sync([]);
            //删除文章
            $model->delete();
        }
        return response()->json(['code'=>0,'msg'=>'删除成功']);
    }

}