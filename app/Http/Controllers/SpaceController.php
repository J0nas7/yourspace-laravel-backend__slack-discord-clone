<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Channel;
use App\Helpers\DataService;
use Illuminate\Support\Facades\Auth;

use DateTime;

class SpaceController extends Controller
{
    private $request;
    private $searchTerm;
    private $pageNr;

    // Instantiate a new controller instance
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->searchTerm = json_decode($this->request->input('postContent'))->searchTerm ?? null;
        $this->pageNr = json_decode($this->request->input('postContent'))->pageNr ?? null;
        $this->request = json_decode($this->request->input('postContent'));
    }

    // Get spaces list
    public function getSpacesList()
    {
        // Grab the spaces list
        $spacesList = Space::select(array('Space_ID', 'Space_Name'))
                                /*->where("Channel_SpaceID", '=', $Space_ID)
                                ->where('Channel_Type', '=', $Channel_Format)*/
                                ->get();
        
        // Return the spaces list
        if ($spacesList) {
            return response()->json([
                'success' => true,
                'message' => 'Spaces list returned',
                'data'    => $spacesList
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Spaces list request failed '),
            'data'    => false
        ], 200);
    }

    // Get specific space from the unique space name
    public function getTheSpace()
    {
        // Setting variables
        $Space_Name = $this->request->Space_Name;
        $space = Space::select(array('Space_ID', 'Space_Name'))->where("Space_Name", $Space_Name)->first();
        
        // If space exists, return the space
        if ($space) {
            return response()->json([
                'success' => true,
                'message' => 'The space returned',
                'data'    => $space
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Get space request failed '),
            'data'    => false
        ], 200);
    }

    // Get format channels list
    public function getChannelsList()
    {
        // Setting variables
        $Space_Name = $this->request->Space_Name;
        $Channel_Format = $this->request->Channel_Format;
        $Channel_Format = strtoupper($Channel_Format);
        $space = Space::where("Space_Name", $Space_Name)->first();
        
        // If space exists, grab the Space_ID
        if ($space) {
            $Space_ID = $space->Space_ID;
    
            // DB get channel list
            $channelList = Channel::select('Channel_Name')
                                    ->where("Channel_SpaceID", '=', $Space_ID)
                                    ->where('Channel_Type', '=', $Channel_Format)
                                    ->get();
        }
        
        // Return the channellist
        if ($space && $channelList) {
            return response()->json([
                'success' => true,
                'message' => 'Channel list returned',
                'data'    => $channelList
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Channel list request failed '),
            'data'    => false
        ], 200);
    }

    // Create a new space
    public function createNewSpace()
    {
        $createFailed = false;
        $errorMsg = "";

        $Space_Name = $this->request->Space_Name;
        $Space_ImageUrl = $this->request->Space_ImageUrl;

        // Check that Space_Name is filled
        if (empty($Space_Name)) {
            $createFailed = true;
            $errorMsg = "Missing space name";
        }

        // Check that Space_Name is not occupied
        $nameOccupied = Space::where("Space_Name", $Space_Name)->first();
        if ($nameOccupied) {
            $createFailed = true;
            $errorMsg = "The space name is already taken.";
        }

        // Check that Space_ImageUrl is a valid URL.
        if (!$createFailed && $Space_ImageUrl && !filter_var($Space_ImageUrl, FILTER_VALIDATE_URL)) {
            $createFailed = true;
            $errorMsg = "Invalid space image url.";
        }

        // There was no errors, create space
        if (!$createFailed) {
            $space = Space::create([
                'Space_Name' => $Space_Name,
                'Space_ImageUrl' => $this->request->Space_ImageUrl ?? '',
                'Space_InviteCode' => '',
                'Space_ProfileID' => Auth::user()->Profile_ID ?? 1
            ]);
        }

        // Send successfull response
        if (!$createFailed && $space) {
            return response()->json([
                'success' => true,
                'message' => 'The space was created',
                'data'    => $space
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Space Creation Failed '),
            'data'    => false
        ], 200);
    }
}
