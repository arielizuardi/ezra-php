<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Google_Service_Sheets;
use Google_Client;

define('GOOGLE_CLIENT_SESSION_KEY', 'gclient');

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        $scopes = [
            'openid',
            'profile',
            'email',
            Google_Service_Sheets::SPREADSHEETS_READONLY,
        ];

        return
            Socialite::driver('google')
                ->scopes($scopes)
                ->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $socialite_user = Socialite::driver('google')->user();
        $google_client_token = [
            'access_token' => $socialite_user->token,
            'refresh_token' => $socialite_user->refreshToken,
            'expires_in' => $socialite_user->expiresIn
        ];

        $google_client = new Google_Client();
        $google_client->setApplicationName("Ezra");
        $google_client->setDeveloperKey(env('GOOGLE_SERVER_KEY'));
        $google_client->setAccessToken(json_encode($google_client_token));

        $user = $this->findUser($socialite_user);
        if (empty($user)) {
            $google_client->revokeToken();
            return redirect('/')->with('flash_message', 'Email kamu belum terdaftar di dalam sistem kami. Silahkan hubungi administrator.');
        }

        Auth::login($user, true);
        session([GOOGLE_CLIENT_SESSION_KEY => serialize($google_client)]);

        return redirect('/dashboard');
    }

    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param  $user Socialite user object
     * @param $provider Social auth provider
     * @return  User
     */
    public function findOrCreateUser($user, $provider)
    {
        $authUser = User::where('provider_id', $user->id)->first();
        if ($authUser) {
            return $authUser;
        }
        return User::create([
            'name'     => $user->name,
            'email'    => $user->email,
            'provider' => $provider,
            'provider_id' => $user->id,
            'provider_access_token' => $user->token,
            'avatar' => str_replace('?sz=50', '', $user->avatar),
        ]);
    }

    public function findUser($socialite_user)
    {
        $user = User::where('email', $socialite_user->email)->first();
        $user->provider = 'google';
        $user->provider_id = $socialite_user->id;
        $user->provider_access_token = $socialite_user->token;
        $user->avatar = str_replace('?sz=50', '', $socialite_user->avatar);
        $user->save();

        return $user;
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        if (!empty(session(GOOGLE_CLIENT_SESSION_KEY))) {
            /**
             * @var $google_client Google_Client
             */
            $google_client = unserialize(session(GOOGLE_CLIENT_SESSION_KEY));
            $google_client->revokeToken();
        }


        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect('/');
    }
}
