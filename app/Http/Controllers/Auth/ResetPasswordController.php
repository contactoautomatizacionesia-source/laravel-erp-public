<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Illuminate\Support\Facades\DB;
use Modules\UserActivityLog\Traits\LogActivity;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    public function __construct()
    {
        $this->middleware('maintenance_mode');
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     *We overwrote the validation method to meet security criteria
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required', 
                'confirmed', 
                PasswordRules::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ];
    }

    protected function resetPassword($user, $password)
    {
        $user->password = \Hash::make($password);
        $user->password_updated_at = now();
        $user->save();

        // Invalidates previous active sessions
        Auth::logoutOtherDevices($password);

        // Log entry (activity log table)
        LogActivity::successLog('Password reset via email link for: ' . $user->email);
    }

    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token');
        $email = $request->email;
        $user = User::where('email', $email)->first();

        // If the record doesn't exist or the token doesn't match the one in the database (hashed)
        // Note: Laravel stores hashed tokens, so we use Password::getRepository() to verify
        if (!$user || !Password::getRepository()->exists($user, $token)) {
            // Redirect with an error message to the login
            return redirect()->route('login')->with('error', __('auth.reset_link_invalid'));
        }
        
        return view(theme('auth.reset'))->with(
            ['token' => $token, 'email' => $request->email]
        );
    }
}
