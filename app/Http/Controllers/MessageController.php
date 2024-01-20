<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use App\Helpers\DataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use DateTime;

class MessageController extends Controller
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

    // Insert new message
    public function createMessage()
    {
        $Message_Content = $this->request->Message_Content;
        $Channel_Name = $this->request->Channel_Name;
        $Space_Name = $this->request->Space_Name;

        $createFailed = false;
        $errorMsg = "";

        // If credentials are empty
        if (empty($Message_Content) || empty($Channel_Name) || empty($Space_Name)) {
            $createFailed = true;
            $errorMsg = "Missing neccesary credentials.";
        }

        // If user details are true
        if (!Auth::user()->Profile_ID) {
            $createFailed = true;
            $errorMsg = "User info not found.";
        }

        // Check that Space_Name and corresponding Channel_Name exists
        $space = Space::where("Space_Name", $Space_Name)->first();
        $channel = Channel::where('Channel_Name', $Channel_Name)->where("Channel_SpaceID", $space->Space_ID)->first();
        if (!$space || !$channel) {
            $createFailed = true;
            $errorMsg = "Could not find the channel or space.";
        }

        // There was no errors, create message
        if (!$createFailed) {
            $newMessage = Message::create([
                'Message_Content' => $Message_Content,
                'Message_FileUrl' => '',
                'Message_MemberID' => Auth::user()->Profile_ID,
                'Message_ChannelID' => $channel->Channel_ID,
            ]);
            $profile = User::where("Profile_ID", $newMessage->Message_MemberID)->first();
            $channel = Channel::select("Channel_Name")->where("Channel_ID", $newMessage->Message_ChannelID)->first();
            $newMessage->Profile_DisplayName = $profile->Profile_DisplayName;
            $newMessage->Profile_ImageUrl = $profile->Profile_ImageUrl;
            $newMessage->Channel_Name = $channel->Channel_Name;
        }

        // Send successfull response
        if (!$createFailed && $newMessage) {
            return response()->json([
                'success' => true,
                'message' => 'The message was created',
                'data'    => $newMessage
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Message Creation Failed '),
            'data'    => false
        ], 200);
    }

    // Get previous 25 messages
    public function read25Messages()
    {
        $selectFailed = false;
        $errorMsg = "";

        // Setting variables
        $Space_Name = $this->request->Space_Name;
        $Channel_Name = $this->request->Channel_Name;

        // Check that Space_Name and corresponding Channel_Name exists
        $space = Space::where("Space_Name", $Space_Name)->first();
        $channel = ($space ? Channel::where('Channel_Name', $Channel_Name)->where("Channel_SpaceID", $space->Space_ID)->first() : false);
        if (!$space || !$channel) {
            $selectFailed = true;
            $errorMsg = "Could not find the channel or space.";
        }

        // There was no errors, create message.
        if (!$selectFailed) {
            $Channel_ID = $channel->Channel_ID;

            // DB get last 25 messages
            /*$messages = Message:://select(array('Message_ID', 'Message_Content', 'Message_CreatedAt', 'Message_FileUrl', 'Profile_DisplayName', 'Profile_ImageUrl'))
                where("Message_ChannelID", $Channel_ID)->where('deleted', 0)
                ->join('Profile', 'Profile.Profile_ID', '=', 'Message.Message_MemberID')
                ->orderBy('Message_ID', 'ASC')->latest()->take(10)->get();*/
            $theMessages = array();
            $readMessages = Message::where("Message_ChannelID", $Channel_ID)->where('deleted', 0)
                //->join('Profile', 'Profile.Profile_ID', '=', 'Message.Message_MemberID')
                ->latest()->take(25)->get()->sortBy('Message_ID');
            foreach ($readMessages as $message) {
                $profile = User::where("Profile_ID", $message->Message_MemberID)->first();
                $message->Profile_DisplayName = $profile->Profile_DisplayName;
                $message->Profile_ImageUrl = $profile->Profile_ImageUrl;
                $theMessages[] = $message;
            }
        }

        // Return the messages
        if (!$selectFailed && $theMessages) {
            return response()->json([
                'success' => true,
                'message' => 'Messages returned',
                'data'    => $theMessages
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Messages request failed '),
            'data'    => false
        ], 200);
    }

    // Update existing message
    public function updateExistingMessage()
    {
        $deleteFailed = false;
        $errorMsg = "";

        // Setting variables
        $Message_ID = $this->request->Message_ID;
        $New_Content = $this->request->New_Content;

        // If the message ID or new message  is empty
        if (!$Message_ID) { $errorMsg = "The original message is invalid."; }
        if (empty($New_Content)) { $errorMsg = "The message cannot be empty."; }

        // There was no errors, save the changes
        if (!$errorMsg) {
            $new_changes = array();
            $new_changes['Message_Content'] = $New_Content;

            if (count($new_changes)) {
                $message_changes = Message::where('Message_ID', $Message_ID)->update($new_changes);
                $return_message = Message::where('Message_ID', $Message_ID)->first();
            }
        }

        // Send successfull response
        if (!$errorMsg && $message_changes) {
            return response()->json([
                'success' => true,
                'message' => 'The changes was saved',
                'data'    => $return_message
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Message Updating Failed '),
            'data'    => false
        ], 200);
    }

    // Delete message
    public function deleteMessage()
    {
        $deleteFailed = false;
        $errorMsg = "";

        // Setting variables
        $Message_ID = $this->request->Message_ID;

        // There was no errors, delete the message
        if (!$errorMsg) {
            $message = Message::where("Message_ID", $Message_ID)->first();
            $message->delete();
        }

        // Send successfull response
        if (!$errorMsg && $message) {
            return response()->json([
                'success' => true,
                'message' => 'The message was deleted',
                'data'    => $message
            ], 200);
        }

        // Send failed response
        return response()->json([
            'success' => false,
            'message' => (!empty($errorMsg) ? $errorMsg : 'Deleting a message failed '),
            'data'    => false
        ], 200);
    }
}
