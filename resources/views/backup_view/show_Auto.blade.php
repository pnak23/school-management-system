@extends('layouts.app_Yajra_Datatable')
<link rel="stylesheet" type="text/css" href="{{ asset('lib/sweetalert2/css/sweetalert2.min.css') }}">
<script type="text/javascript" src="{{ asset('lib/sweetalert2/js/sweetalert2.all.min.js') }}"></script>

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">ព័ត៌មានលម្អិតនៃកាលវិភាគបម្រុងទុក #{{ $schedule->id }}</h3>
            <div>
                <a href="{{ route('backup.edit', $schedule->id) }}" class="btn btn-info">
                    <i class="fas fa-edit"></i> កែប្រែ
                </a>
                <a href="{{ route('backup.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">ព័ត៌មានមូលដ្ឋាន</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 40%">លេខសម្គាល់</th>
                                    <td>{{ $schedule->id }}</td>
                                </tr>
                                <tr>
                                    <th>ស្ថានភាព</th>
                                    <td>
                                        <span class="badge badge-{{ $schedule->is_active ? 'success' : 'danger' }} p-2">
                                            {{ $schedule->is_active ? 'សកម្ម' : 'អសកម្ម' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>ពេលវេលាបម្រុងទុក</th>
                                    <td>{{ $schedule->formatted_backup_time }}</td>
                                </tr>
                                <tr>
                                    <th>ពេលវេលាស្ដារឡើងវិញ</th>
                                    <td>{{ $schedule->formatted_restore_time ?? 'មិនបានកំណត់' }}</td>
                                </tr>
                                <tr>
                                    <th>ពេលវេលាសម្អាត</th>
                                    <td>{{ $schedule->formatted_clean_time ?? 'មិនបានកំណត់' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">ព័ត៌មានបន្ថែម</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 40%">ពិពណ៌នា</th>
                                    <td>{{ $schedule->description ?? 'មិនមានពិពណ៌នា' }}</td>
                                </tr>
                                <tr>
                                    <th>បានបង្កើតនៅ</th>
                                    <td>{{ $schedule->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>បានធ្វើបច្ចុប្បន្នភាពនៅ</th>
                                    <td>{{ $schedule->updated_at }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">សកម្មភាព</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <form action="{{ route('backup.toggleActive', $schedule->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-{{ $schedule->is_active ? 'warning' : 'success' }}">
                                        <i class="fas fa-{{ $schedule->is_active ? 'pause' : 'play' }}"></i>
                                        {{ $schedule->is_active ? 'បិទដំណើរការ' : 'បើកដំណើរការ' }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('backup.destroy', $schedule->id) }}" method="POST" class="d-inline delete-form ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> លុប
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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