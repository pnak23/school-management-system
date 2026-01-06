<?php

namespace App\Http\Requests\Auth;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\LibraryGuest;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');
        $password = $this->input('password');
        $remember = $this->boolean('remember');

      
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        
        if (Auth::attempt([$field => $login, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        
        $student = Student::where('is_active', true)
            ->where(function ($query) use ($login) {
                $query->where('khmer_name', $login)
                    ->orWhere('english_name', $login)
                    ->orWhere('code', $login);
            })
            ->first();

        if ($student && $student->user_id) {
            $user = \App\Models\User::find($student->user_id);
            if ($user && Auth::attempt(['id' => $user->id, 'password' => $password], $remember)) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

        
        $teacher = Teacher::where('is_active', true)
            ->where(function ($query) use ($login) {
                $query->where('khmer_name', $login)
                    ->orWhere('english_name', $login)
                    ->orWhere('teacher_code', $login);
            })
            ->first();

        if ($teacher && $teacher->user_id) {
            $user = \App\Models\User::find($teacher->user_id);
            if ($user && Auth::attempt(['id' => $user->id, 'password' => $password], $remember)) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

     
        $staff = Staff::where('is_active', true)
            ->where(function ($query) use ($login) {
                $query->where('khmer_name', $login)
                    ->orWhere('english_name', $login)
                    ->orWhere('staff_code', $login);
            })
            ->first();

        if ($staff && $staff->user_id) {
            $user = \App\Models\User::find($staff->user_id);
            if ($user && Auth::attempt(['id' => $user->id, 'password' => $password], $remember)) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

       
        $guest = LibraryGuest::where('is_active', true)
            ->where(function ($query) use ($login) {
                $query->where('full_name', $login)
                    ->orWhere('id_card_no', $login);
            })
            ->first();

        if ($guest && $guest->user_id) {
            $user = \App\Models\User::find($guest->user_id);
            if ($user && Auth::attempt(['id' => $user->id, 'password' => $password], $remember)) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

       
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
