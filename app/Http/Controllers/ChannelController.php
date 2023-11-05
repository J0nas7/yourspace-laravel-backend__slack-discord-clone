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

    // Create a new channel in a space
    public function createChannel()
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

    // Read "FORMAT" channels list
    public function readChannelsList()
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
            $channelList = Channel::select(array('Channel_ID', 'Channel_Name', 'Channel_Type'))
                ->where("Channel_SpaceID", '=', $Space_ID)
                ->where('Channel_Type', '=', $Channel_Format)
                ->get();
        }

        // Return the channellist
        if ($space && $channelList) {
            return response()->json([
                'success' => true,
                'message' => 'Channel list returned',
                'data'    => $channelList,
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Channel list request failed '),
            'data'    => false
        ], 200);
    }

    // Update existing channel in a space
    public function updateChannel()
    {
        $updateFailed = false;
        $errorMsg = "";

        // Setting variables
        $Space_Name = $this->request->Space_Name;
        $Channel_Name = $this->request->Channel_Name;
        $Old_Channel_Name = $this->request->Old_Channel_Name;

        // If credentials are empty
        if (empty($Channel_Name) || empty($Old_Channel_Name)) {
            $updateFailed = true;
            $errorMsg = "Missing neccesary credentials.";
        }

        // Check that Channel_Name is not occupied
        $nameOccupied = Channel::where("Channel_Name", $Channel_Name)->first();
        if ($nameOccupied) {
            $updateFailed = true;
            $errorMsg = "The channel name is already taken.";
        }

        $space = Space::select('Space_ID')->where("Space_Name", $Space_Name)->first();
        
        // If space exists, and there was no errors, save changes to channel
        if ($space && !$updateFailed) {
            $Space_ID = $space->Space_ID;
            $channel = Channel::where('Channel_Name', $Old_Channel_Name)->where("Channel_SpaceID", $Space_ID)->update(['Channel_Name' => $Channel_Name]);
        }

        // Send successfull response
        if (!$updateFailed && $channel) {
            return response()->json([
                'success' => true,
                'message' => 'The channel was updated',
                'data'    => [
                    'Channel_Name' => $Channel_Name,
                    'Channel' => $channel
                ]
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Channel Update Failed '),
            'data'    => false
        ], 200);
    }
}
?>