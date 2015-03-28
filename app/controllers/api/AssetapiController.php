<?php
namespace Api;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;

class AssetapiController extends \BaseController {

    public $controller_name = '';

    public $objmap = array(

        'IP'=>'IP',
        'OS'=> 'OS',
        'PIC'=> 'PIC',
        'PicEmail'=>'PicEmail',
        'PicPhone'=>'PicPhone',
        'SKU'=> 'SKU',
        'assetType'=> 'assetType',
        'brc1'=> 'brc1',
        'brc2'=> 'brc2',
        'brc3'=> 'brc3',
        'brchead'=> 'brchead',
        'contractNumber'=> 'contractNumber',
        'createdDate'=> 'createdDate',
        'defaultpic'=> 'defaultpic',
        'extId'=> 'extId',
        'hostName'=> 'hostName',
        'itemDescription'=> 'itemDescription',
        'lastUpdate'=> 'lastUpdate',
        'locationId'=> 'locationId',
        'owner'=> 'owner',
        'pictureFullUrl'=> 'pictureFullUrl',
        'pictureLargeUrl'=> 'pictureLargeUrl',
        'pictureMediumUrl'=> 'pictureMediumUrl',
        'pictureThumbnailUrl'=> 'pictureThumbnailUrl',
        'rackId'=> 'rackId',
        'rackName'=> 'rackName',
        'powerStatus'=> 'powerStatus',
        'labelStatus'=>'labelStatus',
        'virtualStatus'=>'virtualStatus',
        'status'=> 'status',
        'tags'=> 'tags'

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

        $assets = \Asset::get();
        for($i = 0; $i < count($assets);$i++){

                $assets[$i]->extId = $assets[$i]->_id;

                unset($assets[$i]->_id);
                unset($assets[$i]->_token);

                unset($assets[$i]->thumbnail_url);
                unset($assets[$i]->large_url);
                unset($assets[$i]->medium_url);
                unset($assets[$i]->full_url);
                unset($assets[$i]->delete_type);
                unset($assets[$i]->delete_url);
                unset($assets[$i]->filename);
                unset($assets[$i]->filesize);
                unset($assets[$i]->temp_dir);
                unset($assets[$i]->filetype);
                unset($assets[$i]->is_image);
                unset($assets[$i]->is_audio);
                unset($assets[$i]->is_video);
                unset($assets[$i]->fileurl);
                unset($assets[$i]->file_id);
                unset($assets[$i]->caption);
                unset($assets[$i]->files);
                unset($assets[$i]->medium_portrait_url);

                if(!isset( $assets[$i]->rackName )){
                    $rack = \Rack::find( $assets[$i]->rackId );

                    if($rack && isset( $rack->SKU ) ){
                        $assets[$i]->rackName = $rack->SKU;
                    }

                }

                $pics = \Uploaded::where('parent_id', $assets[$i]->extId)
                                    ->where('parent_class','asset')
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

                    $assets[$i]->pictureThumbnailUrl = $dp['thumbnail_url'];
                    $assets[$i]->pictureLargeUrl = $dp['large_url'];
                    $assets[$i]->pictureMediumUrl = $dp['medium_url'];
                    $assets[$i]->pictureFullUrl = $dp['full_url'];
                    $assets[$i]->pictureBrchead = $dp['medium_url'];
                    $assets[$i]->pictureBrc1 = $dp['medium_url'];
                    $assets[$i]->pictureBrc2 = $dp['medium_url'];
                    $assets[$i]->pictureBrc3 = $dp['medium_url'];


                }else{
                    $assets[$i]->pictureThumbnailUrl = '';
                    $assets[$i]->pictureLargeUrl = '';
                    $assets[$i]->pictureMediumUrl = '';
                    $assets[$i]->pictureFullUrl = '';
                    $assets[$i]->pictureBrchead = '';
                    $assets[$i]->pictureBrc1 = '';
                    $assets[$i]->pictureBrc2 = '';
                    $assets[$i]->pictureBrc3 = '';
                }

                unset($assets[$i]->defaultpictures);

                if( isset($assets[$i]->powerStatus) ){
                    $assets[$i]->powerStatus = ( $assets[$i]->powerStatus == 'yes' || strtolower($assets[$i]->powerStatus) == 'y' || intval($assets[$i]->powerStatus) == 1 )?1:0;
                }

                if( isset($assets[$i]->labelStatus) ){
                    $assets[$i]->labelStatus = ( $assets[$i]->labelStatus == 'yes' || strtolower($assets[$i]->labelStatus) == 'y' || intval($assets[$i]->labelStatus) == 1 )?1:0;
                }

                if( isset($assets[$i]->virtualStatus) ){
                    $assets[$i]->virtualStatus = ( $assets[$i]->virtualStatus == 'yes' || strtolower($assets[$i]->virtualStatus) == 'y' || intval($assets[$i]->virtualStatus) == 1 )?1:0;
                }

                if( isset($assets[$i]->createdDate) && !is_string($assets[$i]->createdDate) ){
                    $assets[$i]->createdDate = date('Y-m-d H:i:s',$assets[$i]->createdDate->sec);
                }

                if(isset($assets[$i]->lastUpdate) && !is_string($assets[$i]->lastUpdate)){
                    $assets[$i]->lastUpdate = date('Y-m-d H:i:s',$assets[$i]->lastUpdate->sec);
                }


        }

        $actor = $key;
        \Event::fire('log.api',array($this->controller_name, 'get' ,$actor,'get asset list'));

        return $assets;
		//
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

        $asset_id = $json['extId'];

        if( isset($data['createdDate']) && is_string($data['createdDate'])){
            $data['createdDate'] = new \MongoDate( strtotime($data['createdDate']) );
        }

        if( isset($data['lastUpdate']) && is_string($data['lastUpdate'])){
            $data['lastUpdate'] = new \MongoDate( strtotime($data['lastUpdate']) );
        }

        if( isset($data['powerStatus']) ){
            $data['powerStatus'] = ($data['powerStatus'] == 1)?'yes':'no' ;
        }

        if( isset($data['labelStatus']) ){
            $data['labelStatus'] = ($data['labelStatus'] == 1)?'yes':'no' ;
        }

        if( isset($data['virtualStatus']) ){
            $data['virtualStatus'] = ($data['virtualStatus'] == 1)?'yes':'no' ;
        }

        $rack = \Rack::find($data['rackId']);

        if($rack && isset( $rack->SKU ) ){
            $data['rackName'] = $rack->SKU;
        }



        \Asset::insert($data);

        //$asset_id = \Asset::insertGetId($data);


        //log history

        //$data is the data after inserted

        $apvticket = \Assets::createApprovalRequest('new', $data['assetType'],$data['_id'], $data['_id'] );

        $hdata = array();
        $hdata['historyTimestamp'] = new \MongoDate();
        $hdata['historyAction'] = 'new';
        $hdata['historySequence'] = 0;
        $hdata['historyObjectType'] = 'asset';
        $hdata['historyObject'] = $data;
        $hdata['approvalTicket'] = $apvticket;
        \History::insert($hdata);

        //$this->compileDiffs($data['_id']);

        $actor = $key;
        \Event::fire('log.api',array($this->controller_name, 'post' ,$actor,'post asset'));

        return \Response::json(array('status'=>'OK', 'timestamp'=>time(), 'message'=>$asset_id ));

	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $in = Input::get();
        if(isset($in['key']) && $in['key'] != ''){
            print $in['key'];
        }else{
            print 'no key';
        }
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

        $asset = \Asset::find($id);

        $asset_id = $id;

        if($asset){

            //create history - before state
            $hobj = $asset->toArray();

            $apvticket = \Assets::createApprovalRequest('update', $hobj['assetType'],$id, $id );

            $hobj['_id'] = new \MongoId($id);

            $hdata['historyTimestamp'] = new \MongoDate();
            $hdata['historyAction'] = 'update';
            $hdata['historySequence'] = 0;
            $hdata['historyObjectType'] = 'asset';
            $hdata['historyObject'] = $hobj;
            $hdata['approvalTicket'] = $apvticket;
            \History::insert($hdata);

            //update data fields
            foreach($json as $k=>$v){
                if(isset($this->objmap[$k])){
                    $asset->{$this->objmap[$k]} = $v;
                }
            }

            if( isset($asset->lastUpdate) && is_string($asset->lastUpdate)){
                $asset->lastUpdate = new \MongoDate( strtotime($json['lastUpdate']) );
            }
            /*
            if( isset($asset->powerStatus) ){
                $asset->powerStatus = ($json['powerStatus'] == 1)?'yes':'no' ;
            }

            if( isset($asset->labelStatus) ){
                $asset->labelStatus = ($json['labelStatus'] == 1)?'yes':'no' ;
            }

            if( isset($asset->virtualStatus) ){
                $asset->virtualStatus = ($json['virtualStatus'] == 1)?'yes':'no' ;
            }
            */

            $rack = \Rack::find($asset->rackId );

            if($rack && isset( $rack->SKU ) ){
                $asset->rackName = $rack->SKU;
            }

            $asset->save();

            $hndata = $asset->toArray();
            $hndata['_id'] = new \MongoId($id);

            $hdata = array();
            $hdata['historyTimestamp'] = new \MongoDate();
            $hdata['historyAction'] = 'update';
            $hdata['historySequence'] = 1;
            $hdata['historyObjectType'] = 'asset';
            $hdata['historyObject'] = $hndata;
            $hdata['approvalTicket'] = '';
            \History::insert($hdata);

            //$this->compileDiffs($id);


            $actor = $key;
            \Event::fire('log.api',array($this->controller_name, 'put' ,$actor,'update asset'));

            return \Response::json(array('status'=>'OK', 'timestamp'=>time(), 'message'=>$asset_id ));

        }else{

            $mappeddata = array();
            foreach($json as $k=>$v){
                if(isset($this->objmap[$k])){
                    $mappeddata[ $this->objmap[$k] ] = $v;
                }
            }

            $data = $mappeddata;

            $data['_id'] = new \MongoId( $json['extId'] );

            $asset_id = $json['extId'];

            if( isset($data['createdDate']) && is_string($data['createdDate'])){
                $data['createdDate'] = new \MongoDate( strtotime($data['createdDate']) );
            }

            if( isset($data['lastUpdate']) && is_string($data['lastUpdate'])){
                $data['lastUpdate'] = new \MongoDate( strtotime($data['lastUpdate']) );
            }

            /*
            if( isset($data['powerStatus']) ){
                $data['powerStatus'] = ($data['powerStatus'] == 1)?'yes':'no' ;
            }

            if( isset($data['labelStatus']) ){
                $data['labelStatus'] = ($data['labelStatus'] == 1)?'yes':'no' ;
            }

            if( isset($data['virtualStatus']) ){
                $data['virtualStatus'] = ($data['virtualStatus'] == 1)?'yes':'no' ;
            }
            */

            $rack = \Rack::find($data['rackId']);

            if($rack && isset( $rack->SKU ) ){
                $data['rackName'] = $rack->SKU;
            }

            \Asset::insert($data);


            //log history

            //$data is the data after inserted

            $apvticket = \Assets::createApprovalRequest('new', $data['assetType'],$data['_id'], $data['_id'] );

            $hdata = array();
            $hdata['historyTimestamp'] = new \MongoDate();
            $hdata['historyAction'] = 'new';
            $hdata['historySequence'] = 0;
            $hdata['historyObjectType'] = 'asset';
            $hdata['historyObject'] = $data;
            $hdata['approvalTicket'] = $apvticket;
            \History::insert($hdata);

            //$this->compileDiffs($data['_id']);

            $actor = $key;
            \Event::fire('log.api',array($this->controller_name, 'post' ,$actor,'post asset'));

            return \Response::json(array('status'=>'OK', 'timestamp'=>time(), 'message'=>$asset_id ));

            /*
            $actor = $key;
            \Event::fire('log.api',array($this->controller_name, 'put' ,$actor,'update asset failed'));
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

    public function compileDiffs($id){
        $_id = new \MongoId($id);

        $history = \History::where('historyObject._id',$_id)->where('historyObjectType','asset')
                ->orderBy('historyTimestamp','desc')
                ->orderBy('historySequence','desc')
                ->get();
        $diffs = array();

        foreach($history as $h){
            $h->date = date( 'Y-m-d H:i:s', $h->historyTimestamp->sec );
            $diffs[$h->date][$h->historySequence] = $h->historyObject;
        }

        $history = \History::where('historyObject._id',$_id)->where('historyObjectType','asset')
                        ->where('historySequence',0)
                        ->orderBy('historyTimestamp','desc')
                        ->get();

        foreach($history as $h){
            $h->historyDiff = ($h->historyAction == 'new')?'NA':$this->objdiff( $diffs[$d], 'array' );
            $h->save();
        }

    }

    public function objdiff($obj, $type = null)
    {

        if(is_array($obj) && count($obj) == 2){
            $diff = array();
            foreach ($obj[0] as $key=>$value) {
                if(isset($obj[0][$key]) && isset($obj[1][$key])){
                    if($obj[0][$key] !== $obj[1][$key]){
                        if($key != '_id' && $key != 'createdDate' && $key != 'lastUpdate'){
                            if(!is_array($obj[0][$key])){
                                if(is_null($type)){
                                    $diff[] = $key.' : '. $obj[0][$key].' -> '.$obj[1][$key];
                                }else{
                                    $diff[$key] = array('from'=>$obj[0][$key] ,'to'=>$obj[1][$key] );
                                }

                            }
                        }
                    }
                }
            }
            if(is_null($type)){
                return implode('<br />', $diff);
            }else{
                return $diff;
            }
        }else{
            if(is_null($type)){
                return 'NA';
            }else{
                return array();
            }
        }
    }

    public function ismoving($obj){
        $location_move = false;
        $rack_move = false;
        if(is_array($obj) && count($obj) == 2){

            if(isset($obj[0]['locationId']) && isset($obj[1]['locationId'])){
                if($obj[0]['locationId'] !== $obj[0]['locationId']){
                    $location_move = true;
                }
            }

            if(isset($obj[0]['rackId']) && isset($obj[1]['rackId'])){
                if($obj[0]['rackId'] !== $obj[0]['rackId']){
                    $rack_move = true;
                }
            }

            return array(
                    'location_move'=>$location_move,
                    'rack_move'=>$rack_move
                );
        }else{
            return 'NA';
        }

    }


}
