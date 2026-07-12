<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * CategoryController (Admin)
 *
 * وظيفته:
 * - إدارة التصنيفات (عرض/إضافة/تعديل/حذف)
 * - توليد slug تلقائيا إذا لم يكتب المستخدم
 *
 * ملاحظة مهمة:
 * - نحن نعتمد فقط على: name + description
 * - لا يوجد name_ar أو name_en
 */
class CategoryController extends Controller
{
    /**
     * index:
     * عرض قائمة التصنيفات
     */
    public function index()
    {
        // categories: جلب كل التصنيفات مرتبة
        $categories = Category::orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * create:
     * عرض فورم إضافة تصنيف
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * store:
     * حفظ تصنيف جديد
     */
    public function store(Request $request)
    {
        // validate: التحقق من المدخلات
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'name_en'         => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'description_en'  => ['nullable', 'string'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        // is_active: لو checkbox غير محدد لن يرسل، فنخليه 0
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        // sort_order: لو فاضي نخليه 0
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // slug: لو المستخدم ما كتب slug نولده من الاسم
        if (empty($data['slug'])) {
            $data['slug'] = $this->makeArabicSlug($data['name']);
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم إضافة التصنيف بنجاح');
    }

    /**
     * edit:
     * عرض فورم تعديل تصنيف
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * update:
     * تحديث تصنيف
     */
    public function update(Request $request, Category $category)
    {
        // validate: التحقق من المدخلات
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'name_en'         => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'description_en'  => ['nullable', 'string'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:categories,slug,' . $category->id],
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        // is_active: checkbox
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        // sort_order: لو فاضي نخليه 0
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // slug: لو فاضي نولده
        if (empty($data['slug'])) {
            $data['slug'] = $this->makeArabicSlug($data['name']);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم تعديل التصنيف بنجاح');
    }

    /**
     * destroy:
     * حذف تصنيف
     */
    public function destroy(Category $category)
    {
        // لاحقا: نمنع الحذف إذا يوجد منتجات داخل التصنيف
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم حذف التصنيف');
    }

    /**
     * makeArabicSlug:
     * يولد slug بسيط من نص عربي (بدون تعقيد)
     */
    private function makeArabicSlug(string $text): string
    {
        // تحويل النص إلى slug بسيط:
        // - إزالة الرموز
        // - استبدال المسافات بشرطة
        $slug = trim($text);
        $slug = preg_replace('/[^\p{Arabic}0-9a-zA-Z\s\-]/u', '', $slug);
        $slug = preg_replace('/\s+/u', '-', $slug);
        $slug = strtolower($slug);

        // لو طلع فاضي لأي سبب: نولد slug مؤقت
        if (!$slug) {
            $slug = 'cat-' . time();
        }

        return $slug;
    }
}
