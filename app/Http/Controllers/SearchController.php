<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Menu;

class SearchController extends Controller
{
    //
    public function genre_search()
    {
        // return view('genre_search');
    }
    public function index()
    {
        $menus = Menu::all();;
        return view('index')->with('menus',$menus);
    } 
    public function search(Request $request)
    {
        $keyword_food = $request->food;
        $keyword_price = $request->price;

        if(!empty($keyword_food) && empty($keyword_price)) {
            $query = Menu::query();
            $menus = $query->where('food','like', '%' .$keyword_food. '%')->get();
            $message = "「". $keyword_food."」を含む名前の検索が完了しました。";
            return view('/index')->with([
              'menus' => $menus,
              'message' => $message,
            ]);
        }
        elseif(empty($keyword_food) && $keyword_price ){
        $query = Menu::query();
        $menus = $query->where('price','>=', $keyword_price)->get();
        $message = $keyword_price. "円以上の検索が完了しました";
        return view('/index')->with([
          'menus' => $menus,
          'message' => $message,
        ]);
      }
      elseif(empty($keyword_food) && $keyword_price){
        $query = Menu::query();
        $menus = $query->where('price','<=', $keyword_price)->get();
        $message = $keyword_price. "円以下の検索が完了しました";
        return view('/index')->with([
          'menus' => $menus,
          'message' => $message,
        ]);
      }


      elseif(!empty($keyword_food) && $keyword_price){
        $query = Menu::query();
        $menus = $query->where('food','like', '%' .$keyword_food. '%')->where('price','>=', $keyword_price)->get();
        $message = "「".$keyword_food . "」を含む名前と". $keyword_price. "円以上の検索が完了しました";
        return view('/index')->with([
          'menus' => $menus,
          'message' => $message,
        ]);
      }
      else {
        $message = "検索結果はありません。";
        return view('/index')->with([
            'message'=>$message,
        ]);
      }
    }
}
