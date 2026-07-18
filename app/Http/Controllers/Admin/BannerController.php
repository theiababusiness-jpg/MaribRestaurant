<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Services\Images\ImageProcessingService;
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

    public function store(Request $request, ImageProcessingService $images)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'subtitle_en' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'link_type' => ['required', 'in:none,menu,product'],
            'link_text' => ['nullable', 'string', 'max:255'],
            'link_text_en' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $images->persist(
            $request->file('image'),
            null,
            'images/banners',
            function (?string $imagePath) use ($data) {
                $payload = $data;
                $payload['image_path'] = $imagePath;

                return Banner::create($payload);
            }
        );

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'تمت إضافة العرض');
    }

    public function edit(Banner $banner)
    {
        $products = Product::select('id', 'name')->get();

        return view('admin.banners.edit', compact('banner', 'products'));
    }

    public function update(Request $request, Banner $banner, ImageProcessingService $images)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'subtitle_en' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'link_type' => ['required', 'in:none,menu,product'],
            'link_text' => ['nullable', 'string', 'max:255'],
            'link_text_en' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $images->persist(
            $request->file('image'),
            $banner->image_path,
            'images/banners',
            function (?string $imagePath) use ($banner, $data) {
                $payload = $data;

                if ($imagePath !== null) {
                    $payload['image_path'] = $imagePath;
                }

                $banner->update($payload);

                return $banner;
            }
        );

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'تم التحديث');
    }

    public function destroy(Banner $banner, ImageProcessingService $images)
    {
        $imagePath = $banner->image_path;

        $banner->delete();
        $images->delete($imagePath);

        return back()->with('success', 'تم الحذف');
    }
}
