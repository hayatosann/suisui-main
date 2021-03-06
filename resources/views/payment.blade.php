@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/payment.css') }}">
@section('content')

<h1 class="check">＜注文詳細確認＞</h1>

{{-- <form action="payment_finish.blade.php" method="post"> --}}
    <div class="order-check">
        <span>選んだメニュー{{$menufood}}</span><span>{{$menuQuantity}}個</span><br>       
            <span>人数</span><span>{{$personQuantity}}人</span><br>
        <span>来店日時{{$Comedate}}：</span><span>{{$ComeTime}}</span>
    </div>
    <div class="order-amount">
        <span>合計金額:¥ {{$totalprice}}</span><span></span>
    </div>
    <div class="btn">
      <button type="button" onClick="history.back()" class="button">戻る</button>
    </div>
{{-- </form> --}}


<div class="payment-check">
@if (session('error-message'))
  <p>{{ session('error-message') }}</p>
@endif

  <form action="/payment_check" method="post" class="text-center mt-xxl">
    @csrf

    <input type="hidden" name="menuid" value="{{$menuid}}">
    <input type="hidden" name="menufood" value="{{$menufood}}">
    <input type="hidden" name="menuprice" value="{{$menuprice}}">
    <input type="hidden" name="totalprice" value="{{$totalprice}}">
    <input type="hidden" name="menuQuantity" value="{{$menuQuantity}}">
    <input type="hidden" name="personQuantity" value="{{$personQuantity}}">
    <input type="hidden" name="Comedate" value="{{$Comedate}}">
    <input type="hidden" name="ComeTime" value="{{$ComeTime}}">
    <script
      src="https://checkout.pay.jp/"
      class="payjp-button"
      data-key="{{ config('services.payjp.public_key') }}"
      {{-- data-key="pk_test_720a7b7eeb3f7e4927d6e3be" --}}
      data-text="カード情報を入力"
      data-submit-text="カードを登録する"
     ></script>
     {{-- {{ dd(config('services.payjp.public_key')) }} --}}
  </form>

  {{-- {{dd($cardData)}} --}}
  @if (!empty($cardList))
    <p>もしくは登録済みのカードで支払い</p>
    <form action="/payment" method="post">
      @csrf
      <input type="hidden" name="totalprice" value="{{$totalprice}}">
      <input type="hidden" name="menuid" value="{{$menuid}}">
      <input type="hidden" name="menufood" value="{{$menufood}}">
      <input type="hidden" name="menuprice" value="{{$menuprice}}">
      <input type="hidden" name="menuQuantity" value="{{$menuQuantity}}">
      <input type="hidden" name="personQuantity" value="{{$personQuantity}}">
      <input type="hidden" name="Comedate" value="{{$Comedate}}">
      <input type="hidden" name="ComeTime" value="{{$ComeTime}}">
        @foreach ($cardList as $card)
          <div class="card-item">
            <label>
              <input type="radio" name="payjp_card_id" value="{{ $card['id'] }}" />
              <span class="brand">{{ $card['brand'] }}</span>
              <span class="number">{{ $card['cardNumber'] }}</span>
            </label>
            <div>
              <p>名義: {{ $card['name'] }}</p>
              <p>期限: {{ $card['exp_year'] }}/{{ $card['exp_month'] }}</p>
            </div>
          </div>
        @endforeach
        <div class="btn2">
          <button type="submit" class="button1">注文確定</button>
        </div>
      </form>
    @endif
      
</div>

@endsection