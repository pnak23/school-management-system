@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title text-center">ការកំណត់បម្រុងទុក(Back up)និងស្តារ(Restore)ឡើងវិញ</h3>
        </div>
        <div class="card-body">
            <!-- Clock and Queue Worker status -->
            <div class="alert alert-info rounded-lg">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-clock"></i> រយៈពេលកន្លងទៅ: <span id="clock" class="badge bg-primary rounded-pill">00:00:00</span>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="queue_worker_status" onchange="toggleQueueWorker(this)" {{ $queueWorkerRunning ? 'checked' : '' }}>
                        <label class="form-check-label" for="queue_worker_status">ដំណើរការQueue Worker</label>
                    </div>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="progress mb-4" style="height: 30px; border: 1px solid #dee2e6;">
                <div id="progress-bar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                    role="progressbar" style="width: 0%; min-width: 3em; transition: width 0.3s ease;" 
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <span class="fw-bold">0%</span>
                </div>
            </div>

            <!-- Directory selection form -->
            <form id="backup-settings-form" class="mb-4">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="backup_directory" class="form-label fw-bold mb-2">
                                <i class="fas fa-folder"></i> ថត(Folder/directory)សម្រាប់ផ្ទុកបម្រុងទុក(Back up)
                            </label>
                            <div class="input-group">
                                <select id="backup_directory_select" class="form-select form-control-sm">
                                    <option value="">-- ជ្រើសរើសថត(Folder/directory)ដែលមានស្រាប់ --</option>
                                    @foreach ($backupDirectories as $dir)
                                        <option value="{{ $dir }}">{{ $dir }}</option>
                                    @endforeach
                                </select>
                                <input type="text" id="backup_directory" name="backup_directory" class="form-control form-control-sm" placeholder="ឬបញ្ចូលឈ្មោះថត(Folder/directory)ថ្មី">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="restore_directory" class="form-label fw-bold mb-2">
                                <i class="fas fa-folder-open"></i> ថត(Folder/directory)ស្តារ(Restore)ឡើងវិញ
                            </label>
                            <div class="input-group">
                                <select id="restore_directory_select" class="form-select form-control-sm">
                                    <option value="">-- ជ្រើសរើសថត(Folder/directory)ដែលមានស្រាប់ --</option>
                                    @foreach ($backupDirectories as $dir)
                                        <option value="{{ $dir }}">{{ $dir }}</option>
                                    @endforeach
                                </select>
                                <input type="text" id="restore_directory" name="restore_directory" class="form-control form-control-sm" placeholder="ឬបញ្ចូលឈ្មោះថត(Folder/directory)ថ្មី">
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Reset button -->
            <div class="text-center mb-4">
                <button onclick="resetForm()" class="btn btn-sm btn-secondary px-3 rounded-pill">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>

            <hr>

            <!-- Backup section -->
            <div class="section mb-4">
                <h5 class="text-center mb-3">
                    <i class="fas fa-download"></i> បម្រុងទុក (Back up)
                </h5>
                <div class="d-flex justify-content-center flex-wrap">
                    <button onclick="confirmBackup('backup')" class="btn btn-sm btn-success m-1 px-3 rounded-pill">
                        <i class="fas fa-file-archive"></i> បម្រុងទុកជា Zip
                    </button>
                    <button onclick="confirmBackup('backup_as_sql')" class="btn btn-sm btn-success m-1 px-3 rounded-pill">
                        <i class="fas fa-database"></i> បម្រុងទុកជា SQL
                    </button>
                    <button onclick="confirmBackup('backup_as_winra')" class="btn btn-sm btn-success m-1 px-3 rounded-pill">
                        <i class="fas fa-file"></i> បម្រុងទុកជា WinRA
                    </button>
                </div>
            </div>

            <!-- Restore section -->
            <div class="section mb-4">
                <h5 class="text-center mb-3">
                    <i class="fas fa-upload"></i> ស្តារ (Restore) ឡើងវិញ
                </h5>
                <div class="d-flex justify-content-center flex-wrap">
                    <button onclick="confirmRestore('restore')" class="btn btn-sm btn-warning m-1 px-3 rounded-pill">
                        <i class="fas fa-file-archive"></i> ស្តារ Zip ឡើងវិញ
                    </button>
                    <button onclick="confirmRestore('restore_as_sql')" class="btn btn-sm btn-warning m-1 px-3 rounded-pill">
                        <i class="fas fa-database"></i> ស្តារ SQL ឡើងវិញ
                    </button>
                    <button onclick="confirmRestore('restore_as_winra')" class="btn btn-sm btn-warning m-1 px-3 rounded-pill">
                        <i class="fas fa-file"></i> ស្តារ WinRA ឡើងវិញ
                    </button>
                </div>
            </div>

            <!-- Clean section -->
            <div class="section mb-2">
                <h5 class="text-center mb-3">
                    <i class="fas fa-trash-alt"></i> សម្អាត (Clean) ឯកសារចាស់ៗ
                </h5>
                <div class="d-flex justify-content-center flex-wrap">
                    <button onclick="confirmClean('zip')" class="btn btn-sm btn-danger m-1 px-3 rounded-pill">
                        <i class="fas fa-file-archive"></i> សម្អាត ZIP
                    </button>
                    <button onclick="confirmClean('sql')" class="btn btn-sm btn-danger m-1 px-3 rounded-pill">
                        <i class="fas fa-database"></i> សម្អាត SQL
                    </button>
                    <button onclick="confirmClean('winra')" class="btn btn-sm btn-danger m-1 px-3 rounded-pill">
                        <i class="fas fa-file"></i> សម្អាត WinRA
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* General styles */
    body {
        background-color: #f8f9fa;
    }
    
    .container {
        max-width: 1000px;
        margin: 20px auto;
    }
    
    .card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .card-header {
        padding: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .card-title {
        font-size: 1.25rem;
        font-weight: bold;
        margin-bottom: 0;
        color: #333;
    }
    
    /* Progress bar styling */
    .progress {
        border-radius: 8px;
        background-color: #e9ecef;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .progress-bar {
        font-weight: bold;
        font-size: 0.95rem;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    #progress-bar {
        background: linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
    }
    
    /* Clock styling */
    #clock {
        font-size: 1rem;
        padding: 5px 10px;
    }
    
    /* Form controls */
    .form-control, .form-select {
        font-size: 0.9rem;
        border-radius: 5px;
        border: 1px solid #ced4da;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* Button styling */
    .btn {
        font-size: 0.85rem;
        font-weight: bold;
        transition: all 0.2s;
    }
    
    .btn-sm {
        padding: 0.35rem 0.65rem;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    /* Alerts */
    .alert {
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
    
    /* Sections */
    .section {
        padding: 0.75rem 0;
    }
    
    hr {
        margin: 1.5rem 0;
        border-color: #eee;
    }
    
    /* Switch styling */
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Configure axios defaults
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    // Set CSRF token for axios
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }

    // Handle backup directory selection
    document.getElementById('backup_directory_select').addEventListener('change', function() {
        document.getElementById('backup_directory').value = this.value;
    });

    // Handle restore directory selection
    document.getElementById('restore_directory_select').addEventListener('change', function() {
        document.getElementById('restore_directory').value = this.value;
    });

    let timer;
    let secondsElapsed = 0;
    let progressErrorCount = 0;

    // Initialize progress bar on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's existing progress
        axios.get('{{ route("backup.getBackupProgress") }}')
            .then(function(response) {
                var progress = response.data.progress || 0;
                if (progress > 0 && progress < 100) {
                    // If there's ongoing progress, start the timer
                    startProgress();
                } else {
                    // Otherwise, just update the display
                    updateProgressBar(progress);
                }
            })
            .catch(function(error) {
                // If error, just show 0%
                updateProgressBar(0);
            });
    });

    // Update the timer and progress display
    function updateClock() {
        const hours = String(Math.floor(secondsElapsed / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((secondsElapsed % 3600) / 60)).padStart(2, '0');
        const seconds = String(secondsElapsed % 60).padStart(2, '0');
        document.getElementById("clock").innerText = `${hours}:${minutes}:${seconds}`;
    }

    function updateProgressBar(progress) {
        const progressBar = document.getElementById("progress-bar");
        if (!progressBar) {
            console.error('Progress bar element not found!');
            return;
        }
        
        const progressValue = Math.max(0, Math.min(100, parseFloat(progress) || 0)); // Ensure between 0-100
        
        // Update width with smooth transition
        progressBar.style.width = progressValue + '%';
        progressBar.setAttribute('aria-valuenow', progressValue);
        
        // Update text content
        const textSpan = progressBar.querySelector('span');
        const displayText = Math.floor(progressValue) + '%';
        
        if (textSpan) {
            textSpan.textContent = displayText;
        } else {
            progressBar.innerHTML = '<span class="fw-bold">' + displayText + '</span>';
        }
        
        // Ensure progress bar is visible
        progressBar.style.minWidth = '3em';
        progressBar.style.display = 'flex';
        progressBar.style.visibility = 'visible';
        progressBar.style.opacity = '1';
        
        // Log for debugging
        if (progressValue > 0) {
            console.log('Progress bar updated:', displayText);
        }
    }

    // Reset form function
    function resetForm() {
        document.getElementById('backup-settings-form').reset();
        document.getElementById('backup_directory_select').value = '';
        document.getElementById('restore_directory_select').value = '';
        clearInterval(timer);
        secondsElapsed = 0;
        updateClock();
        updateProgressBar(0);
    }

    // Start the timer and progress
    function startProgress() {
        // Clear any existing timer
        if (timer) {
        clearInterval(timer);
        }
        
        // Reset counters
        secondsElapsed = 0;
        progressErrorCount = 0;
        
        // Initialize display
        updateClock();
        updateProgressBar(0);
        
        // Make progress bar visible
        const progressBarContainer = document.querySelector('.progress');
        if (progressBarContainer) {
            progressBarContainer.style.display = 'block';
        }
        
        // Start polling immediately
        function pollProgress() {
            secondsElapsed++;
            updateClock();

            // Fetch progress from server
            axios.get('{{ route("backup.getBackupProgress") }}')
                .then(function(response) {
                    progressErrorCount = 0; // Reset error count on success
                    var progress = parseFloat(response.data.progress) || 0;
                    
                    console.log('Progress:', progress + '%'); // Debug log
                    updateProgressBar(progress);

                    if (progress >= 100) {
                        clearInterval(timer);
                        updateProgressBar(100); // Ensure progress bar is full
                        updateClock(); // Final clock update
                        
                        // Show completion message
                        setTimeout(function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'បានបញ្ចប់!',
                            text: 'ប្រតិបត្តិការបានបញ្ចប់ដោយជោគជ័យ។',
                                timer: 3000,
                                showConfirmButton: true
                        });
                        }, 500);
                    }
                })
                .catch(function(error) {
                    progressErrorCount++;
                    console.error('កំហុសក្នុងការទាញយកដំណើរការ:', error);
                    
                    // Only stop polling after multiple consecutive errors
                    if (progressErrorCount >= 5) {
                        console.error('Too many errors, stopping progress polling');
                    clearInterval(timer);
                        Swal.fire({
                            icon: 'warning',
                            title: 'កំហុស!',
                            text: 'មិនអាចទាញយកដំណើរការបាន។ សូមពិនិត្យការតភ្ជាប់។',
                            timer: 3000
                        });
                    }
                });
        }
        
        // Poll immediately, then every second
        pollProgress();
        timer = setInterval(pollProgress, 1000);
    }

    // Track backup in progress to prevent duplicates
    let backupInProgress = false;

    // Backup button handlers
    function confirmBackup(action) {
        // Prevent duplicate backups
        if (backupInProgress) {
            Swal.fire({
                icon: 'warning',
                title: 'កំពុងដំណើរការ!',
                text: 'ការបម្រុងទុក(Back up)មួយកំពុងដំណើរការ។ សូមរង់ចាំ...',
                timer: 2000
            });
            return;
        }

        Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "តើអ្នកចង់ដំណើរការការបម្រុងទុក(Back up)នេះ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'បាទ/ចាស បម្រុងទុក(Back up)!',
            cancelButtonText: 'បោះបង់'
        }).then((result) => {
            if (result.isConfirmed) {
                backupInProgress = true;
                var backupDirectory = document.getElementById('backup_directory').value;
                if (!backupDirectory) {
                    backupInProgress = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'កំហុស!',
                        text: 'សូមកំណត់ថត(Folder/directory)សម្រាប់បម្រុងទុក(Back up)។',
                    });
                    return;
                }

                // Determine the route based on action
                var url = '';
                if (action === 'backup') {
                    url = '{{ route("backup.run") }}';
                } else if (action === 'backup_as_sql') {
                    url = '{{ route("backup.run_as_sql") }}';
                } else if (action === 'backup_as_winra') {
                    url = '{{ route("backup.run_as_winra") }}';
                }

                // Make AJAX request
                axios.post(url, {
                    backup_directory: backupDirectory,
                    _token: '{{ csrf_token() }}',
                })
                .then(function(response) {
                    if (response.data.status === 'success') {
                        // Start the progress bar and clock
                        startProgress();
                        Swal.fire({
                            icon: 'success',
                            title: 'ការបម្រុងទុក(Back up)បានចាប់ផ្តើម',
                            text: response.data.message,
                            timer: 2000
                        });
                        
                        // Reset backup flag when progress reaches 100%
                        setTimeout(function() {
                            const checkComplete = setInterval(function() {
                                axios.get('{{ route("backup.getBackupProgress") }}')
                                    .then(function(progressResponse) {
                                        if (progressResponse.data.progress >= 100 || progressResponse.data.progress === 0) {
                                            backupInProgress = false;
                                            clearInterval(checkComplete);
                                        }
                                    })
                                    .catch(function() {
                                        // On error, reset after 5 minutes
                                        setTimeout(function() {
                                            backupInProgress = false;
                                        }, 300000);
                                    });
                            }, 2000);
                        }, 2000);
                    } else {
                        backupInProgress = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'កំហុស!',
                            text: response.data.message,
                        });
                    }
                })
                .catch(function(error) {
                    backupInProgress = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'កំហុស!',
                        text: error.response?.data?.message || 'មានកំហុសមួយបានកើតឡើង។',
                    });
                });
            }
        });
    }

    // Restore button handlers
    function confirmRestore(action) {
        Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "នេះនឹងស្តារ(Restore)ឡើងវិញពីការបម្រុងទុក(Back up)ដែលបានជ្រើសរើស។ ទិន្នន័យបច្ចុប្បន្នអាចត្រូវបានសរសេរជាន់លើ!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'បាទ/ចាស ស្តារ(Restore)ឡើងវិញ!',
            cancelButtonText: 'បោះបង់'
        }).then((result) => {
            if (result.isConfirmed) {
                var restoreDirectory = document.getElementById('restore_directory').value;
                if (!restoreDirectory) {
                    Swal.fire({
                        icon: 'error',
                        title: 'កំហុស!',
                        text: 'សូមកំណត់ថត(Folder/directory)ដើម្បីស្តារ(Restore)ឡើងវិញ។',
                    });
                    return;
                }

                // Determine the route based on action
                var url = '';
                if (action === 'restore') {
                    url = '{{ route("backup.restore") }}';
                } else if (action === 'restore_as_sql') {
                    url = '{{ route("backup.restore_as_sql") }}';
                } else if (action === 'restore_as_winra') {
                    url = '{{ route("backup.restore_as_winra") }}';
                }

                // Make AJAX request
                axios.post(url, {
                    restore_directory: restoreDirectory,
                    _token: '{{ csrf_token() }}',
                })
                .then(function(response) {
                    if (response.data.status === 'success') {
                        // Start the progress bar and clock
                        startProgress();
                        Swal.fire({
                            icon: 'success',
                            title: 'ការស្តារ(Restore)ឡើងវិញបានចាប់ផ្តើម',
                            text: response.data.message,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'កំហុស!',
                            text: response.data.message,
                        });
                    }
                })
                .catch(function(error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'កំហុស!',
                        text: error.response.data.message || 'មានកំហុសមួយបានកើតឡើង។',
                    });
                });
            }
        });
    }

    // Clean button handler
    function confirmClean(type) {
        let typeText = type.toUpperCase();
        Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: `នេះនឹងសម្អាត(Clean)ឯកសារបម្រុងទុក(Back up) ${typeText} ចាស់ៗ ដោយរក្សាទុកតែឯកសារថ្មីបំផុត។ សកម្មភាពនេះមិនអាចត្រឡប់ក្រោយវិញបានទេ!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `បាទ/ចាស សម្អាត(Clean)ឯកសារ ${typeText} !`,
            cancelButtonText: 'បោះបង់'
        }).then((result) => {
            if (result.isConfirmed) {
                // Start the progress bar and clock
                startProgress();

                // Make AJAX request
                axios.post('{{ route("backup.clean") }}', {
                    _token: '{{ csrf_token() }}',
                    type: type
                })
                .then(function(response) {
                    if (response.data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'ជោគជ័យ!',
                            text: response.data.message,
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'កំហុស!',
                            text: response.data.message,
                        });
                    }
                })
                .catch(function(error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'កំហុស!',
                        text: error.response.data.message || 'មានកំហុសមួយបានកើតឡើង។',
                    });
                });
            }
        });
    }

    function toggleQueueWorker(checkbox) {
        if (checkbox.checked) {
            // Start the queue worker
            Swal.fire({
                title: 'ចាប់ផ្តើមកម្មវិធីដំណើរការQueue Worker',
                text: "តើអ្នកចង់ចាប់ផ្តើមកម្មវិធីដំណើរការQueue Worker ទេ?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'បាទ/ចាស ចាប់ផ្តើម!',
                cancelButtonText: 'បោះបង់',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route("queue.start") }}', {
                        _token: '{{ csrf_token() }}',
                    })
                    .then(function(response) {
                        if (response.data.status === 'success' || response.data.status === 'warning') {
                            Swal.fire({
                                icon: 'success',
                                title: 'ជោគជ័យ!',
                                text: response.data.message || 'Queue worker started successfully.',
                                timer: 2000
                            });
                            checkbox.checked = true;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'កំហុស!',
                                text: response.data.message || 'Failed to start queue worker.',
                            });
                            checkbox.checked = false;
                        }
                    })
                    .catch(function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'កំហុស!',
                            text: error.response?.data?.message || 'មានកំហុសមួយបានកើតឡើង។',
                        });
                        checkbox.checked = false;
                    });
                } else {
                    checkbox.checked = false;
                }
            });
        } else {
            // Stop the queue worker
            Swal.fire({
                title: 'បញ្ឈប់កម្មវិធីដំណើរការQueue Worker',
                text: "តើអ្នកចង់បញ្ឈប់កម្មវិធីដំណើរការQueue Worker ទេ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'បាទ/ចាស បញ្ឈប់!',
                cancelButtonText: 'បោះបង់',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route("queue.stop") }}', {
                        _token: '{{ csrf_token() }}',
                    })
                    .then(function(response) {
                        if (response.data.status === 'success' || response.data.status === 'warning') {
                            Swal.fire({
                                icon: 'success',
                                title: 'ជោគជ័យ!',
                                text: response.data.message || 'Queue worker stopped successfully.',
                                timer: 2000
                            });
                            checkbox.checked = false;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'កំហុស!',
                                text: response.data.message || 'Failed to stop queue worker.',
                            });
                            checkbox.checked = true;
                        }
                    })
                    .catch(function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'កំហុស!',
                            text: error.response?.data?.message || 'មានកំហុសមួយបានកើតឡើង។',
                        });
                        checkbox.checked = true;
                    });
                } else {
                    checkbox.checked = true;
                }
            });
        }
    }
</script>
@endpush 