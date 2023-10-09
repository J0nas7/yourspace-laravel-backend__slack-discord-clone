<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Helpers\DataService;

class AuthController extends Controller
{
    private $request;

    // Instantiate a new controller instance
    public function __construct(Request $request)
    {
        $this->request = json_decode($request->input('postContent'));

        $this->middleware('auth:api', ['except' => [
            'userLogin', 
            'userLoggedInTest'
        ]]);
    }

    /**
     * userLoggedInTest
     * Validate if Auth user is logged in
     *
     * @return response json
     */
    public function userLoggedInTest(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'success' => true,
                'message' => 'Is logged in',
                'data'    => Auth::user()
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Is NOT logged in',
                'data'    => false
            ], 200);
        }
    }

    /**
     * refreshJWT
     * Refresh JWT token
     *
     * @return response json
     */
    public function refreshJWT()
    {
        return response()->json([
            'success' => true,
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * userLogout
     * Log out user by the Auth facade
     *
     * @return response json
     */
    public function userLogout()
    {
        Auth::logout();
        return response()->json([
            'success' => true,
            'message' => 'Is logged out',
            'data'    => true
        ], 200);
    }

    /**
     * userLogin
     * Login user with email and password credentials
     *
     * @param  string $request->email
     * @param  string $request->password
     * @return response json
     */
    public function userLogin(Request $request)
    {
        $theEmail = $this->request->Profile_Email ?? $request->Profile_Email;
        $thePassword = $this->request->Profile_Password ?? $request->Profile_Password;

        // Validate login credentials are allowed
        $inputs['Profile_Email'] = $theEmail;
        $inputs['password'] = $thePassword;
        $validator = Validator::make($inputs, [
            'Profile_Email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $inputs;

        $loginFailed = false;
        $errorMsg = "";

        // Credentials are empty or not allowed
        if ($validator->fails() || empty($theEmail) || empty($thePassword)) {
            $loginFailed = true;
            $errorMsg = "Missing or incorrect cridentials";
        }

        // Try login attempt with fulfilled email and password
        if (!$loginFailed) {
            $token = Auth::attempt($credentials);
        }

        // If login succeeds, authenticate the user
        if ($token && !$loginFailed) {
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 200);
        } else if (!$token && empty($errorMsg)) {
            $loginFailed = true;
            $errorMsg = "Login Attempt Failed";
        }

        if ($loginFailed) {
            return response()->json([
                'success' => false,
                'message' => (!empty($errorMsg) ? $errorMsg : 'User Login Failed '),
                'data'    => false
            ], 401);
        }
    }
}
