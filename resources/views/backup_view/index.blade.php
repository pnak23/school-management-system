@extends('layouts.app_A')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('lib/sweetalert2/css/sweetalert2.min.css') }}">
<script type="text/javascript" src="{{ asset('lib/sweetalert2/js/sweetalert2.all.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- <link rel="stylesheet" type="text/css" href="{{ asset('css/Modal.css') }}"> -->
<div class="container custom-font">
<h1>ការកំណត់បម្រុងទុក(Back up)និងស្តារ(Restore)ឡើងវិញ</h1>
    <!-- Clock display area -->
    <h3>🟢រយៈពេលកន្លងទៅ: <span id="clock">00:00:00</span></h3>

    <div class="progress mt-3">
        <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;"
            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
    </div>
    <div class="form-group">

<label for="queue_worker_status">
🔴ចូរធីកសញ្ញា ✔️ក្នុងប្រអប់
    <input type="checkbox" id="queue_worker_status" onchange="toggleQueueWorker(this)" {{ $queueWorkerRunning ? 'checked' : '' }}>
    ដើម្បីដំណើរការQueue Worker
</label>
</div>



    <form id="backup-settings-form" class="custom-font011">
        @csrf
        <div class="form-group">
            <label for="backup_directory">🟦ថត(Folder/directory)សម្រាប់ផ្ទុកបម្រុងទុក(Back up)</label>
            <div class="input-group">
                <select id="backup_directory_select" class="form-control custom-font010">
                    <option value="">-- ជ្រើសរើសថត(Folder/directory)ដែលមានស្រាប់ --</option>
                    @foreach ($backupDirectories as $dir)
                        <option value="{{ $dir }}">{{ $dir }}</option>
                    @endforeach
                </select>
                <input type="text" id="backup_directory" name="backup_directory" class="form-control custom-font010" placeholder="ឬបញ្ចូលឈ្មោះថត(Folder/directory)ថ្មី">
            </div>
        </div>
        <div class="form-group ">
            <label for="restore_directory">🟩ថត(Folder/directory)ស្តារ(Restore)ឡើងវិញ</label>
            <div class="input-group ">
                <select id="restore_directory_select" class="form-control custom-font010">
                    <option value="">-- ជ្រើសរើសថត(Folder/directory)ដែលមានស្រាប់ --</option>
                    @foreach ($backupDirectories as $dir)
                        <option value="{{ $dir }}">{{ $dir }}</option>
                    @endforeach
                </select>
                <input type="text" id="restore_directory" name="restore_directory" class="form-control custom-font010" placeholder="ឬបញ្ចូលឈ្មោះថត(Folder/directory)ថ្មី">
            </div>
        </div>
    </form>
    <button onclick="resetForm()" class="btn btn-secondary">Reset</button>
    <hr>
    
    <button onclick="confirmBackup('backup')" class="btn btn-success custom-font010">បម្រុងទុក (Back up)ជា Zip file </button>
    <button onclick="confirmBackup('backup_as_sql')" class="btn btn-success custom-font010">បម្រុងទុក (Back up)ជា SQL </button>
    <button onclick="confirmBackup('backup_as_winra')" class="btn btn-success custom-font010">បម្រុងទុក (Back up)ជា WinRA </button>
    <br><br>
    <button onclick="confirmRestore('restore')" class="btn btn-warning custom-font010">ស្តារ ( Restore) Zip ឡើងវិញ</button>
    <button onclick="confirmRestore('restore_as_sql')" class="btn btn-warning custom-font010">ស្តារ (Restore) SQL ឡើងវិញ</button>
    <button onclick="confirmRestore('restore_as_winra')" class="btn btn-warning custom-font010">ស្តារ (Restore) WinRA ឡើងវិញ</button>
    <br><br>

    <button onclick="confirmClean('zip')" class="btn btn-danger custom-font010A">សម្អាត(Clean) backup Zip file</button>
<button onclick="confirmClean('sql')" class="btn btn-danger custom-font010A">Clean backup SQL file</button>
<button onclick="confirmClean('winra')" class="btn btn-danger custom-font010A">Clean backup WinRA file</button>

    

</div><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<script>
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

    // Update the timer and progress display
    function updateClock() {
        const hours = String(Math.floor(secondsElapsed / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((secondsElapsed % 3600) / 60)).padStart(2, '0');
        const seconds = String(secondsElapsed % 60).padStart(2, '0');
        document.getElementById("clock").innerText = `${hours}:${minutes}:${seconds}`;
    }

    function updateProgressBar(progress) {
        const progressBar = document.getElementById("progress-bar");
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        progressBar.innerText = Math.floor(progress) + '%';
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
        clearInterval(timer);
        secondsElapsed = 0;
        updateClock(); // Initialize clock display
        timer = setInterval(function() {
            secondsElapsed++;
            updateClock();

            // Fetch progress from server
            axios.get('{{ route("backup.getBackupProgress") }}', {})
                .then(function(response) {
                    var progress = response.data.progress;
                    updateProgressBar(progress);

                    if (progress >= 100) {
                        clearInterval(timer);
                        updateProgressBar(100); // Ensure progress bar is full
                        Swal.fire({
                            icon: 'success',
                            title: 'បានបញ្ចប់!',
                            text: 'ប្រតិបត្តិការបានបញ្ចប់ដោយជោគជ័យ។',
                            timer: 3000
                        });
                    }
                })
                .catch(function(error) {
                    console.error('កំហុសក្នុងការទាញយកដំណើរការ:', error);
                    clearInterval(timer);
                });
        }, 1000);
    }

    // Backup button handlers
    function confirmBackup(action) {
        Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "តើអ្នកចង់ដំណើរការការបម្រុងទុក(Back up)នេះ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'បាទ/ចាស បម្រុងទុក(Back up)!'
        }).then((result) => {
            if (result.isConfirmed) {
                var backupDirectory = document.getElementById('backup_directory').value;
                if (!backupDirectory) {
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

    // Restore button handlers
    function confirmRestore(action) {
        Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "នេះនឹងស្តារ(Restore)ឡើងវិញពីការបម្រុងទុក(Back up)ដែលបានជ្រើសរើស។ ទិន្នន័យបច្ចុប្បន្នអាចត្រូវបានសរសេរជាន់លើ!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'បាទ/ចាស ស្តារ(Restore)ឡើងវិញ!'
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
            confirmButtonText: `បាទ/ចាស សម្អាត(Clean)ឯកសារ ${typeText} !`
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
                title: 'ចាប់ផ្តើមកម្មវិធីដំណើរការQueue Workerរ',
                text: "តើអ្នកចង់ចាប់ផ្តើមកម្មវិធីដំណើរការQueue Workerរទេ?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'បាទ/ចាស ចាប់ផ្តើម!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route("queue.start") }}', {
                        _token: '{{ csrf_token() }}',
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
                            checkbox.checked = false;
                        }
                    })
                    .catch(function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'កំហុស!',
                            text: error.response.data.message || 'មានកំហុសមួយបានកើតឡើង។',
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
                title: 'បញ្ឈប់កម្មវិធីដំណើរការQueueWorker',
                text: "តើអ្នកចង់បញ្ឈប់កម្មវិធីដំណើរការQueueWorkerទេ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'បាទ/ចាស បញ្ឈប់!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route("queue.stop") }}', {
                        _token: '{{ csrf_token() }}',
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
                            checkbox.checked = true;
                        }
                    })
                    .catch(function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'កំហុស!',
                            text: error.response.data.message || 'មានកំហុសមួយបានកើតឡើង។',
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
@endsection

<style>
    /* Container styles */
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Header styles */
    h1 {
        font-size: 2rem;
        color: #333;
        text-align: center;
        margin-bottom: 20px;
    }

    /* Clock display styles */
    #clock {
        font-size: 1.5rem;
        font-weight: bold;
        color: #007bff;
    }

    /* Progress bar styling */
    .progress {
        height: 30px;
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 10px;
    }

    .progress-bar {
        background-color: #28a745;
        color: white;
        font-weight: bold;
        line-height: 30px;
        text-align: center;
        transition: width 0.4s ease;
    }

    /* Button Styles */
    .btn {
        font-size: 1rem;
        font-family: 'Kh Metal Chrieng', sans-serif;
        padding: 10px 20px;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        color: white;
        border: none;
        font-weight: bold;
        margin: 5px;
    }

    .btn-success {
        background-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        transform: scale(1.05);
    }

    .btn-warning {
        background-color: #ffc107;
        color: #333;
    }

    .btn-warning:hover {
        background-color: #e0a800;
        transform: scale(1.05);
    }

    .btn-danger {
        background-color: #dc3545;
        
    }

    .btn-danger:hover {
        background-color: #c82333;
        transform: scale(1.05);
    }

    .btn-secondary {
        background-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        transform: scale(1.05);
    }

    /* Form group styling */
    .form-group label {
        font-weight: bold;
        color: #555;
    }

    .form-group input {
        border-radius: 5px;
        padding: 10px;
        font-size: 1rem;
    }

    .input-group {
        display: flex;
    }

    .input-group .form-control {
        width: 50%;
    }

    /* Spacing for sections */
    hr {
        margin: 20px 0;
        border: 1px solid #ddd;
    }
    .custom-font010 {
    font-family: 'Kh Metal Chrieng', sans-serif;
    font-size: 16px;
    color: black;
}
.custom-font010A {
    font-family: 'Kh Metal Chrieng', sans-serif;
    font-size: 12px;
    color: black;
}
</style>
