<?php

namespace App\Http\Controllers;

use App\Document;
use App\FacebookFeed;
use App\InstagramFeed;
use Illuminate\Http\Request;
use Facebook\Facebook;

class DocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $document = new Document();
        $document->name = $input['name'];
        $document->name_thumbnail = $input['name_thumbnail'];
        $document->type = $input['type'];
        $document->path = '';
        $document->path_thumbnail = '';
        $document->size = $input['size'];
        $document->owner_id = $input['owner_id'];
        $document->owner_name = $input['owner_name'];
        $document->group_id = $input['group_id'];
        $document->school_id = $input['school_id'];
        $document->save();

        return ['success' => 1, 'document' => $document];

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $document = Document::where('id', $id)->first();

        if($document)
            return ['success' => 1, 'document' => $document];

        return ['error'=>1, 'errors' => ['Document does not exist']];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function list(Request $request, $school_id)
    {
        if($request->get('role') == 'student')
            $documents = Document::where('school_id', $school_id)
                            ->where('type', 'image')
                            ->where('created_at', '>=', $request->get('created_at'))
                            ->select('id', 'name', 'name_thumbnail', 'group_id', 'owner_id', 'owner_name', 'created_at')
                            ->orderBy('created_at', 'desc')
                            ->get();
        else
            $documents = Document::where('school_id', $school_id)
                            ->where('type', 'image')
                            ->select('id', 'name', 'name_thumbnail', 'group_id', 'owner_id', 'owner_name', 'created_at')
                            ->orderBy('created_at', 'desc')
                            ->get();

        return ['success' => 1, 'documents' => $documents, 'total' => $documents->count()];
    }

    public function myPhotos($user_id)
    {
        $documents = Document::where('owner_id', $user_id)
                        ->where('type', 'image')
                        ->select('id', 'name', 'name_thumbnail', 'group_id', 'owner_id', 'owner_name', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return ['success' => 1, 'documents' => $documents, 'total' => $documents->count()];
    }

    public function documentGroups(Request $request)
    {
        $groups = $request->get('groups');

        $result = [];
        foreach($groups as $key => $value) {
            $docs = Document::whereIn('owner_id', $value)->pluck('id');
            $result[$key] = $docs;
        }

        return ['success' => 1, 'documents' => $result];
    }

    public function documentsDetails(Request $request)
    {
        $document = Document::whereIn('id', $request->get('documents'))->get()->toArray();

        if($document)
            return ['success' => 1, 'documents' => $document];

        return ['error'=>1, 'errors' => ['Document does not exist']];
    }

    public function dashboardDetails($owner_id)
    {
        $count_documents = Document::where('owner_id', $owner_id)->where('type', 'image')->count();

        return['success' => 1, 'count_documents' => $count_documents];
    }

    public function totalDocuments($school_id)
    {
        $count_documents = Document::where('school_id', $school_id)->where('type', 'image')->count();

        return['success' => 1, 'count_documents' => $count_documents];
    }

    public function updateSchool(Request $request)
    {
        $document = Document::where('id', $request->get('doc_id'))->first();
        $document->school_id = $request->get('school_id');
        $document->save();
        return['success' => 1];
    }

    public function updateOwnerName(Request $request)
    {
        Document::where('owner_id', $request->get('owner_id'))
            ->update(['owner_name' => $request->get('owner_name')]);
        
        return['success' => 1];
    }

    public function delete(Request $request)
    {
        Document::whereIn('id', $request->get('ids'))->where('school_id', $request->get('school_id'))->delete();
        return ['success' => 1];
    }

    public function facebookFeed(Request $request)
    {
        $school_id = $request->get('school_id');
        $old_fb_feed = FacebookFeed::where('school_id', $school_id)->pluck('item_id')->toArray();
        $old_insta_feed = InstagramFeed::where('school_id', $school_id)->pluck('item_id')->toArray();
        // return $old_fb_feed;
        $fb = new Facebook([
            'app_id' => env('FB_CLIENT_ID'),
            'app_secret' => env('FB_CLIENT_SECRET'),
            'default_graph_version' => 'v2.2',
        ]);

        try {
            $response = $fb->get(
                '/'.$request->get('fb_page_id').'/photos?type=uploaded',
                $request->get('fb_access_token')
            );
        } catch(FacebookExceptionsFacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookExceptionsFacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $graphNode = $response->getGraphEdge();

        if(count($graphNode) > 0) {
            foreach($graphNode as $node){
                if(!in_array($node['id'], $old_fb_feed)) {
                    try {
                        // Returns a `FacebookFacebookResponse` object
                        $response_image = $fb->get(
                            '/'.$node['id'].'?fields=images,created_time,link',
                            $request->get('fb_access_token')
                        );
                    } catch(FacebookExceptionsFacebookResponseException $e) {
                        echo 'Graph returned an error: ' . $e->getMessage();
                        exit;
                    } catch(FacebookExceptionsFacebookSDKException $e) {
                        echo 'Facebook SDK returned an error: ' . $e->getMessage();
                        exit;
                    }
                    $graphNode_image = $response_image->getGraphNode();
                    
                    if($graphNode_image) {
                        $new_feed = new FacebookFeed();
                        $new_feed->school_id = $school_id;
                        $new_feed->item_id = $node['id'];
                        $new_feed->permalink = $graphNode_image['link'];
                        $new_feed->media_url = $graphNode_image['images'][0]['source'];
                        $new_feed->date_uploaded = $graphNode_image['created_time']->format('Y-m-d H:i:s');
                        $new_feed->save();
                    }
                } else break;
            }
        }

        try {
            // Returns a `FacebookFacebookResponse` object
            $response_instagram = $fb->get(
                '/'.$request->get('fb_page_id').'?fields=instagram_business_account',
                $request->get('fb_access_token')
            );
        } catch(FacebookExceptionsFacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookExceptionsFacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $graphNode_instagram = $response_instagram->getGraphNode();
        
        if(isset($graphNode_instagram['instagram_business_account']['id'])) {
            try {
                // Returns a `FacebookFacebookResponse` object
                $response_insta_profile = $fb->get(
                    '/'.$graphNode_instagram['instagram_business_account']['id'].'/media',
                    $request->get('fb_access_token')
                );
            } catch(FacebookExceptionsFacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(FacebookExceptionsFacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            $graphNode_insta_profile = $response_insta_profile->getGraphEdge();
    
            if(count($graphNode_insta_profile) > 0) {
                foreach($graphNode_insta_profile as $node) {
                    if(!in_array($node['id'], $old_insta_feed)) {
                        try {
                            // Returns a `FacebookFacebookResponse` object
                            $response_insta_photos = $fb->get(
                                '/'.$node['id'].'?fields=permalink,media_url,timestamp',
                                $request->get('fb_access_token')
                            );
                        } catch(FacebookExceptionsFacebookResponseException $e) {
                            echo 'Graph returned an error: ' . $e->getMessage();
                            exit;
                        } catch(FacebookExceptionsFacebookSDKException $e) {
                            echo 'Facebook SDK returned an error: ' . $e->getMessage();
                            exit;
                        }
                        $graphNoderesponse_insta_photos = $response_insta_photos->getGraphNode();
                        
                        if($graphNoderesponse_insta_photos) {
                            $new_insta_feed = new InstagramFeed();
                            $new_insta_feed->school_id = $school_id;
                            $new_insta_feed->item_id = $node['id'];
                            $new_insta_feed->permalink = $graphNoderesponse_insta_photos['permalink'];
                            $new_insta_feed->media_url = $graphNoderesponse_insta_photos['media_url'];
                            $new_insta_feed->date_uploaded = date('Y-m-d H:i:s',  strtotime($graphNoderesponse_insta_photos['timestamp']));
                            $new_insta_feed->save();
                        }
                    } else break;
                }
            }
        }

        return ['success' => 1];
    }

    public function getSocialFeed($id)
    {
        $fb_feed = FacebookFeed::where('school_id', $id)->select('permalink', 'media_url', 'date_uploaded as create_time')->get()->toArray();
        $insta_feed = InstagramFeed::where('school_id', $id)->select('permalink', 'media_url', 'date_uploaded as create_time')->get()->toArray();

        return ['success' => 1, 'fb_feed' => $fb_feed, 'insta_feed' => $insta_feed];

    }
}
