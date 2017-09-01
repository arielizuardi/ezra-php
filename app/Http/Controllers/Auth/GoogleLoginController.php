<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Google_Client;


class GoogleLoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function signInWithGoogle(Request $request)
    {
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->get('id_token'));
        if (!$payload) {
            return response()->json(['error' => 'Unauthorized user - Google'], 401);
        }

        $email = $request->get('email');
        if (empty($email)) {
            return response()->json(['error' => 'Email required'], 400);
        }

        $auth_response = $request->get('auth_response');
        if (empty($auth_response)) {
            return response()->json(['error' => 'Auth Response Required', 'detail' => json_encode($auth_response)], 400);
        }

        $user = User::where('email', $email)->first();
        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized user'], 401);
        }

        \Log::info($auth_response);

        $access_token = $auth_response['access_token'];
        $user->provider_id = $request->get('id');
        $user->provider_access_token = $access_token;
        $user->avatar = str_replace('?sz=50', '', $request->get('avatar'));
        $user->save();

        \Auth::login($user);

        return response()->json(['redirect_to' => url('/dashboard')], 200);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signOutGoogle(Request $request)
    {
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return response()->json('', 204);
    }
}