<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    // جميع الرسائل
    public function index()
    {
        $messages = ContactMessage::orderBy('id', 'desc')->get();

        return view('admin.contact.index', compact('messages'));
    }

    // تحديد رسالة كمقروءة
    public function markRead(ContactMessage $contact)
    {
        $contact->update(['is_read' => true]);

        return back();
    }
}
