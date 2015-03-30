<?php
namespace Api;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;

class SyncapiController extends \Controller {
    public $controller_name = '';

    public function  __construct()
    {
        //$this->model = "Member";
        $this->controller_name = strtolower( str_replace('Controller', '', get_class()) );

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postScanlog()
    {

        $json = \Input::all();

        $key = \Input::get('key');

        $json['mode'] = 'edit';

        $batch = \Input::get('batch');

        \Dumper::insert($json);

        $result = array();

        foreach( $json as $j){
            print_r($j);
            //\Dumper::insert($j);

            if(isset( $j['logId'])){
                $log = \Scanlog::where('logId', $j['logId'] )->first();

                if($log){
                    $result[] = array('status'=>'OK', 'timestamp'=>time(), 'message'=>$j['logId'] );
                }else{
                    \Scanlog::insert($j);
                    $result[] = array('status'=>'OK', 'timestamp'=>time(), 'message'=>$j['logId'] );
                }
            }
        }

        print_r($result);

        die();

        return Response::json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function putAssets()
    {

        $json = \Input::all();

        $key = \Input::get('key');

        $json['mode'] = 'edit';

        $batch = \Input::get('batch');

        \Dumper::insert($json);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function putRacks()
    {

        $json = \Input::all();

        $key = \Input::get('key');

        $json['mode'] = 'edit';

        $batch = \Input::get('batch');

        \Dumper::insert($json);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function putLocations()
    {

        $json = \Input::all();

        $key = \Input::get('key');

        $json['mode'] = 'edit';

        $batch = \Input::get('batch');

        \Dumper::insert($json);

    }

}