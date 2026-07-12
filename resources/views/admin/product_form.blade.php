@extends('layouts.admin')

@section('content')
<h2>{{ __('messages.product_form') }}</h2>
<p>{{ __('messages.coming_soon') }}</p>

<!-- Placeholder for create/edit product form -->
<form method="post" action="#">
    <!-- CSRF token would be included automatically in a real Laravel app -->

    <label for="name_ar">{{ __('messages.name_ar') }}</label><br>
    <input type="text" id="name_ar" name="name_ar" value="" /><br><br>

    <label for="name_en">{{ __('messages.name_en') }}</label><br>
    <input type="text" id="name_en" name="name_en" value="" /><br><br>

    <label for="price">{{ __('messages.price') }}</label><br>
    <input type="number" id="price" name="price" value="" step="0.01" /><br><br>

    <!-- Option groups and options would be added here -->

    <button type="submit">{{ __('messages.save') }}</button>
</form>
@endsection