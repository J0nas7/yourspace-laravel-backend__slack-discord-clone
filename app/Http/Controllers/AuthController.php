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
    public function __construct(Request $request) {
        $this->request = json_decode($request->input('postContent'));


    }

    /**
     * userLoggedInTest
     * Validate if userLoggedIn Session is set or not
     *
     * @return response json
     */
    public function userLoggedInTest(Request $request) {
        if (Session::get('userLoggedIn')) {
            return response()->json([
                'success' => true,
                'message' => 'Is logged in',
                'data'    => Session::get('userLoggedIn')
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
     * userLogout
     * Log out user by deleting the Session
     *
     * @return response json
     */
    public function userLogout() {
        Session::forget('userLoggedIn');
        Session::flush();
        //$request->session()->forget('adminLoggedIn');
        //$request->session()->flush();
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
    public function userLogin(Request $request) {
        $theEmail = $this->request->email ?? $request->email;
        $thePassword = $this->request->password ?? $request->password;

        // Validate that input request is filled
        $inputs['email'] = $theEmail;
        $inputs['password'] = $thePassword;
        $validator = Validator::make($inputs, [
            'email' => 'required',
            'password' => 'required',
        ]);
        /*$validated = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);*/

        $loginFailed = false;
        $errorMsg = "";

        // Login attempts with email and password
        $authResultWithEmail = Auth::validate([
            'User_Email' => $theEmail, // or using 'username' too
            'User_Password' => $thePassword,
        ]);
        
        // If a login attempt succeeds, put a valid session
        if ($authResultWithEmail) {
            Session::put('userLoggedIn', 'yes');
            return response()->json([
                'success' => true,
                'message' => 'User Login',
                'data'    => true
            ], 200);
        // Login attempt failed
        } else if (!$validator->fails() && empty($errorMsg)) {
            $loginFailed = true;
            $errorMsg = "Login Attempt Failed";
        }

        // If input request fails validation
        if ($validator->fails() && empty($errorMsg)) {
            $loginFailed = true;
            $errorMsg = "Empty request";
        }

        if ($loginFailed) {
            return response()->json([
                'success' => false,
                'message' => (!empty($errorMsg) ? $errorMsg : 'User Login Failed '),
                'data'    => false
            ], 200);
        }
    }
}