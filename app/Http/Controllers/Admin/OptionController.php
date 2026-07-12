<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OptionGroup;
use App\Models\Option;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    // عرض الخيارات داخل مجموعة
    public function index(OptionGroup $optionGroup)
    {
        $options = $optionGroup->options()->orderBy('sort_order')->get();
        return view('admin.options.index', compact('optionGroup', 'options'));
    }

    // صفحة إضافة خيار
    public function create(OptionGroup $optionGroup)
    {
        return view('admin.options.create', compact('optionGroup'));
    }

    // حفظ خيار
    public function store(Request $request, OptionGroup $optionGroup)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'name_en'     => ['nullable','string','max:255'],
            'price_delta' => ['required','numeric'],
            'sort_order'  => ['nullable','integer','min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['option_group_id'] = $optionGroup->id;

        Option::create($data);

        return redirect()->route('admin.options.index', $optionGroup)
            ->with('success', 'تم إضافة الخيار');
    }

    // صفحة تعديل خيار
    public function edit(Option $option)
    {
        return view('admin.options.edit', compact('option'));
    }

    // تحديث خيار
    public function update(Request $request, Option $option)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'name_en'     => ['nullable','string','max:255'],
            'price_delta' => ['required','numeric'],
            'sort_order'  => ['nullable','integer','min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;

        $option->update($data);

        return redirect()->route('admin.options.index', $option->optionGroup)
            ->with('success', 'تم تعديل الخيار');
    }

    // حذف خيار
    public function destroy(Option $option)
    {
        $group = $option->optionGroup;
        $option->delete();

        return redirect()->route('admin.options.index', $group)
            ->with('success', 'تم حذف الخيار');
    }
}
