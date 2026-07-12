<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OptionGroup;
use Illuminate\Http\Request;

/**
 * OptionGroupController (Admin)
 *
 * وظيفته:
 * - إدارة مجموعات التخصيص (عرض/إضافة/تعديل/حذف)
 * - مثال: "نوع الأرز" ، "إضافات" ، "مستوى الحدة"
 */
class OptionGroupController extends Controller
{
    /**
     * index:
     * عرض قائمة مجموعات التخصيص
     */
    public function index()
    {
        // groups: جلب كل المجموعات مرتبة
        $groups = OptionGroup::orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.option_groups.index', compact('groups'));
    }

    /**
     * create:
     * صفحة إضافة مجموعة تخصيص
     */
    public function create()
    {
        return view('admin.option_groups.create');
    }

    /**
     * store:
     * حفظ مجموعة تخصيص جديدة
     */
    public function store(Request $request)
    {
        // validate: التحقق من المدخلات
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'name_en'    => ['required', 'string', 'max:255'],
            'admin_note' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // sort_order: لو فاضي نخليه 0
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // is_required: checkbox (لو غير محدد => 0)
        $data['is_required'] = $request->has('is_required') ? 1 : 0;

        // is_multiple: checkbox (لو غير محدد => 0)
        $data['is_multiple'] = $request->has('is_multiple') ? 1 : 0;

        OptionGroup::create($data);

        return redirect()->route('admin.option_groups.index')
            ->with('success', 'تم إضافة مجموعة التخصيص بنجاح');
    }

    /**
     * edit:
     * صفحة تعديل مجموعة تخصيص
     */
    public function edit(OptionGroup $optionGroup)
    {
        return view('admin.option_groups.edit', compact('optionGroup'));
    }

    /**
     * update:
     * تحديث مجموعة تخصيص
     */
    public function update(Request $request, OptionGroup $optionGroup)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'name_en'    => ['required', 'string', 'max:255'],
             'admin_note' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_required'] = $request->has('is_required') ? 1 : 0;
        $data['is_multiple'] = $request->has('is_multiple') ? 1 : 0;

        $optionGroup->update($data);

        return redirect()->route('admin.option_groups.index')
            ->with('success', 'تم تعديل مجموعة التخصيص بنجاح');
    }

    /**
     * destroy:
     * حذف مجموعة تخصيص
     */
    public function destroy(OptionGroup $optionGroup)
    {
        // لاحقا: ممكن نمنع حذفها إذا فيها Options أو مرتبطة بمنتجات
        $optionGroup->delete();

        return redirect()->route('admin.option_groups.index')
            ->with('success', 'تم حذف مجموعة التخصيص');
    }
}
