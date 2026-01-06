<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vite Connection Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="padding: 50px; font-family: Arial;">
    <h1>Vite Connection Test</h1>
    
    <div id="status" style="padding: 20px; margin: 20px 0; border: 2px solid #ccc; border-radius: 8px;">
        <p>Testing Vite connection...</p>
    </div>
    
    <script>
      
        setTimeout(function() {
            const statusDiv = document.getElementById('status');
            
            let html = '<h2>Test Results:</h2><ul style="list-style: none; padding: 0;">';
            
            
            if (typeof window.$ !== 'undefined') {
                html += '<li style="color: green; font-size: 18px; margin: 10px 0;">✅ jQuery is LOADED! Version: ' + $.fn.jquery + '</li>';
            } else {
                html += '<li style="color: red; font-size: 18px; margin: 10px 0;">❌ jQuery is NOT loaded</li>';
                html += '<li style="color: red; margin: 10px 0 10px 40px;">→ This means Vite is NOT running</li>';
                html += '<li style="color: red; margin: 10px 0 10px 40px;">→ Open PowerShell and run: npm run dev</li>';
            }
            
            
            html += '<li style="margin: 20px 0; font-weight: bold;">Vite Status:</li>';
            
            
            const scripts = document.querySelectorAll('script[src*="@vite"]');
            if (scripts.length === 0) {
                html += '<li style="color: green; margin: 10px 0 10px 40px;">✅ Vite assets are loading</li>';
            } else {
                html += '<li style="color: red; margin: 10px 0 10px 40px;">❌ Vite assets NOT loading</li>';
            }
            
            html += '</ul>';
            
            
            html += '<div style="margin-top: 30px; padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px;">';
            html += '<h3 style="margin-top: 0;">📋 How to Fix:</h3>';
            html += '<ol style="line-height: 1.8;">';
            html += '<li>Open PowerShell</li>';
            html += '<li>Type: <code style="background: #f0f0f0; padding: 2px 8px; border-radius: 4px;">cd "D:\\New project2025\\School Management System by BTeam"</code></li>';
            html += '<li>Press Enter</li>';
            html += '<li>Type: <code style="background: #f0f0f0; padding: 2px 8px; border-radius: 4px;">npm run dev</code></li>';
            html += '<li>Press Enter</li>';
            html += '<li>Wait until you see: <strong>VITE v5.x.x ready</strong></li>';
            html += '<li><strong>KEEP THAT WINDOW OPEN!</strong></li>';
            html += '<li>Refresh this page (Ctrl+Shift+R)</li>';
            html += '</ol>';
            html += '</div>';
            
            statusDiv.innerHTML = html;
            
            console.log('=== VITE TEST ===');
            console.log('jQuery loaded:', typeof window.$ !== 'undefined');
            console.log('jQuery version:', typeof window.$ !== 'undefined' ? $.fn.jquery : 'N/A');
        }, 1000);
    </script>
</body>
</html>



<html>
<head>
    <meta charset="utf-8">
    <title>Vite Connection Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="padding: 50px; font-family: Arial;">
    <h1>Vite Connection Test</h1>
    
    <div id="status" style="padding: 20px; margin: 20px 0; border: 2px solid #ccc; border-radius: 8px;">
        <p>Testing Vite connection...</p>
    </div>
    
    <script>
      
        setTimeout(function() {
            const statusDiv = document.getElementById('status');
            
            let html = '<h2>Test Results:</h2><ul style="list-style: none; padding: 0;">';
            
           
            if (typeof window.$ !== 'undefined') {
                html += '<li style="color: green; font-size: 18px; margin: 10px 0;">✅ jQuery is LOADED! Version: ' + $.fn.jquery + '</li>';
            } else {
                html += '<li style="color: red; font-size: 18px; margin: 10px 0;">❌ jQuery is NOT loaded</li>';
                html += '<li style="color: red; margin: 10px 0 10px 40px;">→ This means Vite is NOT running</li>';
                html += '<li style="color: red; margin: 10px 0 10px 40px;">→ Open PowerShell and run: npm run dev</li>';
            }
            
          
            html += '<li style="margin: 20px 0; font-weight: bold;">Vite Status:</li>';
            
          
            const scripts = document.querySelectorAll('script[src*="@vite"]');
            if (scripts.length === 0) {
                html += '<li style="color: green; margin: 10px 0 10px 40px;">✅ Vite assets are loading</li>';
            } else {
                html += '<li style="color: red; margin: 10px 0 10px 40px;">❌ Vite assets NOT loading</li>';
            }
            
            html += '</ul>';
            
            
            html += '<div style="margin-top: 30px; padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px;">';
            html += '<h3 style="margin-top: 0;">📋 How to Fix:</h3>';
            html += '<ol style="line-height: 1.8;">';
            html += '<li>Open PowerShell</li>';
            html += '<li>Type: <code style="background: #f0f0f0; padding: 2px 8px; border-radius: 4px;">cd "D:\\New project2025\\School Management System by BTeam"</code></li>';
            html += '<li>Press Enter</li>';
            html += '<li>Type: <code style="background: #f0f0f0; padding: 2px 8px; border-radius: 4px;">npm run dev</code></li>';
            html += '<li>Press Enter</li>';
            html += '<li>Wait until you see: <strong>VITE v5.x.x ready</strong></li>';
            html += '<li><strong>KEEP THAT WINDOW OPEN!</strong></li>';
            html += '<li>Refresh this page (Ctrl+Shift+R)</li>';
            html += '</ol>';
            html += '</div>';
            
            statusDiv.innerHTML = html;
            
            console.log('=== VITE TEST ===');
            console.log('jQuery loaded:', typeof window.$ !== 'undefined');
            console.log('jQuery version:', typeof window.$ !== 'undefined' ? $.fn.jquery : 'N/A');
        }, 1000);
    </script>
</body>
</html>









