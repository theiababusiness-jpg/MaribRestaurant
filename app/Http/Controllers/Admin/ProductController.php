<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Services\Images\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->paginate(20);

        $categories = Category::orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.products.partials.rows', compact('products'))->render(),
                'hasMore' => $products->hasMorePages(),
            ]);
        }

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $allGroups = OptionGroup::orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('admin.products.create', compact('categories', 'allGroups'));
    }

    public function store(Request $request, ImageProcessingService $images)
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
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $data['has_special_message'] = $request->boolean('has_special_message');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? null, $data['name']);

        $selectedGroups = (array) $request->input('option_groups', []);
        $groupSorts = (array) $request->input('group_sorts', []);
        $syncData = $this->buildGroupSyncData($selectedGroups, $groupSorts);

        $images->persist(
            $request->file('image'),
            null,
            'images/products',
            function (?string $imagePath) use ($data, $syncData) {
                $payload = $data;

                if ($imagePath !== null) {
                    $payload['image_path'] = $imagePath;
                }

                $product = Product::create($payload);

                if ($syncData !== []) {
                    $product->optionGroups()->sync($syncData);
                }

                return $product;
            }
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'تمت إضافة المنتج بنجاح');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $allGroups = OptionGroup::orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

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

    public function update(Request $request, Product $product, ImageProcessingService $images)
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
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $product->id],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $data['has_special_message'] = $request->boolean('has_special_message');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? null, $data['name'], $product->id);

        $selectedGroups = (array) $request->input('option_groups', []);
        $groupSorts = (array) $request->input('group_sorts', []);
        $syncData = $this->buildGroupSyncData($selectedGroups, $groupSorts);

        $images->persist(
            $request->file('image'),
            $product->image_path,
            'images/products',
            function (?string $imagePath) use ($product, $data, $syncData) {
                $payload = $data;

                if ($imagePath !== null) {
                    $payload['image_path'] = $imagePath;
                }

                $product->update($payload);
                $product->optionGroups()->sync($syncData);

                return $product;
            }
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'تم تعديل المنتج بنجاح');
    }

    public function destroy(Product $product, ImageProcessingService $images)
    {
        $imagePath = $product->image_path;

        $product->delete();
        $images->delete($imagePath);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'تم حذف المنتج');
    }

    private function buildGroupSyncData(array $selectedGroups, array $groupSorts): array
    {
        $syncData = [];

        foreach ($selectedGroups as $groupId) {
            $groupId = (int) $groupId;

            $syncData[$groupId] = [
                'sort_order' => (int) ($groupSorts[$groupId] ?? 0),
            ];
        }

        return $syncData;
    }

    private function makeUniqueSlug(?string $slug, string $name, ?int $ignoreProductId = null): string
    {
        $baseSlug = trim((string) $slug);

        if ($baseSlug === '') {
            $baseSlug = Str::slug($name);
        }

        if ($baseSlug === '') {
            $baseSlug = 'p';
        }

        $candidate = $baseSlug;
        $counter = 1;

        while (
            Product::query()
                ->when($ignoreProductId !== null, fn ($query) => $query->where('id', '!=', $ignoreProductId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
