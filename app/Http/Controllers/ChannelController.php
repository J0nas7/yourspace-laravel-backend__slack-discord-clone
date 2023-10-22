<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Channel;
use App\Helpers\DataService;
use Illuminate\Support\Facades\Auth;

use DateTime;

class ChannelController extends Controller
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

    // Edit existing channel in a space
    public function editChannel()
    {
        $editFailed = false;
        $errorMsg = "";

        // Setting variables
        $Space_Name = $this->request->Space_Name;
        $Channel_Name = $this->request->Channel_Name;
        $Old_Channel_Name = $this->request->Old_Channel_Name;

        // If credentials are empty
        if (empty($Channel_Name) || empty($Old_Channel_Name)) {
            $editFailed = true;
            $errorMsg = "Missing neccesary credentials.";
        }

        // Check that Channel_Name is not occupied
        $nameOccupied = Channel::where("Channel_Name", $Channel_Name)->first();
        if ($nameOccupied) {
            $editFailed = true;
            $errorMsg = "The channel name is already taken.";
        }

        $space = Space::select('Space_ID')->where("Space_Name", $Space_Name)->first();
        
        // If space exists, and there was no errors, save changes to channel
        if ($space && !$editFailed) {
            $Space_ID = $space->Space_ID;
            $channel = Channel::where('Channel_Name', $Old_Channel_Name)->where("Channel_SpaceID", $Space_ID)->update(['Channel_Name' => $Channel_Name]);
        }

        // Send successfull response
        if (!$editFailed && $channel) {
            return response()->json([
                'success' => true,
                'message' => 'The channel was edited',
                'data'    => [
                    'Channel_Name' => $Channel_Name,
                    'Channel' => $channel
                ]
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Channel Edit Failed '),
            'data'    => false
        ], 200);
    }

    // Create a new channel in a space
    public function createNewChannel()
    {
        $Channel_Format_Array = array("TEXT", "AUDIO", "VIDEO");
        $createFailed = false;
        $errorMsg = "";

        $Channel_Name = $this->request->Channel_Name;
        $Channel_Format = strtoupper($this->request->Channel_Format);

        // If credentials are empty
        if (empty($Channel_Name) || empty($Channel_Format)) {
            $createFailed = true;
            $errorMsg = "Missing neccesary credentials.";
        }

        // Check that Channel_Name is not occupied
        $nameOccupied = Channel::where("Channel_Name", $Channel_Name)->first();
        if ($nameOccupied) {
            $createFailed = true;
            $errorMsg = "The channel name is already taken.";
        }

        // Check that chosen Channel_Format is in the default array.
        if (!in_array($Channel_Format, $Channel_Format_Array)) {
            $createFailed = true;
            $errorMsg = "Invalid channel format.";
        }

        // There was no errors, create channel.
        if (!$createFailed) {
            $channel = Channel::create([
                'Channel_Name' => $Channel_Name,
                'Channel_Type' => $Channel_Format,
                'Channel_ProfileID' => '1',
                'Channel_SpaceID' => '4'
            ]);
        }

        // Send successfull response
        if (!$createFailed && $channel) {
            return response()->json([
                'success' => true,
                'message' => 'The channel was created',
                'data'    => $channel
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Channel Creation Failed '),
            'data'    => false
        ], 200);
    }
}
?>