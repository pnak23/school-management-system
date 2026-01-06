@extends('layouts.app_Yajra_Datatable')
<link rel="stylesheet" type="text/css" href="{{ asset('lib/sweetalert2/css/sweetalert2.min.css') }}">
<script type="text/javascript" src="{{ asset('lib/sweetalert2/js/sweetalert2.all.min.js') }}"></script>

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">កែប្រែកាលវិភាគបម្រុងទុក #{{ $schedule->id }}</h3>
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

            <form action="{{ route('backup.update', $schedule->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="backup_time">ពេលវេលាបម្រុងទុក (HH:MM) <span class="text-danger">*</span></label>
                    <input type="time" id="backup_time" name="backup_time" class="form-control" value="{{ old('backup_time', substr($schedule->backup_time, 0, 5)) }}" required>
                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងធ្វើការបម្រុងទុកដោយស្វ័យប្រវត្តិ</small>
                </div>
                <div class="form-group">
                    <label for="restore_time">ពេលវេលាស្ដារឡើងវិញ (HH:MM) (optional)</label>
                    <input type="time" id="restore_time" name="restore_time" class="form-control" value="{{ old('restore_time', !empty($schedule->restore_time) ? substr($schedule->restore_time, 0, 5) : '') }}">
                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងស្ដារការបម្រុងទុកឡើងវិញ (ប្រសិនបើចាំបាច់)</small>
                </div>
                <div class="form-group">
                    <label for="clean_time">ពេលវេលាសម្អាត (HH:MM) (optional)</label>
                    <input type="time" id="clean_time" name="clean_time" class="form-control" value="{{ old('clean_time', !empty($schedule->clean_time) ? substr($schedule->clean_time, 0, 5) : '') }}">
                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងលុបការបម្រុងទុកចាស់ៗ</small>
                </div>
                <div class="form-group">
                    <label for="description">ពិពណ៌នា (optional)</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $schedule->description) }}</textarea>
                    <small class="text-muted">បន្ថែមពិពណ៌នាជាជំនួយដល់ការចងចាំសម្រាប់កាលវិភាគនេះ</small>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">សកម្ម</label>
                    </div>
                    <small class="text-muted">កំណត់ថាតើកាលវិភាគនេះគួរត្រូវបានដំណើរការឬអត់</small>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> រក្សាទុកការផ្លាស់ប្តូរ
                    </button>
                    <a href="{{ route('backup.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> បោះបង់
                    </a>
                </div>
            </form>
        </div>
        <div class="card-footer bg-light text-muted">
            <div class="row">
                <div class="col-md-6">
                    <small>បានបង្កើតនៅ: {{ $schedule->created_at }}</small>
                </div>
                <div class="col-md-6 text-right">
                    <small>បានធ្វើបច្ចុប្បន្នភាពចុងក្រោយនៅ: {{ $schedule->updated_at }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 