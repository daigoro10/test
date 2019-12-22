<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Product;
use App\Cart;
use App\Cartitem;
use App\Purchase;
use App\Favorite;
use Hash;

class ImageController extends Controller
{
    public function index(Product $request)
    {
        $user = Auth::user();
        $items = Product::simplePaginate(6);
        $items_rank = Product::orderBy('favorite', 'desc')->limit(5)->get();
        return view('product.index', ['items' => $items, 'user' => $user, 'items_rank' => $items_rank]);
    }

    public function showchangepassform()
    {
        return view('auth.changepassword');
    }

    public function changepass(Request $request)
    {
        if(!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            return redirect()->back()->with('change_password_error', '現在のパスワードが間違っています。');
        }

        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0) {
            return redirect()->back()->with('change_password_error', '新しいパスワードが現在のパスワードと同じです。');
        }

        $validated_data = $request->validate([
            'current-password' => 'required',
            'new-password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = bcrypt($request->get('new-password'));
        $user->save();

        return redirect()->back()->with('change_password_success', 'パスワードを変更しました。');
    }

    public function make_cart(Request $request)
    {
        $user = Auth::user();
        $cart = new Cart();
        $cart->user_id = $user->id;
        $cart->save();
        return redirect('/');
    }

    public function detail(int $id)
    {
        $user = Auth::user();
        $item = Product::find($id);
        return view('product.detail', ['item' => $item, 'user' => $user]);       
    }

    public function favorite_up(int $id)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $item = Product::find($id);
        $favorites = Favorite::where('user_id', $user_id)->where('product_id', $item->id)->get();

        if (count($favorites) == 0) {
            $favorite = new Favorite();
            $favorite->user_id = $user->id;
            $favorite->product_id = $item->id;
            $favorite->save();
            $item->favorite += 1;
            $item->save();    
        }
        return view('product.detail', ['item' => $item, 'user' => $user]);
    }

    public function mypageshow(Request $request)
    {
        $user = Auth::user();
        $favorites = $user->favorites;
        return view('product.mypage', ['favorites' => $favorites, 'user' => $user]);
    }

    public function add(int $id)
    {
        $user = Auth::user();
        $item = Product::find($id);
        $cart = Auth::user()->cart;
        $total = 0;
        foreach ($cart->cartitems as $cartitem) {
            $total += $cartitem->product->price;
        }
        $cartitem = new Cartitem();
        $cartitem->product_id = $item->id;
        $cartitem->cart_id = $cart->id;
        $cartitem->quantity = 1;
        $cartitem->size = $item->size;
        $cartitem->save();    
        return view('product.detail', ['item' => $item, 'user' => $user]);
    }

    public function cartshow(Request $request)
    {
        $user = Auth::user();
        $cart = Auth::user()->cart;
        $total = 0;
        foreach ($cart->cartitems as $cartitem) {
            $total += $cartitem->product->price;
        }
        return view('product.cartshow', ['cart' => $cart, 'user' => $user, 'total'=>$total]);
    }

    public function delete(int $cartitem_id)
    {
        $cartitem = Cartitem::find($cartitem_id);
        $cartitem->delete();
        return redirect('/cartshow/');
    }

    public function purchase(int $cart_id)
    {
        $user = Auth::user();
        $cartitems = Cartitem::where('cart_id', $cart_id)->get();
        foreach ($cartitems as $cartitem) {
            $purchase = new Purchase();
            $purchase->user_id = $user->id;
            $purchase->product_id = $cartitem->product->id;
            $purchase->save();
            $cartitem->delete();
        }
        return view('product.purchase', ['cartitems' => $cartitems, 'user' => $user]);
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $keyword = $request->input('keyword');

        if(!empty($keyword))
        {
            $products = Product::where('name', 'like', '%'.$keyword.'%')->get();

        }else{
            $products = Product::all();
        }
        return view('product.search', ['products' => $products, 'keyword' => $keyword, 'user' => $user]);
    }
}
