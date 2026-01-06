# 📦 Complete Package Guide: Modern CRUD Stack with DataTables

## 🎯 Stack Overview

This project uses a modern, reusable stack for building CRUD pages:

- **Backend:** Laravel 11 + Yajra Laravel DataTables (server-side processing)
- **Frontend:** jQuery + DataTables.js + SweetAlert2
- **Build Tool:** Vite (Laravel 11 default)
- **Styling:** Tailwind CSS (Laravel 11 default)

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Installation Steps](#installation-steps)
3. [Configuration](#configuration)
4. [File Structure](#file-structure)
5. [How to Use (Create New CRUD Page)](#how-to-use-create-new-crud-page)
6. [Troubleshooting](#troubleshooting)
7. [Quick Reference Commands](#quick-reference-commands)

---

## 🔧 Prerequisites

Before starting, ensure you have:

### 1. Node.js (v18.0.0 or higher)
```powershell
# Check Node.js version
node --version
# Should show: v18.17.1 or v20.19.0 or higher
```

**If Node.js version is too old:**
- Download from: https://nodejs.org/
- Install the LTS version (v20.x recommended)
- Restart your terminal after installation

### 2. PHP (v8.2 or higher)
```powershell
# Check PHP version
php --version
```

### 3. Composer
```powershell
# Check Composer version
composer --version
```

### 4. Laravel Project Already Set Up
```powershell
# Check Laravel version
php artisan --version
```

---

## 📥 Installation Steps

### Step 1: Install Backend Dependencies (Composer)

#### 1.1 Install Yajra Laravel DataTables
```powershell
composer require yajra/laravel-datatables-oracle:"^11.0"
```

**Expected output:**
```
Using version ^11.0 for yajra/laravel-datatables-oracle
./composer.json has been updated
...
Package manifest generated successfully.
```

#### 1.2 Publish Configuration (Optional)
```powershell
php artisan vendor:publish --provider="Yajra\DataTables\DataTablesServiceProvider"
```

This creates: `config/datatables.php`

---

### Step 2: Install Frontend Dependencies (npm)

#### 2.1 Install jQuery
```powershell
npm install jquery --save
```

#### 2.2 Install DataTables.js
```powershell
npm install datatables.net datatables.net-dt datatables.net-responsive datatables.net-responsive-dt --save
```

#### 2.3 Install SweetAlert2
```powershell
npm install sweetalert2 --save
```

#### 2.4 Verify Installation
Check `package.json`:
```json
{
  "dependencies": {
    "datatables.net": "^2.2.3",
    "datatables.net-dt": "^2.2.3",
    "datatables.net-responsive": "^3.1.0",
    "datatables.net-responsive-dt": "^3.1.0",
    "jquery": "^3.7.1",
    "sweetalert2": "^11.15.10"
  }
}
```

---

## ⚙️ Configuration

### Step 3: Configure Vite

#### 3.1 Update `vite.config.js`
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Listen on all network interfaces
        hmr: {
            host: '127.0.0.1', // Use IPv4 localhost for HMR
        },
    },
});
```

---

### Step 4: Configure JavaScript Files

#### 4.1 Create Helper Module: `resources/js/modules/datatable-helpers.js`
```javascript
/**
 * DataTable Helpers
 * Reusable functions for initializing server-side DataTables and SweetAlert2 confirm dialogs
 */

import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';
import 'datatables.net-responsive';
import 'datatables.net-responsive-dt';
import Swal from 'sweetalert2';

/**
 * Initialize a server-side DataTable
 * @param {string} selector - jQuery selector for the table (e.g., '#users-table')
 * @param {string} ajaxUrl - URL to fetch data from
 * @param {Array} columns - Array of column definitions
 * @param {Object} options - Additional DataTable options (optional)
 * @returns {Object} DataTable instance
 */
export function initServerSideDataTable(selector, ajaxUrl, columns, options = {}) {
    const defaultOptions = {
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        columns: columns,
        responsive: true,
        order: [[0, 'desc']], // Default: sort by first column descending
        pageLength: 10,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
            emptyTable: 'No data available',
            zeroRecords: 'No matching records found',
        },
    };

    // Merge user options with defaults
    const finalOptions = { ...defaultOptions, ...options };

    return $(selector).DataTable(finalOptions);
}

/**
 * Show SweetAlert2 confirmation dialog before delete
 * @param {string} title - Dialog title (e.g., 'Delete User?')
 * @param {string} text - Dialog text (e.g., 'This action cannot be undone.')
 * @param {Function} onConfirm - Callback function to run if user confirms
 * @param {string} confirmButtonText - Text for confirm button (default: 'Yes, delete it!')
 */
export function confirmDelete(title, text, onConfirm, confirmButtonText = 'Yes, delete it!') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            onConfirm();
        }
    });
}

/**
 * Show success toast notification
 * @param {string} message - Success message
 */
export function showSuccessToast(message) {
    Swal.fire({
        icon: 'success',
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

/**
 * Show error toast notification
 * @param {string} message - Error message
 */
export function showErrorToast(message) {
    Swal.fire({
        icon: 'error',
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}
```

#### 4.2 Update `resources/js/bootstrap.js`
```javascript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Make jQuery available globally (required by DataTables)
import $ from 'jquery';
window.$ = window.jQuery = $;

console.log('✅ Bootstrap.js loaded: jQuery, Axios configured');
```

#### 4.3 Update `resources/js/app.js`
```javascript
import './bootstrap';

// Import DataTables
import 'datatables.net';
import 'datatables.net-dt';
import 'datatables.net-responsive';
import 'datatables.net-responsive-dt';

// Import SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

// Import custom DataTable helpers
import { 
    initServerSideDataTable, 
    confirmDelete, 
    showSuccessToast, 
    showErrorToast 
} from './modules/datatable-helpers';

// Expose helpers globally
window.initServerSideDataTable = initServerSideDataTable;
window.confirmDelete = confirmDelete;
window.showSuccessToast = showSuccessToast;
window.showErrorToast = showErrorToast;

console.log('✅ App.js loaded: DataTables, SweetAlert2, Helpers available');
```

---

### Step 5: Configure Blade Layout

#### 5.1 Update `resources/views/layouts/app.blade.php`

Ensure your main layout includes Vite and script/style stacks:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Page-specific styles -->
    @stack('styles')
</head>
<body>
    <!-- Your layout content here -->
    @yield('content')

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
```

---

### Step 6: Configure Environment

#### 6.1 Update `.env` File

**Option 1: Use IPv4 Localhost (Recommended)**
```env
APP_URL=http://127.0.0.1:8000
```

**Option 2: Use Network IP (if accessing from other devices)**
```env
APP_URL=http://192.168.1.19:8000
```

**⚠️ Important:** Do NOT use `http://localhost` as it can cause IPv6 issues.

#### 6.2 Clear Laravel Config Cache
```powershell
php artisan config:clear
```

---

### Step 7: Build Assets

#### 7.1 Start Vite Development Server
```powershell
npm run dev
```

**Expected output:**
```
  VITE v7.2.7  ready in 441 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: http://192.168.1.19:5173/

  LARAVEL v12.41.1  plugin v2.0.1

  ➜  APP_URL: http://127.0.0.1:8000
```

**⚠️ IMPORTANT:** Keep this terminal running while developing!

#### 7.2 For Production (Build Once)
```powershell
npm run build
```

---

## 📁 File Structure

After setup, your project should have:

```
project-root/
│
├── app/
│   └── Http/
│       └── Controllers/
│           └── Admin/
│               ├── SampleUserTableController.php  ← Example controller
│               └── StudentController.php          ← Your CRUD controller
│
├── resources/
│   ├── js/
│   │   ├── app.js                 ← Main JS entry point (imports helpers)
│   │   ├── bootstrap.js           ← jQuery setup
│   │   └── modules/
│   │       └── datatable-helpers.js  ← Reusable DataTable functions
│   │
│   ├── css/
│   │   └── app.css                ← Tailwind CSS
│   │
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php      ← Main layout (with @vite, @stack)
│       │   └── sidebar.blade.php  ← Sidebar menu
│       │
│       └── admin/
│           ├── sample_users/
│           │   └── index.blade.php       ← Demo page
│           │
│           └── students/
│               └── datatable_index.blade.php  ← Students with DataTables
│
├── routes/
│   └── web.php                    ← Define routes here
│
├── vite.config.js                 ← Vite configuration
├── package.json                   ← npm dependencies
├── composer.json                  ← Composer dependencies
└── .env                           ← Environment variables (APP_URL)
```

---

## 🚀 How to Use (Create New CRUD Page)

### Example: Create a "Teachers" DataTable Page

#### Step 1: Create Controller

**File:** `app/Http/Controllers/Admin/TeacherController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    /**
     * Display the DataTable view
     */
    public function index()
    {
        return view('admin.teachers.index');
    }

    /**
     * Return DataTables JSON (Ajax endpoint)
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Teacher::with(['department', 'position'])
                ->select('teachers.*');

            return DataTables::of($query)
                ->addColumn('action', function ($teacher) {
                    return '
                        <button onclick="editTeacher(' . $teacher->id . ')" class="btn btn-sm btn-primary">Edit</button>
                        <button onclick="deleteTeacher(' . $teacher->id . ')" class="btn btn-sm btn-danger">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    // Add store, update, destroy methods here...
}
```

---

#### Step 2: Define Routes

**File:** `routes/web.php`

```php
use App\Http\Controllers\Admin\TeacherController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Teachers routes
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/data', [TeacherController::class, 'getData'])->name('teachers.data'); // Ajax endpoint
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    
});
```

---

#### Step 3: Create Blade View

**File:** `resources/views/admin/teachers/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Teachers</h2>
            <button onclick="openCreateModal()" class="btn btn-primary">+ Add Teacher</button>
        </div>

        <!-- DataTable -->
        <table id="teachers-table" class="display responsive nowrap w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Create/Edit Modal (add your modal HTML here) -->

@endsection

@push('scripts')
<script>
// ========================================
// 1. Initialize DataTable
// ========================================
let teachersTable;

document.addEventListener('DOMContentLoaded', function() {
    // Check if helper functions are loaded
    if (typeof initServerSideDataTable === 'undefined') {
        console.error('❌ initServerSideDataTable is not loaded!');
        return;
    }

    // Initialize DataTable
    teachersTable = initServerSideDataTable(
        '#teachers-table',
        '{{ route("admin.teachers.data") }}',
        [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'department.name', name: 'department.name' },
            { data: 'position.name', name: 'position.name' },
            { 
                data: 'is_active', 
                name: 'is_active',
                render: function(data) {
                    return data ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                }
            },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    );

    console.log('✅ Teachers DataTable initialized');
});

// ========================================
// 2. Create Teacher
// ========================================
function openCreateModal() {
    // Your modal logic here
    console.log('Open create modal');
}

// ========================================
// 3. Edit Teacher
// ========================================
function editTeacher(id) {
    // Your edit logic here
    console.log('Edit teacher:', id);
}

// ========================================
// 4. Delete Teacher (with SweetAlert2)
// ========================================
function deleteTeacher(id) {
    confirmDelete(
        'Delete Teacher?',
        'This action cannot be undone.',
        function() {
            // Perform delete
            fetch(`/admin/teachers/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessToast('Teacher deleted successfully');
                    teachersTable.ajax.reload();
                } else {
                    showErrorToast(data.message || 'Failed to delete teacher');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast('An error occurred');
            });
        }
    );
}
</script>
@endpush
```

---

#### Step 4: Test Your Page

1. **Start Vite (if not running):**
   ```powershell
   npm run dev
   ```

2. **Start Laravel server (if not running):**
   ```powershell
   php artisan serve
   ```

3. **Open browser:**
   ```
   http://127.0.0.1:8000/admin/teachers
   ```

4. **Check browser console (F12):**
   - Should see: `✅ Bootstrap.js loaded`, `✅ App.js loaded`, `✅ Teachers DataTable initialized`
   - Should NOT see any red errors

---

## 🐛 Troubleshooting

### Problem 1: "jQuery is not loaded"

**Symptoms:**
```
❌ jQuery is not loaded. Run: npm install jquery --save && npm run dev
```

**Solutions:**

1. **Check if npm packages are installed:**
   ```powershell
   npm list jquery
   npm list datatables.net
   npm list sweetalert2
   ```

2. **If missing, install them:**
   ```powershell
   npm install jquery datatables.net datatables.net-dt datatables.net-responsive datatables.net-responsive-dt sweetalert2 --save
   ```

3. **Check if Vite is running:**
   ```powershell
   npm run dev
   ```
   Keep this terminal open!

4. **Clear browser cache:**
   - Press `Ctrl + Shift + R` (hard refresh)

5. **Check `resources/js/bootstrap.js`:**
   - Ensure it has:
     ```javascript
     import $ from 'jquery';
     window.$ = window.jQuery = $;
     ```

---

### Problem 2: CORS Error (IPv6 Issue)

**Symptoms:**
```
Access to script at 'http://[::1]:5173/@vite/client' from origin 'http://192.168.1.19:8000' 
has been blocked by CORS policy
```

**Cause:** You're accessing via `http://192.168.1.19:8000` but `.env` has `APP_URL=http://localhost`

**Solution:**

1. **Update `.env`:**
   ```env
   APP_URL=http://127.0.0.1:8000
   ```

2. **Clear config cache:**
   ```powershell
   php artisan config:clear
   ```

3. **Stop Vite (Ctrl+C) and restart:**
   ```powershell
   npm run dev
   ```

4. **Update `vite.config.js`:**
   ```javascript
   server: {
       host: '0.0.0.0',
       hmr: {
           host: '127.0.0.1', // Use IPv4, not IPv6
       },
   }
   ```

5. **Access via IPv4:**
   - Use: `http://127.0.0.1:8000` ✅
   - NOT: `http://localhost:8000` ❌ (can use IPv6)
   - NOT: `http://192.168.1.19:8000` ❌ (network IP)

---

### Problem 3: "Node.js version too old"

**Symptoms:**
```
The engine "node" is incompatible with this module. Expected version "^18.0.0 || ^20.0.0 || >=21.0.0".
```

**Solution:**

1. **Download Node.js v20.x:**
   - Go to: https://nodejs.org/
   - Download LTS version (v20.19.0 or newer)

2. **Install and restart terminal**

3. **Verify:**
   ```powershell
   node --version
   # Should show: v20.19.0 or higher
   ```

4. **Re-install npm packages:**
   ```powershell
   npm install
   ```

---

### Problem 4: "Port 5173 is already in use"

**Symptoms:**
```
Port 5173 is already in use
```

**Solution:**

1. **Find the terminal running Vite:**
   - Look for a terminal with output: `VITE v7.2.7 ready in 441 ms`

2. **Stop it:**
   - Press `Ctrl + C` in that terminal

3. **Restart Vite:**
   ```powershell
   npm run dev
   ```

---

### Problem 5: "DataTables warning: table id=users-table"

**Symptoms:**
- DataTables shows an alert dialog
- Table doesn't load data

**Common Causes:**

1. **Ajax URL is wrong:**
   - Check route exists in `routes/web.php`
   - Check route name matches: `route('admin.teachers.data')`

2. **Column mismatch:**
   - Number of `<th>` tags must match number of columns in JavaScript
   - Column names must match database columns or relationships

3. **Missing CSRF token:**
   - Ensure `<meta name="csrf-token" content="{{ csrf_token() }}">` is in layout

**Solution:**
- Open browser console (F12) → Network tab
- Look for the Ajax request to `/admin/teachers/data`
- Check if it returns 200 OK
- Click on it → Preview tab → See the error message

---

### Problem 6: "Call to undefined method DataTables::of()"

**Symptoms:**
```
Call to undefined method Yajra\DataTables\Facades\DataTables::of()
```

**Solution:**

1. **Check if Yajra is installed:**
   ```powershell
   composer show | grep datatables
   ```
   Should show: `yajra/laravel-datatables-oracle`

2. **If missing, install:**
   ```powershell
   composer require yajra/laravel-datatables-oracle:"^11.0"
   ```

3. **Clear cache:**
   ```powershell
   php artisan config:clear
   php artisan cache:clear
   ```

---

## 📝 Quick Reference Commands

### Daily Development Commands

```powershell
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite (keep running!)
npm run dev

# Terminal 3: Run migrations (when needed)
php artisan migrate

# Clear caches (when changing .env or config)
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### First-Time Setup Commands

```powershell
# 1. Clone project
git clone <repo-url>
cd "School Management System by BTeam"

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Copy .env file
copy .env.example .env

# 5. Generate app key
php artisan key:generate

# 6. Configure database in .env, then migrate
php artisan migrate

# 7. Start development servers
# Terminal 1:
php artisan serve

# Terminal 2:
npm run dev
```

### Production Build Commands

```powershell
# Build assets for production (minified, optimized)
npm run build

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🎓 Available Helper Functions

After setup, these are available globally in all Blade views:

### 1. `initServerSideDataTable(selector, ajaxUrl, columns, options)`
Initialize a server-side DataTable.

```javascript
let table = initServerSideDataTable(
    '#teachers-table',
    '/admin/teachers/data',
    [
        { data: 'id', name: 'id' },
        { data: 'name', name: 'name' },
        { data: 'action', name: 'action', orderable: false }
    ]
);
```

### 2. `confirmDelete(title, text, onConfirm, confirmButtonText)`
Show SweetAlert2 confirmation dialog.

```javascript
confirmDelete(
    'Delete Teacher?',
    'This cannot be undone.',
    function() {
        // Delete logic here
    }
);
```

### 3. `showSuccessToast(message)`
Show success notification.

```javascript
showSuccessToast('Teacher created successfully!');
```

### 4. `showErrorToast(message)`
Show error notification.

```javascript
showErrorToast('Failed to delete teacher');
```

---

## 📚 Additional Resources

- **Yajra DataTables Docs:** https://yajrabox.com/docs/laravel-datatables
- **DataTables.js Docs:** https://datatables.net/
- **SweetAlert2 Docs:** https://sweetalert2.github.io/
- **Vite Docs:** https://vitejs.dev/
- **Laravel 11 Docs:** https://laravel.com/docs/11.x

---

## ✅ Checklist: Is Everything Working?

Use this checklist to verify your setup:

- [ ] Node.js v18+ installed (`node --version`)
- [ ] npm packages installed (`npm list jquery`)
- [ ] Composer packages installed (`composer show | grep datatables`)
- [ ] `.env` has `APP_URL=http://127.0.0.1:8000`
- [ ] `vite.config.js` has `server: { host: '0.0.0.0' }`
- [ ] `resources/js/app.js` imports jQuery, DataTables, SweetAlert2
- [ ] `resources/js/bootstrap.js` has `window.$ = window.jQuery = $`
- [ ] `resources/views/layouts/app.blade.php` has `@vite` and `@stack`
- [ ] Vite is running (`npm run dev`) and shows no errors
- [ ] Laravel server is running (`php artisan serve`)
- [ ] Browser shows: `✅ Bootstrap.js loaded`, `✅ App.js loaded`
- [ ] Browser shows: `✅ DataTable initialized`
- [ ] No red errors in browser console (F12)

---

## 🎉 Congratulations!

You now have a fully configured modern CRUD stack!

You can create new DataTable pages by:
1. Creating a controller with `getData()` method
2. Defining routes (index + data)
3. Creating a Blade view with `@extends('layouts.app')` and `@push('scripts')`
4. Using `initServerSideDataTable()` helper

Happy coding! 🚀

---

**Last Updated:** December 9, 2025  
**Laravel Version:** 11.x  
**Vite Version:** 7.2.7  
**Yajra DataTables Version:** 11.0  







