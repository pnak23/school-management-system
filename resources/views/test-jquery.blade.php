<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>jQuery Test Page</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">jQuery & DataTables Test</h1>
            
            <div id="results" class="my-6 p-6 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-gray-600">Testing...</p>
            </div>
            
            <div class="flex gap-4">
                <button onclick="testJQuery()" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Test jQuery
                </button>
                
                <button onclick="testSwal()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Test SweetAlert2
                </button>
                
                <a href="{{ route('admin.students-dt.index') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition inline-block">
                    Go to Students
                </a>
            </div>
            
            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="font-bold text-yellow-800 mb-2">⚠️ Important:</h3>
                <ul class="list-disc list-inside text-yellow-700 space-y-1">
                    <li>Make sure <code class="bg-yellow-100 px-2 py-1 rounded">npm run dev</code> is running in a terminal</li>
                    <li>Check terminal for any Vite errors</li>
                    <li>Hard refresh browser: <code class="bg-yellow-100 px-2 py-1 rounded">Ctrl+Shift+R</code> (Windows) or <code class="bg-yellow-100 px-2 py-1 rounded">Cmd+Shift+R</code> (Mac)</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function testJQuery() {
            const results = document.getElementById('results');
            let html = '<h3 class="text-xl font-bold text-gray-900 mb-4">Test Results:</h3><ul class="space-y-2">';
            
            // Test jQuery
            if (typeof $ !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> jQuery ($) is loaded - Version: ' + $.fn.jquery + '</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> jQuery ($) is NOT loaded</li>';
            }
            
            // Test jQuery global
            if (typeof jQuery !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> jQuery (jQuery) is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> jQuery (jQuery) is NOT loaded</li>';
            }
            
            // Test DataTables
            if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> DataTables is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> DataTables is NOT loaded</li>';
            }
            
            // Test SweetAlert2
            if (typeof Swal !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> SweetAlert2 is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> SweetAlert2 is NOT loaded</li>';
            }
            
            // Test Helper Functions
            if (typeof initServerSideDataTable !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> initServerSideDataTable helper is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> initServerSideDataTable helper is NOT loaded</li>';
            }
            
            if (typeof confirmDelete !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> confirmDelete helper is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> confirmDelete helper is NOT loaded</li>';
            }
            
            html += '</ul>';
            
           
            html += '<div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">';
            html += '<h4 class="font-bold text-blue-800 mb-2">Console Check:</h4>';
            html += '<p class="text-blue-700">Open browser console (F12) and check for any red errors.</p>';
            html += '</div>';
            
            results.innerHTML = html;
            
            console.log('=== jQuery & DataTables Test ===');
            console.log('jQuery ($):', typeof $ !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
            console.log('DataTables:', typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
            console.log('SweetAlert2:', typeof Swal !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
            console.log('initServerSideDataTable:', typeof initServerSideDataTable !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
            console.log('confirmDelete:', typeof confirmDelete !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
        }
        
        function testSwal() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'SweetAlert2 is working correctly!',
                    confirmButtonText: 'Awesome!',
                    confirmButtonColor: '#4F46E5'
                });
            } else {
                alert('SweetAlert2 is not loaded. Make sure npm run dev is running.');
            }
        }
        
      
        window.addEventListener('load', function() {
            setTimeout(testJQuery, 500);
        });
    </script>
</body>
</html>



<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>jQuery Test Page</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">jQuery & DataTables Test</h1>
            
            <div id="results" class="my-6 p-6 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-gray-600">Testing...</p>
            </div>
            
            <div class="flex gap-4">
                <button onclick="testJQuery()" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Test jQuery
                </button>
                
                <button onclick="testSwal()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Test SweetAlert2
                </button>
                
                <a href="{{ route('admin.students-dt.index') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition inline-block">
                    Go to Students
                </a>
            </div>
            
            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="font-bold text-yellow-800 mb-2">⚠️ Important:</h3>
                <ul class="list-disc list-inside text-yellow-700 space-y-1">
                    <li>Make sure <code class="bg-yellow-100 px-2 py-1 rounded">npm run dev</code> is running in a terminal</li>
                    <li>Check terminal for any Vite errors</li>
                    <li>Hard refresh browser: <code class="bg-yellow-100 px-2 py-1 rounded">Ctrl+Shift+R</code> (Windows) or <code class="bg-yellow-100 px-2 py-1 rounded">Cmd+Shift+R</code> (Mac)</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function testJQuery() {
            const results = document.getElementById('results');
            let html = '<h3 class="text-xl font-bold text-gray-900 mb-4">Test Results:</h3><ul class="space-y-2">';
            
          
            if (typeof $ !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> jQuery ($) is loaded - Version: ' + $.fn.jquery + '</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> jQuery ($) is NOT loaded</li>';
            }
            
           
            if (typeof jQuery !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> jQuery (jQuery) is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> jQuery (jQuery) is NOT loaded</li>';
            }
            
           
            if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> DataTables is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> DataTables is NOT loaded</li>';
            }
            
           
            if (typeof Swal !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> SweetAlert2 is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> SweetAlert2 is NOT loaded</li>';
            }
            
           
            if (typeof initServerSideDataTable !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> initServerSideDataTable helper is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> initServerSideDataTable helper is NOT loaded</li>';
            }
            
            if (typeof confirmDelete !== 'undefined') {
                html += '<li class="flex items-center text-green-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> confirmDelete helper is loaded</li>';
            } else {
                html += '<li class="flex items-center text-red-600"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> confirmDelete helper is NOT loaded</li>';
            }
            
            html += '</ul>';
            
           
            html += '<div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">';
            html += '<h4 class="font-bold text-blue-800 mb-2">Console Check:</h4>';
            html += '<p class="text-blue-700">Open browser console (F12) and check for any red errors.</p>';
            html += '</div>';
            
            results.innerHTML = html;
            
            console.log('=== jQuery & DataTables Test ===');
            console.log('jQuery ($):', typeof $ !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
            console.log('DataTables:', typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
            console.log('SweetAlert2:', typeof Swal !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
            console.log('initServerSideDataTable:', typeof initServerSideDataTable !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
            console.log('confirmDelete:', typeof confirmDelete !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
        }
        
        function testSwal() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'SweetAlert2 is working correctly!',
                    confirmButtonText: 'Awesome!',
                    confirmButtonColor: '#4F46E5'
                });
            } else {
                alert('SweetAlert2 is not loaded. Make sure npm run dev is running.');
            }
        }
        
       
        window.addEventListener('load', function() {
            setTimeout(testJQuery, 500);
        });
    </script>
</body>
</html>









