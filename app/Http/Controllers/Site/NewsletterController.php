<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email:rfc', 'max:190'],
            'website' => ['prohibited'],
        ], ['email.*' => 'Please enter a valid email address.']);

        // Same response whether or not the address was already subscribed (no address enumeration)
        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => mb_strtolower($data['email'])]);
        $subscriber->fill(['status' => 'subscribed', 'unsubscribed_at' => null, 'ip_address' => $subscriber->ip_address ?? $request->ip()])->save();

        $message = "Thanks! You're subscribed to our oral-health tips.";

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : back()->with('newsletter_status', $message);
    }
}
