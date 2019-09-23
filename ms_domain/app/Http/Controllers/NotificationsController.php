<?php

/*
 * Copyright 2018 SCTR Services
 *
 * Distribution and reproduction are prohibited.
 *
 * @package     tutella-api
 * @copyright   SCTR Services LLC 2018
 * @license     No License (Proprietary)
 */

namespace App\Http\Controllers;

use App\Helpers\CurlHelper;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    /** @var string */
    private $notificationsUrl;

    public function __construct()
    {
        $this->notificationsUrl = env('NOTIFICATIONS_URL');
    }

    /**
     * @SWG\Get( path="/notifications",
     *   summary="Get all notifications.",
     *   description="Get all notifications from notifications micro service.",
     *   operationId="getNotifications",
     *   produces={"application/json"},
     *   tags={"notifications"},
     *   @SWG\Response(
     *     response="200"
     *   )
     * )
     */
    public function index(Request $req)
    {
        $split_token = explode(' ', $req->header('authorization'));
        $user = User::authUser(end($split_token));

        if (isset($user['success'])) {
            $response = CurlHelper::curlPost($this->notificationsUrl . 'notifications', ['school_id' => $user['user']->school_id]);

            return response()->json($response, 200);
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get( path="/notifications/markAsRead",
     *   summary="Mark notifications as read.",
     *   description="Mark notifications as read on notifications micro service.",
     *   operationId="markNotificationsAsRead",
     *   produces={"application/json"},
     *   tags={"notifications"},
     *   @SWG\Response(
     *     response="200"
     *   )
     * )
     */
    public function markAsRead(Request $req)
    {
        $split_token = explode(' ', $req->header('authorization'));
        $user = User::authUser(end($split_token));

        if (isset($user['success'])) {
            $response = CurlHelper::curlPost($this->notificationsUrl . 'notifications/markAsRead', ['school_id' => $user['user']->school_id]);

            return response()->json($response, 200);
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    public function sendAnnouncement(Request $req)
    {
        $split_token = explode(' ', $req->header('authorization'));
        $user = User::authUser(end($split_token));

        if (isset($user['success'])) {
            $input = $req->all();
            $date = date('Y-m-d H:i');
            $sendInput['msg'] = $input['message'];

            $users = User::whereIn('user_id', $input['users'])->select('id', 'firebase_token')->get();

            foreach ($users as $user) {
                $user->count = 1;
            }

            $sendInput = ['msg' => $req->get('message'), 'users' => $users];

            $notifications_url = env('NOTIFICATIONS_URL') . 'announcements/sendAnnouncement';

            $response = CurlHelper::curlPost($notifications_url, $sendInput, true);

            return response()->json([$response], 200);
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

}
