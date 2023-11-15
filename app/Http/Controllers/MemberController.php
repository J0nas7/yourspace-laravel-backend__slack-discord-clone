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

class MemberController extends Controller
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

    // Create a new membership of a space
    public function createMember()
    {
        $errorMsg = "";

        $Space_Name = $this->request->Space_Name;
        $space = Space::select("Space_ID")->where("Space_Name", $Space_Name)->first();
        $profile = Auth::user();
        $Member_Role = "GUEST";
        $Member_ProfileID = $profile ? $profile->Profile_ID : 0;
        $Member_SpaceID = $space ? $space->Space_ID : 0;

        $alreadyMember = Member::select("Member_SpaceID")->where("Member_ProfileID", $Member_ProfileID)->where("Member_SpaceID", $Member_SpaceID)->first();
        if (!$errorMsg && $alreadyMember) {
            $errorMsg = "You are already a member of this space.";
        }

        // There was no errors, create membership
        if (!$errorMsg) {
            $member = Member::create([
                'Member_Role' => $Member_Role,
                'Member_ProfileID' => $Member_ProfileID,
                'Member_SpaceID' => $Member_SpaceID
            ]);
        }

        // Send successfull response
        if (!$errorMsg && $member) {
            return response()->json([
                'success' => true,
                'message' => 'The membership was created',
                'data'    => $member
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Become a member request failed '),
            'data'    => false
        ], 200);
    }

    // Change a membership role
    public function updateMember()
    {
        $errorMsg = "";

        $Space_Name = $this->request->Space_Name;
        $Profile_ID = $this->request->Profile_ID;
        $New_Role = $this->request->New_Role;
        $profile = Auth::user();
        $space = Space::select(array("Space_ID", "Space_ProfileID"))->where("Space_Name", $Space_Name)->first();
        $Member_SpaceID = $space->Space_ID;

        // Check that auth is the space owner
        if (!$errorMsg && $space->Space_ProfileID !== $profile->Profile_ID) {
            $errorMsg = "You should be owner of this space to do this.";
        }

        // Check that membership exists
        $membership = Member::where('Member_ProfileID', $Profile_ID)->where('Member_SpaceID', $Member_SpaceID)->first();
        if (!$errorMsg && !$membership) {
            $errorMsg = "Membership does not exist";
        }

        // There was no errors, save the changes
        if (!$errorMsg) {
            $new_changes = array();
            if ($New_Role) $new_changes['Member_Role'] = $New_Role;

            if (count($new_changes)) {
                $member_changes = Member::where('Member_ProfileID', $Profile_ID)
                                        ->where('Member_SpaceID', $space->Space_ID)
                                        ->update($new_changes);
                $return_member = Member::where('Member_ProfileID', $Profile_ID)
                                        ->where('Member_SpaceID', $space->Space_ID)
                                        ->first();
            }
        }

        // Send successfull response
        if (!$errorMsg && $member_changes) {
            return response()->json([
                'success' => true,
                'message' => 'The changes was saved',
                'data'    => $return_member
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Space Role Updating Failed '),
            'data'    => false
        ], 200);
    }

    // Delete a membership from a space
    public function deleteMember()
    {
        $errorMsg = "";

        $Space_Name = $this->request->Space_Name;
        $Profile_ID = $this->request->Profile_ID;
        $space = Space::select(array("Space_ID", "Space_ProfileID"))->where("Space_Name", $Space_Name)->first();
        $Member_SpaceID = $space->Space_ID;
        $profile = Auth::user();
        $Profile_ID = $Profile_ID ? $Profile_ID : $profile->Profile_ID;

        // Check that membership exists
        $membership = Member::where('Member_ProfileID', $Profile_ID)->where('Member_SpaceID', $Member_SpaceID)->first();
        if (!$errorMsg && !$membership) {
            $errorMsg = "Membership does not exist";
        }

        // Check that the member is deleting itself, or is it a space admin
        $adminMember = Member::select("Member_Role")->where("Member_ProfileID", $profile->Profile_ID)->where("Member_SpaceID", $Member_SpaceID)->first();
        if (
            !$errorMsg && 
            $Profile_ID !== $profile->Profile_ID &&
            $adminMember->Member_Role !== "ADMIN" &&
            $space->Space_ProfileID !== $profile->Profile_ID
        ) {
            $errorMsg = "You are not allowed to delete this membership.";
        }

        // There was no errors, delete the membership
        if (!$errorMsg) {
            $membership = Member::where("Member_ProfileID", $Profile_ID)->where("Member_SpaceID", $Member_SpaceID)->first();
            $membership->delete();
        }

        // Send successfull response
        if (!$errorMsg && $membership) {
            return response()->json([
                'success' => true,
                'message' => 'The membership was deleted',
                'data'    => $membership
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Deleting a member failed '),
            'data'    => false
        ], 200);
    }
}
