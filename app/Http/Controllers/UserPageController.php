<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPageController extends Controller
{
    /**
     * Display the user page (for students, teachers, staff, guests)
     */
    public function index()
    {
        return view('user_page.index');
    }

    /**
     * Show QR Code Generator for User Page
     * 
     * This page allows admin/staff to generate a QR code that links to the user page.
     * Users can scan the QR code to quickly access all library services.
     */
    public function showQRGenerator()
    {
        // Check permissions (admin, manager, staff only)
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'manager', 'staff'])) {
            abort(403, 'You do not have permission to access QR Code Generator.');
        }

        // Generate the URL that the QR code will point to
        $qrUrl = route('user_page.index');

        return view('user_page.qr_generator', [
            'qrUrl' => $qrUrl,
            'title' => 'User Page QR Generator',
            'description' => 'Scan this QR code to quickly access all library services: check-in, start reading, browse books, view reports, and more.',
        ]);
    }
}

