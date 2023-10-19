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

        // Check that Space_ImageUrl is a valid URL.
        if (!in_array($Channel_Format, $Channel_Format_Array)) {
            $createFailed = true;
            $errorMsg = "Invalid channel format.";
        }

        // There was no errors, create space
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