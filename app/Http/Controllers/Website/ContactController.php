<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\WebsiteContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('success', 'Thank you. Your message has been received.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:25'],
            'subject' => ['nullable', 'string', 'max:180'],
            'service' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $contact = WebsiteContact::create([
            ...$validated,
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        if ($email = config('website.contact_notification_email')) {
            try {
                Mail::raw($this->mailBody($contact), function ($mail) use ($email, $contact): void {
                    $mail->to($email)->subject('New Fulawala website enquiry');
                    if ($contact->email) $mail->replyTo($contact->email, $contact->name);
                });
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('success', 'Thank you! Our team will contact you shortly.');
    }

    private function mailBody(WebsiteContact $contact): string
    {
        return implode(PHP_EOL, [
            'New Fulawala website enquiry',
            'Name: ' . $contact->name,
            'Phone: ' . $contact->phone,
            'Email: ' . ($contact->email ?: 'Not provided'),
            'Service: ' . ($contact->service ?: 'General'),
            'Subject: ' . ($contact->subject ?: 'Not provided'),
            '',
            $contact->message,
        ]);
    }
}
