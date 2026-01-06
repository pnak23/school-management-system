<?php
// កំណត់ឈ្មោះអាដាប់ទ័រគោលដៅ
$adapterName = "Wireless LAN adapter Wi-Fi";

// អនុវត្ត ipconfig និងចាប់យកលទ្ធផល
exec("ipconfig", $output);

$ip = null;
$adapterFound = false;

// រកមើលអាដាប់ទ័រជាក់លាក់និងអាសយដ្ឋាន IPv4 របស់វា
foreach ($output as $line) {
    // ពិនិត្យមើលអាដាប់ទ័រគោលដៅ
    if (strpos($line, $adapterName) !== false) {
        $adapterFound = true;
        continue;
    }

    // ប្រសិនបើរកឃើញអាដាប់ទ័រ សូមរកមើលអាសយដ្ឋាន IPv4
    if ($adapterFound && strpos($line, "IPv4 Address") !== false) {
        if (preg_match('/\d+\.\d+\.\d+\.\d+/', $line, $matches)) {
            $ip = $matches[0];
            break;
        }
    }
}

// ចាប់ផ្តើម Laravel server ជាមួយអាសយដ្ឋាន IP ដែលបានរកឃើញ
if ($ip) {
    $url = "http://$ip:8000";
    echo "កំពុងចាប់ផ្តើម Laravel server នៅ $url\n"; // បង្ហាញ URL ដែលអាចចុចបាន
    echo "ចុច Ctrl+Click លើតំណខាងលើដើម្បីបើកក្នុងកម្មវិធីរុករកតាមអ៊ីនធឺណិតរបស់អ្នក។\n";

    // ប្រើ "start" សម្រាប់ Windows ដើម្បីធានាថា Laravel server ដំណើរការនៅក្នុង terminal ថ្មី
    exec("start php artisan serve --host=$ip --port=8000");
} else {
    echo "បរាជ័យក្នុងការទាញយកអាសយដ្ឋាន IPv4 សម្រាប់អាដាប់ទ័រ: $adapterName\n";
}
