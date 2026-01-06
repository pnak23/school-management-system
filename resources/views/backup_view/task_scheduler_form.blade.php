@extends('layouts.app_Yajra_Datatable')
<link rel="stylesheet" type="text/css" href="{{ asset('lib/sweetalert2/css/sweetalert2.min.css') }}">
<script type="text/javascript" src="{{ asset('lib/sweetalert2/js/sweetalert2.all.min.js') }}"></script>

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">បង្កើតកម្មវិធីកំណត់ពេលសម្រាប់ Windows</h3>
            <a href="{{ route('backup.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
            </a>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> ការណែនាំ</h5>
                <p>បំពេញទម្រង់ខាងក្រោមដើម្បីបង្កើតឯកសារសម្រាប់កម្មវិធីកំណត់ពេលនៅក្នុង Windows។ ឯកសារទាំងនេះនឹងត្រូវបានទាញយកជា ZIP ហើយអ្នកត្រូវតែ:</p>
                <ol>
                    <li>ពន្លា ZIP ហើយរក្សាទុកឯកសារទាំងពីរ (XML និង BAT) នៅកន្លែងដែលអ្នកអាចរកឃើញពួកវា។</li>
                    <li>ដើម្បីដំឡើង, ចុចស្ដាំលើឯកសារ XML ហើយជ្រើសរើស "ស្ដារកិច្ចការ(import)"។</li>
                    <li>បើមានការស្នើសុំការផ្ទៀងផ្ទាត់, ផ្តល់ព័ត៌មានចូលរបស់អ្នក។</li>
                </ol>
                <p>ការធ្វើបែបនេះនឹងកំណត់កម្មវិធីកំណត់ពេលរបស់ Windows ឱ្យដំណើរការកម្មវិធីរត់តាមកាលវិភាគរបស់ Laravel អំឡុងពេលដែលជ្រើសរើស, ដែលនឹងធ្វើឱ្យការBack Upរបស់អ្នកដំណើរការដោយស្វ័យប្រវត្តិ។</p>
            </div>

            <form action="{{ route('backup.createSchedulerTask') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="task_name">ឈ្មោះកិច្ចការ <span class="text-danger">*</span></label>
                    <input type="text" id="task_name" name="task_name" class="form-control" value="{{ old('task_name', 'HR-Management-Backup-Task') }}" required>
                    <small class="text-muted">ឈ្មោះសម្រាប់កិច្ចការកំណត់ពេល។ គួរតែជាឈ្មោះដែលមានលក្ខណៈឯកតា ហើយងាយចងចាំ។</small>
                </div>
                
                <div class="form-group">
                    <label for="interval_minutes">ចន្លោះពេលដំណើរការ (នាទី) <span class="text-danger">*</span></label>
                    <input type="number" id="interval_minutes" name="interval_minutes" class="form-control" value="{{ old('interval_minutes', 5) }}" min="1" required>
                    <small class="text-muted">រៀងរាល់ប៉ុន្មាននាទីដែលកម្មវិធីកំណត់ពេលគួរតែដំណើរការ។ 5 នាទីគឺជាតម្លៃលំនាំដើម និងណែនាំ។</small>
                </div>
                
                <div class="form-group">
                    <label for="php_path">ទីតាំង PHP <span class="text-danger">*</span></label>
                    <input type="text" id="php_path" name="php_path" class="form-control" value="{{ old('php_path', 'D:\PHP_sarana\Xammp\php\php.exe') }}" required>
                    <small class="text-muted">ផ្លូវពេញទៅកាន់ឯកសារ PHP.exe នៅលើម៉ាស៊ីនរបស់អ្នក។ ជាធម្មតាវានៅក្នុងថតឯកសាររបស់ XAMPP, WAMP, ឬការដំឡើង PHP ផ្សេងទៀត។</small>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download"></i> បង្កើត និងទាញយកឯកសារកម្មវិធីកំណត់ពេល
                    </button>
                    <a href="{{ route('backup.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> បោះបង់
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 