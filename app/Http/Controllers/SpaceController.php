<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Channel;
use App\Helpers\DataService;
use App\Models\Member;
use App\Models\User;
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
        $errorMsg = "";

        // Grab the spaces list
        $user = Auth::user();
        $memberOfSpacesList = Member::select("Member_SpaceID")->where("Member_ProfileID", $user->Profile_ID)->get();
        $spacesList = array();
        if ($memberOfSpacesList) {
            foreach ($memberOfSpacesList as $memberSpace) {
                $spacesList[] = Space::select(array('Space_ID', 'Space_Name'))
                    ->where("Space_ID", $memberSpace->Member_SpaceID)
                    ->first();
            }
        } else {
            $errorMsg = "NotAnyMember";
        }

        // Return the spaces list
        if (!$errorMsg && $spacesList) {
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

    // Get members of space list
    public function getMembersOfSpaceList()
    {
        $errorMsg = "";

        // Grab the members list
        $Space_Name = $this->request->Space_Name;
        $space = Space::select(array('Space_ID', 'Space_Name'))->where("Space_Name", $Space_Name)->first();
        $membersOfSpaceList = Member::select("Member_ProfileID")->where("Member_SpaceID", $space->Space_ID)->get();
        $membersList = array();

        // Grab the members details
        if ($membersOfSpaceList) {
            foreach ($membersOfSpaceList AS $memberOfSpace) {
                $profile = User::select(array('Profile_ID', 'Profile_DisplayName', 'Profile_ImageUrl'))->where('Profile_ID', $memberOfSpace->Member_ProfileID)->first();
                $membersList[] = array(
                    "Profile_ID" => $profile->Profile_ID,
                    "Profile_DisplayName" => $profile->Profile_DisplayName,
                    "Profile_ImageUrl" => $profile->Profile_ImageUrl,
                );
            }
        } else {
            $errorMsg = "NotAnyMembers";
        }

        // Return the members of space list
        if (!$errorMsg && $membersList) {
            return response()->json([
                'success' => true,
                'message' => 'Members of space list returned',
                'data'    => $membersList
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Members of space list request failed '),
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
            $channelList = Channel::select(array('Channel_ID', 'Channel_Name'))
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

    // Delete a space
    public function deleteSpace()
    {
        $deleteFailed = false;
        $errorMsg = "";
        $Space_Name = $this->request->Space_Name;

        // Check that Space_Name is filled
        if (empty($Space_Name)) {
            $deleteFailed = true;
            $errorMsg = "Missing space name";
        }

        // Check that the Space_Name exists
        $nameExists = Space::where("Space_Name", $Space_Name)->first();
        if (!$nameExists) {
            $deleteFailed = true;
            $errorMsg = "The space name does not exists.";
        }

        // There was no errors, delete the space
        if (!$deleteFailed) {
            $deleteChannels = Channel::where("Channel_SpaceID", $nameExists->Space_ID)->delete();
            $deleteSpace = $nameExists->delete();
        }

        // Send succesful response
        if (!$deleteFailed && $deleteChannels && $deleteSpace) {
            return response()->json([
                'success' => true,
                'message' => 'The space was deleted',
                'deleteChannels'    => $deleteChannels,
                'deleteSpace'    => $deleteSpace,
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Space Deleting Failed '),
            'data'    => false
        ], 200);
    }

    // Create a new space
    public function editSpace()
    {
        $edit_failed = false;
        $errorMsg = "";
        $new_changes = array();

        if (isset($this->request->Space_Name_old)) $Space_Name_old = $this->request->Space_Name_old;
        if (isset($this->request->Space_Name_new)) $Space_Name_new = $this->request->Space_Name_new;
        if (isset($this->request->Space_ImageUrl)) $Space_ImageUrl = $this->request->Space_ImageUrl;

        // Check that Space_Name is filled
        if (empty($Space_Name_old)) {
            $edit_failed = true;
            $errorMsg = "Missing space name";
        }

        // Check that new Space_Name is not occupied
        $nameOccupied = Space::where("Space_Name", $Space_Name_new)->first();
        if ($nameOccupied && $Space_Name_new) {
            $edit_failed = true;
            $errorMsg = "The new space name is already taken.";
        }

        // There was no errors, save the changes
        if (!$edit_failed) {
            if ($Space_Name_new) $new_changes['Space_Name'] = $Space_Name_new;

            if (count($new_changes)) {
                $current_space = Space::select("Space_ID")->where("Space_Name", $Space_Name_old)->first();
                $space_changes = Space::where('Space_ID', $current_space->Space_ID)->update($new_changes);
                $return_space = Space::where('Space_ID', $current_space->Space_ID)->first();
            }
        }

        // Send successfull response
        if (!$edit_failed && $space_changes) {
            return response()->json([
                'success' => true,
                'message' => 'The changes was saved',
                'data'    => $return_space
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Space Editing Failed '),
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

        if (!Auth::user()->Profile_ID) {
            $createFailed = true;
            $errorMsg = "User info not found.";
        }

        // There was no errors, create space
        if (!$createFailed) {
            $space = Space::create([
                'Space_Name' => $Space_Name,
                'Space_ImageUrl' => $this->request->Space_ImageUrl ?? '',
                'Space_InviteCode' => '',
                'Space_ProfileID' => Auth::user()->Profile_ID
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
