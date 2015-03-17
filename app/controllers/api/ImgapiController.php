<?php
namespace Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;


class ImgapiController extends \BaseController {

    public $controller_name = '';

    public $objmap = array(
        'ns' => 'ns',
        'parentId' => 'parent_id',
        'parentClass' => 'parent_class',
        'url' => 'url',
        'fileId' => 'file_id',
        'isImage' => 'is_image',
        'isAudio' => 'is_audio',
        'isVideo' => 'is_video',
        'isPdf' => 'is_pdf',
        'isDoc' => 'is_doc',
        'name' => 'name',
        'type' => 'type',
        'size' => 'size',
        'createdDate' => 'createdDate',
        'lastUpdate' => 'lastUpdate',
        'pictureFullUrl'=> 'full_url',
        'pictureLargeUrl'=> 'large_url',
        'pictureMediumUrl'=> 'medium_url',
        'pictureThumbnailUrl'=> 'thumbnail_url'
    );

    public $exclude = array(
        'ns',
        'url',
        'name',
        'type',
        'size'
    );


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $key = Input::get('key');
        $id = Input::get('id');
        $class = Input::get('cls');

        if(is_null($id) || $id == 'all'){
            $images = \Uploaded::get();
        }else{
            $images = \Uploaded::where('parent_id', $id)
                            ->where('parent_class', $class)
                            ->get();
        }
        for($i = 0; $i < count($images);$i++){


                unset($images[$i]->_id);
                unset($images[$i]->_token);

                foreach($this->objmap as $k=>$v){
                    $images[$i]->{$k} = $images[$i]->{$v};
                    if(!in_array($k, $this->exclude)){
                        unset($images[$i]->{$v});
                    }
                }

                unset($images[$i]->delete_url);
                unset($images[$i]->delete_type);

                $images[$i]->extId = $images[$i]->parentId;
                $images[$i]->deleted = 0;

                if( isset($images[$i]->createdDate) && !is_string($images[$i]->createdDate) ){
                    $images[$i]->createdDate = date('Y-m-d H:i:s',$images[$i]->createdDate->sec);
                }

                if(isset($images[$i]->lastUpdate) && !is_string($images[$i]->lastUpdate)){
                    $images[$i]->lastUpdate = date('Y-m-d H:i:s',$images[$i]->lastUpdate->sec);
                }

        }

        $actor = $key;
        \Event::fire('log.api',array($this->controller_name, 'get' ,$actor,'get image list'));

        return $images;
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

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $itemId
     * @param string $key
     * @return Response
     */
    public function show($itemId, $key)
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
