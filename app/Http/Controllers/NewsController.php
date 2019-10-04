<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryNew;
use App\Models\Comments;
use App\Models\News;
use App\Models\Tag;
use App\Models\TagNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    //

    public function category(Request $request, $category){
        $request = $request->toArray();
        $request['category'] = $category;
        return $this->getNewsAllList($request);
    }

    public function tag(Request $request, $tag){
        $request = $request->toArray();
        $request['tag'] = $tag;
        return $this->getNewsAllList($request);
    }

    public function getNewsList(Request $request){
        $request = $request->toArray();
        return $this->getNewsAllList($request);
    }
    public function getNewsAllList($request){
        if(!is_array($request)){
            $request = $request->toArray();
        }
        $new_query = News::query();
        $new_query->where('status', 'normal');
        $cat_new_id = [];
        if (isset($request['category'])) {
            $category_id = Category::where('alias', $request['category'])->value('id');
            $category_list = Category::where([
                    ['parent_id', '=', $category_id]
                ])
                ->pluck('name');
            $cat_new_id = CategoryNew::where('cat_id', $category_id)->pluck('cat_new_id')->toArray();
            $new_query->whereIn('id', $cat_new_id);
        }
        $tag_new_id = [];
        if (isset($request['tag'])) {
            $tag_id = Tag::where('name', $request['tag'])->value('id');
            $tag_new_id = TagNew::where('tag_id', $tag_id)->pluck('tag_new_id')->toArray();
            $new_query->whereIn('id', $tag_new_id);
        }

        if(isset($request['search'])){
            $new_query->where('title','like','%'.$request['search'].'%');
        }

        $new_query->select(['id', 'cover_img', 'title']);
        if(isset($request['orderby']) && in_array($request['orderby'], ['created_at', 'comment_count', 'views', 'like'])){
            $new_query->orderBy($request['orderby'], 'desc');
        }

        $data = $new_query->orderBy('id', 'desc')->simplePaginate(20);
        $tag = $new_query->select('tag')->get();
        $tag_list = [];
        foreach ($tag as $key=>$item){
            $tag_list = array_merge($tag_list, json_decode($item->tag, JSON_UNESCAPED_UNICODE));
        }

        $recommend_tag = Tag::where('recommend', Tag::Tag_Recommend_On)->pluck('name');
        $data = [
            'data' => $data,
            'category' => $request['category'] ?? NULL,
            'tag' => $request['tag'] ?? NULL,
            'tag_list' => array_unique($tag_list),
            'recommend_tag' => $recommend_tag
        ];
        if(isset($category_list)){
            $data['category_list'] = $category_list;
        }
        return view('home.list', $data);
    }


    public function item(Request $request, $id){
        $new = News::find($id);
        $user = Auth::user();
        $vip = true;
        if ($user === NULL || $user->vip==0){
            $vip = false;
        }

        if ( !$vip ){
            $news_recommend = News::getRecommendNews();
            $tags_recommend = Tag::getRecommendTags();
            $comments_recommend = Comments::getRecommendComments();
        }
        $recommend_tag = Tag::where('recommend', Tag::Tag_Recommend_On)->pluck('name');
        return view('home.png',[
            'new' => $new,
            'vip' => $vip,
            'news_recommend' => $news_recommend,
            'tags_recommend' => $tags_recommend,
            'comments_recommend' => $comments_recommend,
            'recommend_tag' => $recommend_tag
        ]);
//        return view('templet');
    }
}
