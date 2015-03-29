<?php
namespace Api;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;

class RackapiController extends \BaseController {

    public $controller_name = '';

    public $objmap = array(

        'SKU'=> 'SKU',
        'brc1'=> 'brc1',
        'brc2'=> 'brc2',
        'brc3'=> 'brc3',
        'brchead'=> 'brchead',
        'contractNumber'=> 'contractNumber',
        'createdDate'=> 'createdDate',
        'defaultpic'=> 'defaultpic',
        'extId'=> 'extId',
        'itemDescription'=> 'itemDescription',
        'lastUpdate'=> 'lastUpdate',
        'locationId'=> 'locationId',
        'pictureFullUrl'=> 'pictureFullUrl',
        'pictureLargeUrl'=> 'pictureLargeUrl',
        'pictureMediumUrl'=> 'pictureMediumUrl',
        'pictureThumbnailUrl'=> 'pictureThumbnailUrl',
        'status'=> 'status',
        'tags'=> 'tags',
        'deleted'=>'deleted'

        //used for internal android app
        /*
        'localEdit'=>'localEdit',
        'uploaded'=> 'uploaded',
        'id'=> 'id',
        'tableName'=> 'ASSET',
        'query_string'=> 'query_string',
        'mode'=> 'mode'
        */
    );

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
		//
        $locations = \Rack::get();
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

               $pics = \Uploaded::where('parent_id', $locations[$i]->extId)
                                    ->where('parent_class','rack')
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

        }

        $actor = $key;
        \Event::fire('log.api',array($this->controller_name, 'get' ,$actor,'rack list'));

        return $locations;
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $json = \Input::all();

        $key = \Input::get('key');


        \Dumper::insert($json);

        $mappeddata = array();
        foreach($json as $k=>$v){
            if(isset($this->objmap[$k])){
                $mappeddata[ $this->objmap[$k] ] = $v;
            }
        }

        $data = $mappeddata;

        $data['_id'] = new \MongoId( $json['extId'] );

        $rack_id = $json['extId'];

        if( isset($data['createdDate']) && is_string($data['createdDate'])){
            $data['createdDate'] = new \MongoDate( strtotime($data['createdDate']) );
        }

        if( isset($data['lastUpdate']) && is_string($data['lastUpdate'])){
            $data['lastUpdate'] = new \MongoDate( strtotime($data['lastUpdate']) );
        }

        $location = \Assetlocation::find($data['locationId']);

        if($location && isset( $location->name ) ){
            $data['locationName'] = $location->name;
        }



        \Rack::insert($data);

        //$asset_id = \Asset::insertGetId($data);


        //log history

        //$data is the data after inserted

        $apvticket = \Assets::createApprovalRequest('new', 'rack' ,$data['_id'], $data['_id'] );

        $hdata = array();
        $hdata['historyTimestamp'] = new \MongoDate();
        $hdata['historyAction'] = 'new';
        $hdata['historySequence'] = 0;
        $hdata['historyObjectType'] = 'rack';
        $hdata['historyObject'] = $data;
        $hdata['approvalTicket'] = $apvticket;
        \History::insert($hdata);

        //$this->compileDiffs($data['_id']);

        $actor = $key;
        \Event::fire('log.api',array($this->controller_name, 'post' ,$actor,'post rack'));

        return \Response::json(array('status'=>'OK', 'timestamp'=>time(), 'message'=>$rack_id ));
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
        $json = \Input::all();

        $key = \Input::get('key');

        $json['mode'] = 'edit';

        $batch = \Input::get('batch');

        \Dumper::insert($json);

        $rack = \Rack::find($id);

        $rack_id = $id;

        if($rack){

            //create history - before state
            $hobj = $rack->toArray();

            $apvticket = \Assets::createApprovalRequest('update', 'rack' ,$id, $id );

            $hobj['_id'] = new \MongoId($id);

            $hdata['historyTimestamp'] = new \MongoDate();
            $hdata['historyAction'] = 'update';
            $hdata['historySequence'] = 0;
            $hdata['historyObjectType'] = 'rack';
            $hdata['historyObject'] = $hobj;
            $hdata['approvalTicket'] = $apvticket;
            \History::insert($hdata);

            //update data fields
            foreach($json as $k=>$v){
                if(isset($this->objmap[$k])){
                    $rack->{$this->objmap[$k]} = $v;
                }
            }

            if( isset($rack->lastUpdate) && is_string($rack->lastUpdate)){
                $rack->lastUpdate = new \MongoDate( strtotime($json['lastUpdate']) );
            }

            $location = \Assetlocation::find($rack->locationId);

            if($location && isset( $location->name ) ){
                $rack->locationName = $location->name;
            }

            $rack->save();

            $hndata = $rack->toArray();
            $hndata['_id'] = new \MongoId($id);

            $hdata = array();
            $hdata['historyTimestamp'] = new \MongoDate();
            $hdata['historyAction'] = 'update';
            $hdata['historySequence'] = 1;
            $hdata['historyObjectType'] = 'rack';
            $hdata['historyObject'] = $hndata;
            $hdata['approvalTicket'] = '';
            \History::insert($hdata);

            //$this->compileDiffs($id);


            $actor = $key;
            \Event::fire('log.api',array($this->controller_name, 'put' ,$actor,'update rack'));

            return \Response::json(array('status'=>'OK', 'timestamp'=>time(), 'message'=>$rack_id ));

        }else{

            $mappeddata = array();
            foreach($json as $k=>$v){
                if(isset($this->objmap[$k])){
                    $mappeddata[ $this->objmap[$k] ] = $v;
                }
            }

            $data = $mappeddata;

            $data['_id'] = new \MongoId( $json['extId'] );

            $rack_id = $json['extId'];

            if( isset($data['createdDate']) && is_string($data['createdDate'])){
                $data['createdDate'] = new \MongoDate( strtotime($data['createdDate']) );
            }

            if( isset($data['lastUpdate']) && is_string($data['lastUpdate'])){
                $data['lastUpdate'] = new \MongoDate( strtotime($data['lastUpdate']) );
            }

            $location = \Assetlocation::find($data['locationId']);

            if($location && isset( $location->name ) ){
                $data['locationName'] = $location->name;
            }

            \Rack::insert($data);

            //log history

            //$data is the data after inserted

            $apvticket = \Assets::createApprovalRequest('new', 'rack' ,$data['_id'], $data['_id'] );

            $hdata = array();
            $hdata['historyTimestamp'] = new \MongoDate();
            $hdata['historyAction'] = 'new';
            $hdata['historySequence'] = 0;
            $hdata['historyObjectType'] = 'rack';
            $hdata['historyObject'] = $data;
            $hdata['approvalTicket'] = $apvticket;
            \History::insert($hdata);

            //$this->compileDiffs($data['_id']);

            $actor = $key;
            \Event::fire('log.api',array($this->controller_name, 'post' ,$actor,'post rack'));

            return \Response::json(array('status'=>'OK', 'timestamp'=>time(), 'message'=>$rack_id ));

            /*
            $actor = $key;
            \Event::fire('log.api',array($this->controller_name, 'put' ,$actor,'update rack failed'));
            return \Response::json(array('status'=>'ERR:NOTEXIST', 'timestamp'=>time() ));
            */

        }

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
