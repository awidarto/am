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
        'status'=> 'status',
        'tags'=> 'tags',

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

                if(isset($assets[$i]->defaultpictures)){
                    $dp = $assets[$i]->defaultpictures;

                    if(isset($dp['delete_type'])){
                        unset($dp['delete_type']);
                    }
                    if(isset($dp['delete_url'])){
                        unset($dp['delete_url']);
                    }
                    if(isset($dp['temp_dir'])){
                        unset($dp['temp_dir']);
                    }

                    if(is_array($dp)){
                        foreach($dp as $k=>$v){
                            $name = 'picture'.str_replace(' ', '', ucwords( str_replace('_', ' ', $k) ));
                            $assets[$i]->{$name} = $v;
                        }
                    }

                    unset($assets[$i]->defaultpictures);

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

                $assets[$i]->createdDate = date('Y-m-d H:i:s',$assets[$i]->createdDate->sec);
                $assets[$i]->lastUpdate = date('Y-m-d H:i:s',$assets[$i]->lastUpdate->sec);


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

        if( isset($data['createdDate']) && is_string($data['createdDate'])){
            $data['createdDate'] = new \MongoDate( strtotime($data['createdDate']) );
        }

        if( isset($data['lastUpdate']) && is_string($data['lastUpdate'])){
            $data['lastUpdate'] = new \MongoDate( strtotime($data['lastUpdate']) );
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

        $actor = $key;
        \Event::fire('log.api',array($this->controller_name, 'post' ,$actor,'post asset'));

        return \Response::json(array('status'=>'OK', 'timestamp'=>time() ));

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

        \Dumper::insert($json);

        $asset = \Asset::find($id);

        if($asset){

            //create history - before state

            $apvticket = \Assets::createApprovalRequest('update', $hobj['assetType'],$id, $id );

            $hobj = $asset->toArray();
            $hobj['_id'] = new \MongoId($id);

            $hdata['historyTimestamp'] = new MongoDate();
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


            $actor = $key;
            \Event::fire('log.api',array($this->controller_name, 'put' ,$actor,'update asset'));

            return \Response::json(array('status'=>'OK', 'timestamp'=>time() ));

        }else{

            $actor = $key;
            \Event::fire('log.api',array($this->controller_name, 'put' ,$actor,'update asset failed'));

            return \Response::json(array('status'=>'ERR:NOTEXIST', 'timestamp'=>time() ));

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
