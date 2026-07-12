<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        $products = Product::select('id', 'name')->get();
        return view('admin.banners.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'title_en'     => 'nullable|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'subtitle_en'  => 'nullable|string|max:255',
            'image'        => 'required|image',
            'link_type'    => 'required|in:none,menu,product',
            'link_text'    => 'nullable|string|max:255',
            'link_text_en' => 'nullable|string|max:255',
            'link_url'     => 'nullable|string|max:255',
            'product_id'   => 'nullable|exists:products,id',
            'sort_order'   => 'integer',
            'start_at'     => 'nullable|date',
            'end_at'       => 'nullable|date',
        ]);

        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/banners'), $filename);
            $data['image_path'] = 'images/banners/' . $filename;
        }

        Banner::create($data);

        return redirect()->route('admin.banners.index')
            ->with('success', 'تم إضافة العرض');
    }

    public function edit(Banner $banner)
    {
        $products = Product::select('id', 'name')->get();
        return view('admin.banners.edit', compact('banner', 'products'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'title_en'     => 'nullable|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'subtitle_en'  => 'nullable|string|max:255',
            'image'        => 'nullable|image',
            'link_type'    => 'required|in:none,menu,product',
            'link_text'    => 'nullable|string|max:255',
            'link_text_en' => 'nullable|string|max:255',
            'link_url'     => 'nullable|string|max:255',
            'product_id'   => 'nullable|exists:products,id',
            'sort_order'   => 'integer',
            'start_at'     => 'nullable|date',
            'end_at'       => 'nullable|date',
        ]);

        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($banner->image_path && file_exists(public_path($banner->image_path))) {
                unlink(public_path($banner->image_path));
            }

            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/banners'), $filename);
            $data['image_path'] = 'images/banners/' . $filename;
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')
            ->with('success', 'تم التحديث');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        $banner->delete();

        return back()->with('success', 'تم الحذف');
    }
}
