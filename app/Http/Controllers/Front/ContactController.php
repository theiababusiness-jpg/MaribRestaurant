<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\FrontLang;
use App\Support\SeoData;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $seo = SeoData::make([
            'title' => FrontLang::t('تواصل معنا | مطاعم مأرب', 'Contact Us | Marib Restaurant'),
            'description' => FrontLang::t(
                'تواصل مع مطاعم مأرب للاستفسارات والطلبات الخاصة والملاحظات العامة.',
                'Contact Marib Restaurant for inquiries, special orders, and general feedback.'
            ),
        ]);

        return view('front.contact', compact('seo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'message' => 'required|string|max:1000',
        ]);

        ContactMessage::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'message' => $data['message'],
            'is_read' => false,
        ]);

        return back()->with('success', FrontLang::t('تم إرسال رسالتك بنجاح', 'Your message has been sent successfully.'));
    }
}
