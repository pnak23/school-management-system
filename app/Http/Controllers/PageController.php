<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    /**
     * Display the about page.
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Display the contact page.
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission.
     */
    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);
        
        // In a real application, you would send an email here or store in database
        // Example:
        // Mail::to('admin@schoolmanagement.com')->send(new ContactFormMail($request->all()));
        
        // For now, just redirect back with success message
        return redirect()->route('contact')->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}












