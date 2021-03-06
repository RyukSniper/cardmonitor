<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required|string',
            'mail' => 'required|email',
            'message' => 'required|string',
        ]);

        Mail::to(config('app.mail'))
            ->queue(new \App\Mail\Contact($attributes));

        return back()->with('status', [
            'type' => 'success',
            'text' => 'Nachricht verschickt.',
        ]);
    }
}
