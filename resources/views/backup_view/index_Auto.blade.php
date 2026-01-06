{{-- @extends('layouts.app_A') --}}
@extends('layouts.app_Yajra_Datatable')
<link rel="stylesheet" type="text/css" href="{{ asset('lib/sweetalert2/css/sweetalert2.min.css') }}">
<script type="text/javascript" src="{{ asset('lib/sweetalert2/js/sweetalert2.all.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">ការកំណត់កាលវិភាគBack Upប្រព័ន្ធ</h3>
            <a href="{{ route('backup.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> បង្កើតកាលវិភាគថ្មី
            </a>
        </div>
        <div class="card-body">
            @if (session('status'))
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'ជោគជ័យ',
                        text: '{{ session("status") }}',
                        confirmButtonColor: '#3085d6'
                    });
                </script>
            @endif

            @if (session('error'))
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'បរាជ័យ',
                        text: '{{ session("error") }}',
                        confirmButtonColor: '#d33'
                    });
                </script>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>លេខរៀង</th>
                            <th>ពេលវេលាBack Up</th>
                            <th>ពេលវេលាRestore</th>
                            <th>ពេលវេលាសម្អាត</th>
                            <th>ពិពណ៌នា</th>
                            <th>ស្ថានភាព</th>
                            <th>កាលបរិច្ឆេទបង្កើត</th>
                            <th>កាលបរិច្ឆេទEdit</th>
                            <th>សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td>{{ $schedule->id }}</td>
                                <td>{{ $schedule->formatted_backup_time }}</td>
                                <td>{{ $schedule->formatted_restore_time ?? '-' }}</td>
                                <td>{{ $schedule->formatted_clean_time ?? '-' }}</td>
                                <td>{{ $schedule->description ?? '-' }}</td>
                                <td>
                                    <span style="font-size: 12px; color: black;" class="badge badge-{{ $schedule->is_active ? 'success' : 'danger' }}">
                                        {{ $schedule->is_active ? 'សកម្ម' : 'អសកម្ម' }}
                                    </span>
                                </td>
                                <td>{{ $schedule->created_at }}</td>
                                <td>{{ $schedule->updated_at }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('backup.edit', $schedule->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('backup.toggleActive', $schedule->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-{{ $schedule->is_active ? 'warning' : 'success' }}" title="{{ $schedule->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $schedule->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('backup.destroy', $schedule->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">មិនមានកាលវិភាគBack Upត្រូវបានកំណត់នៅឡើយទេ</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">បង្កើតកម្មវិធីកំណត់ពេលរបស់ Windows</h5>
                    </div>
                    <div class="card-body">
                        <p>បង្កើតឯកសារកម្មវិធីកំណត់ពេលរបស់ Windows ដើម្បីដំណើរការកម្មវិធីកាលវិភាគ Laravel ជាទៀងទាត់។</p>
                        <a href="{{ route('backup.taskSchedulerForm') }}" class="btn btn-info">
                            <i class="fas fa-clock"></i> បង្កើតកម្មវិធីកំណត់ពេលរបស់ Windows
                        </a>
                    </div>
                </div>
            </div>
            
            @if (count($schedules) == 0)
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">បង្កើតកាលវិភាគដំបូង</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('backup.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="backup_time">ពេលវេលាBack Up (HH:MM) <span class="text-danger">*</span></label>
                                    <input type="time" id="backup_time" name="backup_time" class="form-control" required>
                                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងធ្វើការBack Upដោយស្វ័យប្រវត្តិ</small>
                                </div>
                                <div class="form-group">
                                    <label for="restore_time">ពេលវេលាRestore (HH:MM) (optional)</label>
                                    <input type="time" id="restore_time" name="restore_time" class="form-control">
                                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងស្ដារការBack Upឡើងវិញ (ប្រសិនបើចាំបាច់)</small>
                                </div>
                                <div class="form-group">
                                    <label for="clean_time">ពេលវេលាសម្អាត (HH:MM) (optional)</label>
                                    <input type="time" id="clean_time" name="clean_time" class="form-control">
                                    <small class="text-muted">កំណត់ពេលវេលាដែលប្រព័ន្ធនឹងលុបការBack Upចាស់ៗ</small>
                                </div>
                                <div class="form-group">
                                    <label for="description">ពិពណ៌នា (optional)</label>
                                    <textarea id="description" name="description" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" checked>
                                        <label class="custom-control-label" for="is_active">សកម្ម</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">បង្កើតកាលវិភាគ</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'តើអ្នកប្រាកដឬ?',
                text: "អ្នកមិនអាចត្រឡប់វិញបានទេបន្ទាប់ពីលុប!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'យល់ព្រម លុប!',
                cancelButtonText: 'បោះបង់'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
});
</script>
@endsection
