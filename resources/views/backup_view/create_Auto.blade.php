@extends('layouts.app_Yajra_Datatable')
<link rel="stylesheet" type="text/css" href="{{ asset('lib/sweetalert2/css/sweetalert2.min.css') }}">
<script type="text/javascript" src="{{ asset('lib/sweetalert2/js/sweetalert2.all.min.js') }}"></script>

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">បង្កើតកាលវិភាគបម្រុងទុកថ្មី</h3>
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

            <form action="{{ route('backup.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="backup_time">ពេលវេលាបម្រុងទុក (HH:MM) <span class="text-danger">*</span></label>
                    <input type="time" id="backup_time" name="backup_time" class="form-control" value="{{ old('backup_time') }}" required>
                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងធ្វើការបម្រុងទុកដោយស្វ័យប្រវត្តិ</small>
                </div>
                <div class="form-group">
                    <label for="restore_time">ពេលវេលាស្ដារឡើងវិញ (HH:MM) (optional)</label>
                    <input type="time" id="restore_time" name="restore_time" class="form-control" value="{{ old('restore_time') }}">
                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងស្ដារការបម្រុងទុកឡើងវិញ (ប្រសិនបើចាំបាច់)</small>
                </div>
                <div class="form-group">
                    <label for="clean_time">ពេលវេលាសម្អាត (HH:MM) (optional)</label>
                    <input type="time" id="clean_time" name="clean_time" class="form-control" value="{{ old('clean_time') }}">
                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងលុបការបម្រុងទុកចាស់ៗ</small>
                </div>
                <div class="form-group">
                    <label for="description">ពិពណ៌នា (optional)</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    <small class="text-muted">បន្ថែមពិពណ៌នាជាជំនួយដល់ការចងចាំសម្រាប់កាលវិភាគនេះ</small>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">សកម្ម</label>
                    </div>
                    <small class="text-muted">កំណត់ថាតើកាលវិភាគនេះគួរត្រូវបានដំណើរការឬអត់</small>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> បង្កើតកាលវិភាគ
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