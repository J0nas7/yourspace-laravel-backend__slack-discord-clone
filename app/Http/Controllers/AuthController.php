<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Helpers\DataService;
use App\Models\Channel;
use App\Models\Member;
use App\Models\Space;
use App\Models\User;
use DateTime;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    private $request;

    // Instantiate a new controller instance
    public function __construct(Request $request)
    {
        $this->request = json_decode($request->input('postContent')) ?? $request;

        $this->middleware('auth:api', ['except' => [
            'userData',
            'userLogin',
            'userCreate',
            'userLoggedInTest',
            'refreshJWT',
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
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Is NOT logged in',
            'data'    => false
        ], 200);
    }

    /**
     * refreshJWT
     * Refresh JWT token
     *
     * @return response json
     */
    public function refreshJWT()
    {
        try {
            $newToken = Auth::refresh();
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'error' => 'JWT refresh failed',
                'message' => $e->getMessage()
            ], 401);
        }

        return response()->json([
            'success' => true,
            'authorisation' => [
                'newAccessToken' => $newToken
            ]
        ]);
    }

    /**
     * userData
     * Return user data by the Auth facade
     *
     * @return response json
     */
    public function userData()
    {
        $selectFailed = false;
        $errorMsg = "";

        // Setting variables
        $Space_Name = $this->request->Space_Name ?? '';

        // Check that Space_Name exists
        $space = Space::where("Space_Name", $Space_Name)->first();
        
        // There was no errors, return user data message.
        if (!$selectFailed && Auth::check()) {
            $user = Auth::user();
            if ($space) {
                $userRole = Member::where("Member_SpaceID", $space->Space_ID)->where("Member_ProfileID", $user->Profile_ID)->first();
                $user->Member_Role = $userRole->Member_Role;
            }
            return response()->json([
                'success' => true,
                'message' => 'User data returned',
                'data'    => $user
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'User Data Request Failed '),
            'data'    => false
        ], 200);
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
     * @param  string $request->Profile_Email
     * @param  string $request->Profile_Password
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
        $token = false;
        if (!$loginFailed) {
            $token = Auth::attempt($credentials);
        }

        // If login succeeds, authenticate the user
        if ($token && !$loginFailed) {
            $profile = Auth::user();
            $memberOfSpaces = Member::where("Member_ProfileID", $profile->Profile_ID)->count();
            return response()->json([
                'success' => true,
                'user' => $profile,
                'memberOfSpaces' => $memberOfSpaces,
                'authorisation' => [
                    'accessToken' => $token,
                    'refreshToken' => Auth::refresh()
                ]
            ], 200);
        }

        $loginFailed = true;
        $errorMsg = "Login Attempt Failed";
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'User Login Failed '),
            'data'    => false
        ], 200);
    }

    /**
     * userCreate
     * Create user by the Auth facade
     *
     * @param  string $request->Profile_RealName
     * @param  string $request->Profile_DisplayName
     * @param  string $request->Profile_Email
     * @param  string $request->Profile_Password
     * @return response json
     */
    public function userCreate()
    {
        $createFailed = false;
        $errorMsg = "";

        $Profile_RealName = $this->request->Profile_RealName;
        $Profile_DisplayName = $this->request->Profile_DisplayName;
        $Profile_Email = $this->request->Profile_Email;
        $Profile_Password = $this->request->Profile_Password;
        $Profile_Password2 = $this->request->Profile_Password2;
        $Profile_BirthdayDD = $this->request->Profile_BirthdayDD;
        $Profile_BirthdayMM = $this->request->Profile_BirthdayMM;
        $Profile_BirthdayYYYY = $this->request->Profile_BirthdayYYYY;

        $Profile_Birthday = new DateTime();
        $Profile_Birthday->setDate($Profile_BirthdayYYYY, $Profile_BirthdayMM, $Profile_BirthdayDD);
        $Profile_Birthday->setTime(0, 0, 0);

        if (empty($Profile_RealName) || empty($Profile_DisplayName) || 
            empty($Profile_Email) || empty($Profile_Password) || 
            empty($Profile_Password2) || empty($Profile_BirthdayDD) || 
            empty($Profile_BirthdayMM) || empty($Profile_BirthdayYYYY))
        {
            $createFailed = true;
            $errorMsg = "Missing neccesary credentials.";
        }

        if (!$Profile_Birthday) {
            $createFailed = true;
            $errorMsg = "Wrong format in birthday.";
        }

        if ($Profile_Password !== $Profile_Password2) {
            $createFailed = true;
            $errorMsg = "Passwords does not match.";
        }

        if (!$createFailed && !filter_var($Profile_Email, FILTER_VALIDATE_EMAIL)) {
            $createFailed = true;
            $errorMsg = "Invalid email address.";
        }

        // Check that Profile_DisplayName and Profile_Email is not occupied
        $displayNameOccupied = User::where("Profile_DisplayName", $Profile_DisplayName)->first();
        $emailOccupied = User::where("Profile_Email", $Profile_Email)->first();
        if ($displayNameOccupied || $emailOccupied) {
            $createFailed = true;
            $errorMsg = "Display-name or e-mail is already taken.";
        }

        if (!$createFailed) {
            $profile = User::create([
                'Profile_RealName' => $Profile_RealName,
                'Profile_DisplayName' => $Profile_DisplayName,
                'Profile_Email' => $Profile_Email,
                'Profile_Password' => $Profile_Password,
                'Profile_Birthday' => $Profile_Birthday->format('Y-m-d H:i:s'),
                'Profile_ImageUrl' => '',
            ]);
        }

        if (!$createFailed && $profile) {
            $credentials['Profile_Email'] = $Profile_Email;
            $credentials['password'] = $Profile_Password;
            $token = Auth::attempt($credentials);

            $memberOfSpaces = Member::where("Member_ProfileID", $profile->Profile_ID)->count();

            return response()->json([
                'success' => true,
                'message' => 'The user was created and logged in',
                'data'    => $profile,
                'memberOfSpaces' => $memberOfSpaces,
                'authorisation' => [
                    'accessToken' => $token,
                    'refreshToken' => Auth::refresh()
                ]
            ], 200);
        }
        
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Profile Creation Failed '),
            'data'    => false
        ], 200);
    }
}
