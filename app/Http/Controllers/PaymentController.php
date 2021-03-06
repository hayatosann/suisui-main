<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Order;
// use Payjp\Charge;
// use \Payjp\Customer;

class PaymentController extends Controller
{
    //
    public function index(Request $request)
    {
        // dd($request);
        $menuid = $request->menuid;
        $menufood = $request->menufood;
        $menuprice = $request->menuprice;
        $totalprice = $request->totalprice;
        $menuQuantity = $request->menuQuantity;
        $personQuantity = $request->personQuantity;
        $Comedate = $request->Comedate;
        $ComeTime = $request->ComeTime;
        $user = Auth::user();
        // dd($request);
        $cardList = [];
    // 既にpayjpに登録済みの場合
      if (!empty($user->payjp_customer_id)) {
        // カード一覧を取得
        \Payjp\Payjp::setApiKey(config('payjp.secret_key'));
        // \Payjp\Payjp::setApiKey(config('services.payjp.secret_key'));
        $cardDatas = \Payjp\Customer::retrieve($user->payjp_customer_id)->cards->data; 
        // dd($cardDatas);
        foreach ($cardDatas as $cardData) {
          $cardList[] = [
            'id'=> $cardData->id,
            'cardNumber' =>  "**** **** **** {$cardData->last4}",
            'brand' =>  $cardData->brand,
            'exp_year' =>  $cardData->exp_year,
            'exp_month' =>  $cardData->exp_month,
            'name' =>  $cardData->name,
          ];
          // dd($cardDatas);
          }
        }
            return view('payment', ['cardList'=> $cardList,'menuid'=>$menuid,'menufood'=>$menufood,'menuprice'=>$menuprice,'totalprice'=>$totalprice,'menuQuantity'=>$menuQuantity, 'personQuantity'=>$personQuantity,'Comedate'=>$Comedate,'ComeTime'=>$ComeTime]);
    }
    public function payment(Request $request){
      // dd($request);
      if (empty($request->get('payjp-token')) && !$request->get('payjp_card_id')) {
        abort(404);
      }
      // dd($request);
    
      DB::beginTransaction();
      try {
        // ログインユーザー取得
        $user = Auth::user();
        
        // シークレットキーを設定
        \Payjp\Payjp::setApiKey(config('payjp.secret_key'));

        // ⭐️ 顧客情報登録
        $customer = \Payjp\Customer::create([
        // カード情報も合わせて登録する
        'card' => $request->get('payjp-token'),
        // 概要
        'description' => "userId: {$user->id}, userName: {$user->name}",
        ]);
        // dd($customer);
        // DBにcustomer_idを登録
        $user->payjp_customer_id = $customer->id;
        $user->save();
        // dd($user);

        $totalprice = $request->input('totalprice');

        // ⭐️ 支払い処理
        // 新規支払い情報作成
        \Payjp\Charge::create([
          "customer" => $customer->id,
          "amount" => $totalprice,
          "currency" => 'jpy',
     ]);
    
        // ⭐️ 以前使用したカードを使う場合
        if (!empty($request->get('payjp_card_id'))) {
          $customer = \Payjp\Customer::retrieve($user['payjp_customer_id']);
          // 使用するカードを設定
          $customer->default_card = $request->get('payjp_card_id');
          $customer->save();
        // ⭐️ 既にpayjpに登録済みの場合
        } elseif (!empty($user['payjp_customer_id'])) {
          // カード情報を追加
          $customer = \Payjp\Customer::retrieve($user['payjp_customer_id']);
          $card = $customer->cards->create([
            'card' => $request->get('payjp-token'),
          ]);
           // 使用するカードを設定
           $customer->default_card = $card->id;
           $customer->save();
        // ⭐️ payjp未登録の場合
        } else {
           // payjpで顧客新規登録 & カード登録
           $customer = \Payjp\Customer::create([
              'card' => $request->get('payjp-token'),
           ]);
           $user = Auth::user();
           // DBにcustomer_idを登録
           $user->payjp_customer_id = $customer->id;
          //  $user->payjp_customer_id = $request->payjp_card_id;
           $user->save();
        }

         DB::commit();
    
        return redirect('/payment_finish')->with('message', '支払いが完了しました');

        // dd($request);
        $orders = new Order();
        $orders-> menu_id= $request->menuid;
        $orders-> user_id = Auth::user()->id;
        $orders->totalprice = $request->totalprice;
        $orders->menu_amount = $request->menuQuantity;
        $orders->person_amount = $request->personQuantity;
        $orders->come_date = $request->Comedate;
        $orders->come_time = $request->ComeTime;
        $orders->save();
    
      } catch (\Exception $e) {
        Log::error($e);
        DB::rollback();
    
        if(strpos($e,'has already been used') !== false) {
          return redirect()->back()->with('error-message', '既に登録されているカード情報です');
        }
    
        return redirect()->back();
    }
    }
    public function finish()
    {
      $users = Auth::user()->name;
      return view('payment_finish',compact('users'));
    }
}