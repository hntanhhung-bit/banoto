<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    // Hiển thị giỏ hàng
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    // Thêm sản phẩm vào giỏ hàng
    public function add(Request $request, Product $product)
    {
        // 1. Kiểm tra tồn kho sản phẩm
        if ($product->quantity <= 0) {
            return redirect()->back()->with('error', "Rất tiếc! Mẫu xe '{$product->name}' hiện tại đã hết hàng trong kho.");
        }

        $cart = session()->get('cart', []);
        $selectedColor = $request->filled('color') ? trim($request->input('color')) : ($product->color ?: 'Trắng');
        
        // Tạo cart key hoặc lưu theo product_id + color
        $cartKey = $product->id;
        $currentQuantityInCart = isset($cart[$cartKey]) ? (int) $cart[$cartKey]['quantity'] : 0;
        $newQuantity = $currentQuantityInCart + (int) $request->input('quantity', 1);

        // 2. Kiểm tra nếu số lượng thêm vượt quá tồn kho
        if ($newQuantity > $product->quantity) {
            return redirect()->back()->with('error', "Không thể thêm! Số lượng bạn yêu cầu ({$newQuantity} xe) vượt quá số lượng còn lại trong kho (Chỉ còn: {$product->quantity} xe).");
        }

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $newQuantity;
            if ($request->filled('color')) {
                $cart[$cartKey]['color'] = $selectedColor;
            }
        } else {
            $cart[$cartKey] = [
                "name" => $product->name,
                "quantity" => (int) $request->input('quantity', 1),
                "price" => $product->price,
                "category" => $product->category ? $product->category->name : 'N/A',
                "image" => $product->image,
                "color" => $selectedColor,
                "stock" => $product->quantity,
            ];
        }

        session()->put('cart', $cart);
        return redirect()->route('cart.index')->with('success', "Đã thêm xe '{$product->name}' (Màu: {$selectedColor}) vào giỏ hàng thành công!");
    }

    // Xoá sản phẩm khỏi giỏ hàng
    public function remove(Product $product)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được xoá khỏi giỏ hàng.');
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function update(Request $request, $id)
    {
        $requestedQuantity = (int) $request->input('quantity', 1);

        if ($requestedQuantity <= 0) {
            return redirect()->route('cart.index')->with('error', 'Số lượng xe phải lớn hơn hoặc bằng 1!');
        }

        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('cart.index')->with('error', 'Sản phẩm không tồn tại trong hệ thống!');
        }

        // Kiểm tra tồn kho
        if ($requestedQuantity > $product->quantity) {
            return redirect()->route('cart.index')->with('error', "Không thể cập nhật! Số lượng ({$requestedQuantity} xe) vượt quá số lượng trong kho (Kho chỉ còn: {$product->quantity} xe).");
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $requestedQuantity;
            $cart[$id]['stock'] = $product->quantity;
            session()->put('cart', $cart);
            return redirect()->route('cart.index')->with('success', "Đã cập nhật số lượng xe '{$product->name}' thành công ({$requestedQuantity} xe)!");
        }

        return redirect()->route('cart.index')->with('error', 'Cập nhật giỏ hàng thất bại!');
    }
}
