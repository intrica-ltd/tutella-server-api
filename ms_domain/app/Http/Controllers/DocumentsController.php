<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Helpers\CurlHelper;
use Intervention\Image\ImageManagerStatic as ImageIntervention;
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    /**
     * @SWG\Post(path="/documents/upload",
     *   summary="Upload photo",
     *   description="Upload photo",
     *   operationId="uploadPhoto",
     *   produces={"application/json"},
     *   tags={"Documents"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="file",
     *     description="The file to upload",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="group_id",
     *     description="Upload the file to group",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="type",
     *     description="['image', 'user_avatar', 'school_avatar']",
     *     required=false,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' => 1, 'data'=> ['document' => '']]")
    * )
    */
    public function upload(Request $request)
    {

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {

            $file = $request->file('file');

            if(!isset($file))
                return response()->json()->setStatusCode(490, 'empty_input');

            if(!$request->has('type') && !$request->has('group_id'))
                return response()->json()->setStatusCode(484, 'error_invalid_input');

            if($request->has('type') && !in_array($request->get('type'), ['image', 'user_avatar', 'school_avatar']))
                return response()->json()->setStatusCode(493, 'error_invalid_type');

            $file_name = md5($file->getClientOriginalName() . time());
            $full_file_name = $file_name.'.'.$file->getClientOriginalExtension();
            $full_thumbnail_name = $file_name.'_thumb.'.$file->getClientOriginalExtension();

            $input = $request->all();
            $input['name'] = $full_file_name;
            $input['name_thumbnail'] = $full_thumbnail_name;
            $input['school_id'] = $user['user']->school_id;
            $input['owner_id'] = $user['user']->user_id;
            $input['owner_name'] = $user['user']->first_name . ' ' . $user['user']->last_name;
            if($request->has('group_id')) {
                $input['group_id'] = $request->get('group_id');
                $input['type'] = 'image';
            } else {
                $input['group_id'] = '';
                $input['type'] = $request->get('type');
            }
            $input['size'] = 0.00;// round($request->file('file')->size, 2);

            $documents_url      = env('DOCUMENTS_URL_API').'documents/store';
            $response           = CurlHelper::curlPost($documents_url, $input);
            
            if(isset($response->success)){
                $file->move(env('FILE_STORAGE'), $full_file_name);

                $image = ImageIntervention::make(env('FILE_STORAGE') . '/' . $full_file_name)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
//                if($request->get('type') == 'user_avatar') {
                    $image->orientate();
  //              }
                    
                $image->save(env('FILE_STORAGE') . '/thumbnails/' . $full_thumbnail_name);

                if($request->get('type') == 'user_avatar') {
                    $changeAvatar = User::where('user_id', $user['user']->user_id)->first();
                    $changeAvatar->image = $full_file_name;
                    $changeAvatar->image_id = $response->document->id;
                    $changeAvatar->save();
                }

                if($request->get('type') == 'school_avatar') {
                    $input_school['logo'] = $response->document->name;
                    $input_school['logo_id'] = $response->document->id;

                    $url = ENV('SCHOOLS_URL_API') . 'schools/' . $user['user']->school_id . '/changeLogo';
                    $response_school = CurlHelper::curlPost($url, $input_school);
                }

                return response()->json(['success' =>1, 'data'=> ['document' => $response->document]])->setStatusCode(200, 'success_document_uploaded');
        
            }
            return response()->json()->setStatusCode(492, 'error_document_upload');

        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }


    /**
     * @SWG\Get(path="/documents/list",
     *   summary="Show all documents for the school.",
     *   description="Show all documents for the school.",
     *   operationId="shwoDocumentsTable",
     *   produces={"application/json"},
     *   tags={"Documents"},
     *   @SWG\Response(response="200", description="['documents' => [], 'total' => total]")
     * )
    */
    public function list(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        $input = [];
        
        if(isset($user['success'])) {
            $input['role'] = $user['user']->role;
            $input['created_at'] = $user['user']->created_at;

            $documents_url      = env('DOCUMENTS_URL_API').'documents/'.$user['user']->school_id.'/list';
            $response           = CurlHelper::curlGet($documents_url, $input);
            
            if(isset($response->success)) {
                return response()->json(['documents' => $response->documents, 'total' => $response->total])->setStatusCode(200, 'success');
            }
            
            return response()->json()->setStatusCode(480, 'error_fetching_data');
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }

    /**
     * @SWG\Get(path="/documents/myPhotos",
     *   summary="Show all documents for the leader/student.",
     *   description="Show all documents for the leader/student.",
     *   operationId="shwoDocumentsTableForUser",
     *   produces={"application/json"},
     *   tags={"Documents"},
     *   @SWG\Response(response="200", description="['documents' => [], 'total' => total]")
     * )
    */
    public function myPhotos(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $documents_url      = env('DOCUMENTS_URL_API').'documents/'.$user['user']->user_id.'/myPhotos';
            $response           = CurlHelper::curlGet($documents_url);

            if(isset($response->success)) {
                return response()->json(['documents' => $response->documents, 'total' => $response->total])->setStatusCode(200, 'success');
            }
            
            return response()->json()->setStatusCode(480, 'error_fetching_data');
        }
    }

    /**
     * @SWG\Get(path="/documents/filter",
     *   summary="Get all groups and the documents for that group.",
     *   description="Get all groups and the documents for that group.",
     *   operationId="filterDocuments",
     *   produces={"application/json"},
     *   tags={"Documents"},
     *   @SWG\Response(response="200", description="['group_id' => [list_of_doc_ids]]")
     * )
    */
    public function filter(Request $request)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            if($user['user']->school_id == null) {
                return response()->json([])->setStatusCode(200, 'success');
            }

            $groups_url     = env('GROUPS_URL_API').'groups/'.$user['user']->school_id.'/groupUsers';
            $response       = CurlHelper::curlGet($groups_url);
            
            if($response->success) {
                if(count($response->data) > 0) {
                   $groups_url     = env('DOCUMENTS_URL_API').'documents/documentGroups';
                    $response_doc   = CurlHelper::curlPost($groups_url, ['groups' => $response->data]);
                    
                    if(isset($response_doc->success)) {
                        return response()->json($response_doc->documents)->setStatusCode(200, 'success');
                    }
                }
                return response()->json([])->setStatusCode(200, 'success');
            }
        }

        return response()->json()->setStatusCode(480, 'error_fetching_data');
    }

    /**
     * @SWG\Post(path="/documents/download",
     *   summary="Download one or multiple photos.",
     *   description="Download one or multiple photos.",
     *   operationId="downloadPhoto",
     *   produces={"application/json"},
     *   tags={"Documents"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="document",
     *     description="Document id or array of document ids",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' => 1, 'data'=> ['document' => '']]")
    * )
    */
    public function download(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'document' => 'required'
        ]);          
        
        if ($validator->fails()) {
            $fail =  ['error'=>1, 'errors' => $validator->errors()->all()];
            return response()->json($fail)->setStatusCode(465, 'validation_error');
        }

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            if(is_array($request->get('document'))) {
                $groups_url     = env('DOCUMENTS_URL_API').'documents/documentsDetails';
                $response_doc   = CurlHelper::curlPost($groups_url, ['documents' => $request->get('document')]);

                if(isset($response_doc->success)){
                    if(isset($response_doc->documents[0]) && $response_doc->documents[0]->type == 'poster') {
                        return response()->download(env('FILE_STORAGE').'/'.$response_doc->documents[0]->name)->setStatusCode(200, 'success');
                    } else {
                        $zip_name = env('FILE_STORAGE').'/zip/photos_'.time().'.zip';
                        $zip = new \ZipArchive;
                        $zip->open($zip_name, \ZipArchive::CREATE);
                        foreach($response_doc->documents as $document) {
                            if($document->school_id == $user['user']->school_id)
                                $zip->addFile(env('FILE_STORAGE').'/'.$document->name, $document->name);
                            
                        }
                        $zip->close();
                        return response()->download($zip_name)->setStatusCode(200, 'success');
                    }
                }
            } else {
                $groups_url     = env('DOCUMENTS_URL_API').'documents/'.$request->get('document');
                $response_doc   = CurlHelper::curlGet($groups_url);

                if(isset($response_doc->success) && $response_doc->document->school_id == $user['user']->school_id) {
                    return response()->download(env('FILE_STORAGE').'/'.$response_doc->document->name)->setStatusCode(200, 'success');
                }
                
                return response()->json()->setStatusCode(495, 'error_document_does_not_exist');
            }
        }

        return response()->json()->setStatusCode(480, 'error_fetching_data');
    }

    public function getPhoto(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $groups_url     = env('DOCUMENTS_URL_API').'documents/'.$id;
            $response_doc   = CurlHelper::curlGet($groups_url);

            if(isset($response_doc->success) && ($user['user']->role == 'super_admin' || $response_doc->document->school_id == $user['user']->school_id)) {
                if(file_exists(env('FILE_STORAGE').'/'.$response_doc->document->name)) {

                    return response()->download(env('FILE_STORAGE').'/'.$response_doc->document->name, null, [], null)->setStatusCode(200, 'success');
                }
                return response()->json()->setStatusCode(495, 'error_photo_does_not_exist');
            }
        }

        return response()->json()->setStatusCode(480, 'error_fetching_data');
    }

    public function getThumbnailPhoto(Request $request, $id)
    {
        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));

        if(isset($user['success'])) {
            $groups_url     = env('DOCUMENTS_URL_API').'documents/'.$id;
            $response_doc   = CurlHelper::curlGet($groups_url);

            if(isset($response_doc->success) && ($user['user']->role == 'super_admin' || $response_doc->document->school_id == $user['user']->school_id)) {
                if(file_exists(env('FILE_STORAGE').'/thumbnails/'.$response_doc->document->name_thumbnail)) {

                    return response()->download(env('FILE_STORAGE').'/thumbnails/'.$response_doc->document->name_thumbnail, null, [], null)->setStatusCode(200, 'success');
                }
                return response()->json()->setStatusCode(495, 'error_photo_does_not_exist');
            }
        }

        return response()->json()->setStatusCode(480, 'error_fetching_data');
    }

    /**
     * @SWG\Get(path="/documents/profilePhoto/{id}",
     *   summary="Get user's profile photo.",
     *   description="Get user's profile photo.",
     *   operationId="profilePhoto",
     *   produces={"application/json"},
     *   tags={"Documents"},
     *   @SWG\Response(response="200", description="the photo")
     * )
    */
    public function profilePhoto(Request $request, $id) {
        $check = User::checkIfUserExsist($id);
        if(isset($check['success'])) {
            $user = $check['user'];

            if($user->image_id == null && $user->image != '')
                return response()->json(['url' => $user->image])->setStatusCode(200, 'success');

            if($user->image_id) {
                $groups_url     = env('DOCUMENTS_URL_API').'documents/'.$user->image_id;
                $response_doc   = CurlHelper::curlGet($groups_url);

                if(isset($response_doc->success)) {
                    if(file_exists(env('FILE_STORAGE').'/thumbnails/'.$response_doc->document->name_thumbnail)) {
                        return response()->download(env('FILE_STORAGE').'/thumbnails/'.$response_doc->document->name_thumbnail, null, [], null)->setStatusCode(200, 'success');
                    }
                    return response()->json()->setStatusCode(495, 'error_photo_does_not_exist');
                }
            }
        }

        return response()->download(env('FILE_STORAGE').'/avatar.png', null, [], null)->setStatusCode(200, 'success');
    }

    /**
     * @SWG\Post(path="/documents/delete",
     *   summary="Delete one or multiple photos.",
     *   description="Delete one or multiple photos.",
     *   operationId="DeletePhoto",
     *   produces={"application/json"},
     *   tags={"Documents"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="documents",
     *     description="Array of document ids",
     *     required=true,
     *     @SWG\Schema(ref="#")
     *   ),
    *   @SWG\Response(response="200", description="['success' => 1]]")
    * )
    */
    public function delete(Request $request)
    {
        if(!is_array($request->get('documents')))
            return response()->json()->setStatusCode(484, 'error_invalid_input');

        if(count($request->get('documents')) == 0)
            return response()->json()->setStatusCode(200, 'success');

        $split_token  = explode(' ', $request->header('authorization'));
        $user = User::authUser(end($split_token));
        if(isset($user['success'])) {
            $documents_url   = env('DOCUMENTS_URL_API').'documents/delete';
            $response_doc   = CurlHelper::curlPost($documents_url, ['school_id' => $user['user']->school_id, 'ids' => $request->get('documents')]);

            if(isset($response_doc->success))
                return response()->json(['deleted' => $request->get('documents')])->setStatusCode(200, 'success');

            return response()->json()->setStatusCode(498, 'error_document_delete');            
        }
        return response()->json()->setStatusCode(463, 'error_user_not_found');
    }
}
