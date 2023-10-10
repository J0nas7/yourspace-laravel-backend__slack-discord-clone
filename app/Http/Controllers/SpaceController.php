<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Helpers\DataService;
use Illuminate\Support\Facades\Auth;

use DateTime;

class SpaceController extends Controller
{
    private $request;
    private $searchTerm;
    private $pageNr;

    // Instantiate a new controller instance
    public function __construct(Request $request) {
        $this->request = json_decode($request->input('postContent')) ?? $request;
        $this->searchTerm = json_decode($this->request->input('postContent'))->searchTerm ?? null;
        $this->pageNr = json_decode($this->request->input('postContent'))->pageNr ?? null;
    }

    // Create a new space
    public function createNewSpace() {
        $createFailed = false;
        $errorMsg = "";

        $Space_Name = $this->request->Space_Name;

        if (empty($this->request->Space_Name)) {
            $createFailed = true;
            $errorMsg = "Missing space name";
        }
        
        // Check that Space_Name is not occupied
        $nameOccupied = Space::where("Space_Name", $Space_Name)->first();
        if ($nameOccupied) {
            $createFailed = true;
            $errorMsg = "The space name is already taken.";
        }

        if (!$createFailed) {
            $space = Space::create([
                'Space_Name' => $Space_Name,
                'Space_ImageUrl' => $this->request->Space_ImageUrl ?? '',
                'Space_InviteCode' => '',
                'Space_ProfileID' => Auth::user()->Profile_ID ?? 1
            ]);
        }
        
        if (!$createFailed && $space) {
            return response()->json([
                'success' => true,
                'message' => 'The space was created',
                'data'    => $space
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => (!empty($errorMsg) ? $errorMsg : 'Space Creation Failed '),
                'data'    => false
            ], 200);
        }
    }
}
