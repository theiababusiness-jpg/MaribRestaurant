<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\OptionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
{
    $query = Product::with('category')
        ->orderBy('sort_order')
        ->orderBy('id', 'desc');

    // البحث
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('name_en', 'like', "%{$search}%");
        });
    }

    // الفلترة
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    $products = $query->paginate(20);

    $categories = Category::orderBy('sort_order')
        ->orderBy('id', 'desc')
        ->get();

    // استجابة AJAX
    if ($request->ajax()) {
        return response()->json([
            'html' => view('admin.products.partials.rows', compact('products'))->render(),
            'hasMore' => $products->hasMorePages()
        ]);
    }

    return view('admin.products.index', compact('products', 'categories'));
}


    // باقي الدوال كما هي بدون أي تغيير 👇

    public function create()
    {
        $categories = Category::orderBy('sort_order')->orderBy('id', 'desc')->get();
        $allGroups = OptionGroup::orderBy('sort_order')->orderBy('id', 'desc')->get();

        return view('admin.products.create', compact('categories', 'allGroups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'has_special_message' => ['nullable', 'boolean'],
			'special_message' => ['nullable', 'string', 'max:1000'],
            'special_message_en' => ['nullable', 'string', 'max:1000'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $baseSlug = $data['slug'] ?? Str::slug($data['name']);
        if (!$baseSlug) {
            $baseSlug = 'p';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $data['slug'] = $slug;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $filename);
            $data['image_path'] = 'images/products/' . $filename;
        }

        $product = Product::create($data);

        $selectedGroups = $request->input('option_groups', []);
        $groupSorts = $request->input('group_sorts', []);

        $syncData = [];

        foreach ($selectedGroups as $groupId) {
            $pivotSort = isset($groupSorts[$groupId]) ? (int) $groupSorts[$groupId] : 0;
            $syncData[$groupId] = ['sort_order' => $pivotSort];
        }

        if (!empty($syncData)) {
            $product->optionGroups()->sync($syncData);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('sort_order')->orderBy('id', 'desc')->get();
        $allGroups = OptionGroup::orderBy('sort_order')->orderBy('id', 'desc')->get();

        $attachedGroups = $product->optionGroups()
            ->withPivot('sort_order')
            ->orderBy('pivot_sort_order')
            ->get();

        $attachedIds = $attachedGroups->pluck('id')->toArray();
        $attachedSortMap = $attachedGroups->pluck('pivot.sort_order', 'id')->toArray();

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'allGroups',
            'attachedIds',
            'attachedSortMap'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'has_special_message' => ['nullable', 'boolean'],
            'special_message_en' => ['nullable', 'string', 'max:1000'],
        	'special_message' => ['nullable', 'string', 'max:1000'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $product->id],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            if (!$data['slug']) {
                $data['slug'] = 'p-' . time();
            }
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $filename);
            $data['image_path'] = 'images/products/' . $filename;
        }

        $product->update($data);

        $selectedGroups = $request->input('option_groups', []);
        $groupSorts = $request->input('group_sorts', []);

        $syncData = [];

        foreach ($selectedGroups as $groupId) {
            $pivotSort = isset($groupSorts[$groupId]) ? (int) $groupSorts[$groupId] : 0;
            $syncData[$groupId] = ['sort_order' => $pivotSort];
        }

        $product->optionGroups()->sync($syncData);

        return redirect()->route('admin.products.index')
            ->with('success', 'تم تعديل المنتج بنجاح');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'تم حذف المنتج');
    }
}
