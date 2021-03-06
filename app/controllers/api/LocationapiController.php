<?php
namespace Api;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;

class LocationapiController extends \BaseController {

    public $controller_name = '';

    public function  __construct()
    {
        //$this->model = "Member";
        $this->controller_name = strtolower( str_replace('Controller', '', get_class()) );

    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $key = Input::get('key');

        $user = \Apiauth::user($key);

		//
        $locations = \Assetlocation::get();
        for($i = 0; $i < count($locations);$i++){

                $locations[$i]->extId = $locations[$i]->_id;

                unset($locations[$i]->_id);
                unset($locations[$i]->_token);

                unset($locations[$i]->thumbnail_url);
                unset($locations[$i]->large_url);
                unset($locations[$i]->medium_url);
                unset($locations[$i]->full_url);
                unset($locations[$i]->delete_type);
                unset($locations[$i]->delete_url);
                unset($locations[$i]->filename);
                unset($locations[$i]->filesize);
                unset($locations[$i]->temp_dir);
                unset($locations[$i]->filetype);
                unset($locations[$i]->is_image);
                unset($locations[$i]->is_audio);
                unset($locations[$i]->is_video);
                unset($locations[$i]->fileurl);
                unset($locations[$i]->file_id);
                unset($locations[$i]->caption);
                unset($locations[$i]->files);
                unset($locations[$i]->medium_portrait_url);

                if(isset($locations[$i]->deleted)){

                }else{
                    $locations[$i]->deleted = 0;
                }


               $pics = \Uploaded::where('parent_id', $locations[$i]->extId)
                                    ->where('parent_class','location')
                                    ->orderBy('createdDate','desc')
                                    ->get();

                if( count( $pics->toArray() ) > 0 ){
                    //$dp = $assets[$i]->defaultpictures;

                    $dp = $pics->toArray();
                    $dp = $dp[0];

                    if(isset($dp['delete_type'])){
                        unset($dp['delete_type']);
                    }
                    if(isset($dp['delete_url'])){
                        unset($dp['delete_url']);
                    }
                    if(isset($dp['temp_dir'])){
                        unset($dp['temp_dir']);
                    }

                    /*
                    if(is_array($dp)){
                        foreach($dp as $k=>$v){
                            $name = 'picture'.str_replace(' ', '', ucwords( str_replace('_', ' ', $k) ));
                            $assets[$i]->{$name} = $v;
                        }
                    }
                    */

                    $locations[$i]->pictureThumbnailUrl = $dp['thumbnail_url'];
                    $locations[$i]->pictureLargeUrl = $dp['large_url'];
                    $locations[$i]->pictureMediumUrl = $dp['medium_url'];
                    $locations[$i]->pictureFullUrl = $dp['full_url'];
                    $locations[$i]->pictureBrchead = $dp['medium_url'];
                    $locations[$i]->pictureBrc1 = $dp['medium_url'];
                    $locations[$i]->pictureBrc2 = $dp['medium_url'];
                    $locations[$i]->pictureBrc3 = $dp['medium_url'];

                }else{
                    $locations[$i]->pictureThumbnailUrl = '';
                    $locations[$i]->pictureLargeUrl = '';
                    $locations[$i]->pictureMediumUrl = '';
                    $locations[$i]->pictureFullUrl = '';
                    $locations[$i]->pictureBrchead = '';
                    $locations[$i]->pictureBrc1 = '';
                    $locations[$i]->pictureBrc2 = '';
                    $locations[$i]->pictureBrc3 = '';
                }

                $locations[$i]->createdDate = date('Y-m-d H:i:s',$locations[$i]->createdDate->sec);
                $locations[$i]->lastUpdate = date('Y-m-d H:i:s',$locations[$i]->lastUpdate->sec);

                    foreach($locations[$i]->toArray() as $k=>$v){
                        if(is_null($v)){
                            $locations[$i]->{$k} = '';
                        }

                        if(is_array($v)){
                            unset($locations[$i]->{$k});
                        }
                    }
        }

        $actor = $user->fullname.' : '.$user->email;
        \Event::fire('log.api',array($this->controller_name, 'get' ,$actor,'location list'));

        return $locations;
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
