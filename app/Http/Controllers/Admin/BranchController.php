<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\Maps\GoogleMapsUrlService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request, GoogleMapsUrlService $googleMapsUrlService)
    {
        $data = $this->validateBranch($request, $googleMapsUrlService);
        Branch::create($data);

        return redirect()->route('admin.branches.index')->with('success', 'تمت إضافة الفرع بنجاح');
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch, GoogleMapsUrlService $googleMapsUrlService)
    {
        $branch->update($this->validateBranch($request, $googleMapsUrlService));

        return redirect()->route('admin.branches.index')->with('success', 'تم تحديث الفرع بنجاح');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return back()->with('success', 'تم حذف الفرع');
    }

    protected function validateBranch(Request $request, GoogleMapsUrlService $googleMapsUrlService): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_en' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'google_maps_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['pickup_enabled'] = $request->boolean('pickup_enabled', true);
        $data['delivery_enabled'] = $request->boolean('delivery_enabled');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['lat'] = null;
        $data['lng'] = null;

        if (filled($data['google_maps_url'] ?? null)) {
            try {
                $resolvedMap = $googleMapsUrlService->resolve((string) $data['google_maps_url']);
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    'google_maps_url' => 'رابط خرائط Google غير صالح أو لا يمكن استخراج موقع الفرع منه. / The Google Maps URL is invalid or the branch coordinates could not be extracted.',
                ]);
            }

            $data['google_maps_url'] = $resolvedMap['input_url'];
            $data['lat'] = $resolvedMap['lat'];
            $data['lng'] = $resolvedMap['lng'];
        }

        if ($data['delivery_enabled'] && (! filled($data['google_maps_url'] ?? null) || $data['lat'] === null || $data['lng'] === null)) {
            throw ValidationException::withMessages([
                'google_maps_url' => 'يجب إدخال رابط Google Maps صالح للفرع قبل تفعيل التوصيل. / A valid Google Maps URL is required before enabling delivery.',
            ]);
        }

        return $data;
    }
}
