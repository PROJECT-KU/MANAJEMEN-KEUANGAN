<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyTestMail;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    // Override function to show the password reset email form
    public function showLinkRequestForm(Request $request)
    {
        return view('auth.passwords.email');
    }

    // Check if email is registered and redirect to password reset form if registered
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We can\'t find a user with that email address.'],
            ]);
        }

        $this->broker()->sendResetLink(
            $request->only('email')
        );

        return back()->with(['status' => 'We have e-mailed your password reset link!']);
    }
}
