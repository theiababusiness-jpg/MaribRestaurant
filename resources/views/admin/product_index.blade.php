@extends('layouts.admin')

@section('content')
<h2>{{ __('messages.products') }}</h2>
<p>{{ __('messages.product_list') }}</p>

<!-- Placeholder table for listing products -->
<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('messages.name') }}</th>
            <th>{{ __('messages.price') }}</th>
            <th>{{ __('messages.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>مثال: رز بالدجاج</td>
            <td>64</td>
            <td>{{ __('messages.coming_soon') }}</td>
        </tr>
    </tbody>
</table>
@endsection