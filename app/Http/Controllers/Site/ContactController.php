<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Rules\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('contact', [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:20', new PhoneNumber],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'website' => ['prohibited'], // honeypot
        ], [
            'phone.required_without' => 'Please give a phone number or an email so we can reply.',
            'email.required_without' => 'Please give a phone number or an email so we can reply.',
        ]);

        ContactMessage::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ? PhoneNumber::normalize($data['phone']) : null,
            'email' => $data['email'] ?? null,
            'message' => $data['message'],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->to(url()->previous().'#contactForm')
            ->with('contact_status', 'Thank you — we have received your message and will get back to you soon.');
    }
}
