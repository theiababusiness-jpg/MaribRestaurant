@extends('layouts.app')

@php
    use App\Support\FrontLang;
@endphp

@section('content')
    {{-- عنوان التصنيف --}}
    <h2 class="page-title dark-text">
        {{ FrontLang::db($category->name, $category->name_en ?? null) }}
    </h2>

    {{-- وصف التصنيف (اختياري) --}}
    @if($category->description)
        <p class="page-subtitle dark-text">
            {{ FrontLang::db($category->description, $category->description_en ?? null) }}
        </p>
    @endif

    {{-- إذا لا توجد منتجات --}}
    @if($products->count() === 0)
        <p class="page-subtitle dark-text">
            {{ FrontLang::t('لا توجد منتجات في هذا التصنيف.', 'No products in this category.') }}
        </p>
    @else

        {{-- شبكة عرض المنتجات --}}
        <div class="grid">
            @foreach($products as $product)

                {{-- الكرت كامل قابل للنقر وينقلك لصفحة المنتج --}}
                <a class="card dark-card" href="{{ route('product.show', $product) }}">

                    {{-- صورة المنتج (إن وجدت) --}}
                    @if(!empty($product->image_path))
                        <img
                            src="{{ asset($product->image_path) }}"
                            alt="{{ $product->name }}"
                            style="width:100%; height:160px; object-fit:cover; border-radius:14px; margin-bottom:10px;"
                            loading="lazy"
                        >
                    @endif

                    {{-- اسم المنتج --}}
                    <h3 class="card__title dark-text">
                        {{ FrontLang::db($product->name, $product->name_en ?? null) }}
                    </h3>

                    {{-- وصف المنتج (اختياري) --}}
                    @if($product->description)
                        <p class="card__desc dark-text">
                            {{ FrontLang::db($product->description, $product->description_en ?? null) }}
                        </p>
                    @endif

                    {{-- سعر المنتج --}}
                    <div class="card__price dark-text">
                        {{ number_format((float)$product->price, 0) }}
                        {{ FrontLang::t('ريال', 'SER') }}
                    </div>

                    {{-- زر واضح داخل الكرت (اختياري) --}}
                    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                        <span class="btn btn--small dark-btn" style="min-width:auto;">
                            {{ FrontLang::t('عرض التفاصيل', 'View Details') }}
                        </span>
                    </div>
                </a>

            @endforeach
        </div>

    @endif
@endsection
