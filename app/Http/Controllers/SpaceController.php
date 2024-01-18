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

    // Create a new space
    public function createSpace()
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

        // There was no errors, create ownership
        if (!$createFailed && $space) {
            $owner = Member::create([
                'Member_Role' => "OWNER",
                'Member_ProfileID' => Auth::user()->Profile_ID,
                'Member_SpaceID' => $space->Space_ID
            ]);
        }

        // Send successfull response
        if (!$createFailed && $space && $owner) {
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

    // Read specific space from the unique space name
    public function readSpace()
    {
        // Setting variables
        $Space_Name = $this->request->Space_Name;
        $space = Space::select(array('Space_ID', 'Space_Name'))->where("Space_Name", $Space_Name)->first();

        // If space exists, return the space
        if ($space) {
            $profile = Auth::user();
            $Member_ProfileID = $profile->Profile_ID;
            $Member_SpaceID = $space->Space_ID;
            $alreadyMember = Member::select("Member_SpaceID")->where("Member_ProfileID", $Member_ProfileID)->where("Member_SpaceID", $Member_SpaceID)->first();

            return response()->json([
                'success' => true,
                'message' => 'The space returned',
                'data'    => $space,
                'alreadyMember' => ($alreadyMember ? true : false),
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Read space request failed '),
            'data'    => false
        ], 200);
    }

    // Read highlighted spaces list
    public function readHighlightedSpacesList()
    {
        $errorMsg = "";

        // Grab the spaces list
        $spacesList = Space::select(array('Space_ID', 'Space_Name'))->get();

        // Return the spaces list
        if (!$errorMsg && $spacesList) {
            return response()->json([
                'success' => true,
                'message' => 'Highlighted spaces list returned',
                'data'    => $spacesList
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Highlighted spaces list request failed '),
            'data'    => false
        ], 200);
    }

    // Read member of spaces list
    public function readMemberOfSpacesList()
    {
        $errorMsg = "";

        // Setting variables
        $Profile_ID = $this->request->Profile_ID ?? '';

        // Grab the specific user or self
        if ($Profile_ID) {
            $user = User::where('Profile_ID', $Profile_ID)->first();
        } else {
            $user = Auth::user();
        }

        // Grab the spaces list
        $memberOfSpacesList = Member::select("Member_SpaceID")->where("Member_ProfileID", $user->Profile_ID)->get();
        $spacesList = array();
        if ($memberOfSpacesList) {
            foreach ($memberOfSpacesList as $memberOfSpace) {
                $space = Space::select(array('Space_ID', 'Space_Name'))
                    ->where("Space_ID", $memberOfSpace->Member_SpaceID)
                    ->first();

                if ($space) {
                    $spacesList[] = $space;
                }
            }
        } else {
            $errorMsg = "NotMemberOfSpace";
        }

        // Return the spaces list
        if (!$errorMsg && $spacesList) {
            return response()->json([
                'success' => true,
                'message' => 'Member of spaces list returned',
                'data'    => $spacesList
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Member of spaces list request failed '),
            'data'    => false
        ], 200);
    }

    // Read members of space list
    public function readMembersOfSpaceList()
    {
        $errorMsg = "";

        // Grab the members list
        $Space_Name = $this->request->Space_Name;
        $space = Space::select(array('Space_ID', 'Space_Name'))->where("Space_Name", $Space_Name)->first();
        $membersOfSpaceList = ($space ? Member::select("Member_ProfileID")->where("Member_SpaceID", $space->Space_ID)->get() : false);
        $membersList = array();

        // Grab the members details
        if ($membersOfSpaceList) {
            foreach ($membersOfSpaceList as $memberOfSpace) {
                $profile = User::select(array('Profile_ID', 'Profile_DisplayName', 'Profile_ImageUrl'))->where('Profile_ID', $memberOfSpace->Member_ProfileID)->first();
                $role = Member::select("Member_Role")->where("Member_ProfileID", $profile->Profile_ID)->where("Member_SpaceID", $space->Space_ID)->first();
                $membersList[] = array(
                    "Profile_ID" => $profile->Profile_ID,
                    "Profile_DisplayName" => $profile->Profile_DisplayName,
                    "Profile_ImageUrl" => $profile->Profile_ImageUrl,
                    "Member_Role" => $role->Member_Role
                );
            }
        } else {
            $errorMsg = "NoMembersOfSpace";
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

    // Update an existing space
    public function updateSpace()
    {
        $updateFailed = false;
        $errorMsg = "";
        $new_changes = array();

        if (isset($this->request->Space_Name_old)) $Space_Name_old = $this->request->Space_Name_old;
        if (isset($this->request->Space_Name_new)) $Space_Name_new = $this->request->Space_Name_new;
        if (isset($this->request->Space_ImageUrl)) $Space_ImageUrl = $this->request->Space_ImageUrl;

        // Check that Space_Name is filled
        if (empty($Space_Name_old)) {
            $updateFailed = true;
            $errorMsg = "Missing space name";
        }

        // Check that new Space_Name is not occupied
        $nameOccupied = Space::where("Space_Name", $Space_Name_new)->first();
        if ($nameOccupied && $Space_Name_new) {
            $updateFailed = true;
            $errorMsg = "The new space name is already taken.";
        }

        // There was no errors, save the changes
        if (!$updateFailed) {
            if ($Space_Name_new) $new_changes['Space_Name'] = $Space_Name_new;

            if (count($new_changes)) {
                $current_space = Space::select("Space_ID")->where("Space_Name", $Space_Name_old)->first();
                $space_changes = Space::where('Space_ID', $current_space->Space_ID)->update($new_changes);
                $return_space = Space::where('Space_ID', $current_space->Space_ID)->first();
            }
        }

        // Send successfull response
        if (!$updateFailed && $space_changes) {
            return response()->json([
                'success' => true,
                'message' => 'The changes was saved',
                'data'    => $return_space
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Space Updating Failed '),
            'data'    => false
        ], 200);
    }

    // Delete a space, and its channels and messages
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
        $space = Space::where("Space_Name", $Space_Name)->first();
        if (!$space) {
            $deleteFailed = true;
            $errorMsg = "The space name does not exists.";
        }

        // There was no errors, delete the space and its channels
        if (!$deleteFailed) {
            $deleteChannels = Channel::where("Channel_SpaceID", $space->Space_ID)->delete();
            $deleteSpace = $space->delete();
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
}
