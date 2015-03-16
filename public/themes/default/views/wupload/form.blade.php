{{ HTML::script('js/zeroclipboard/ZeroClipboard.js') }}
<div class="control-group">
    <div class="controls">
        <div class="fileupload fileupload-new margin-none" data-provides="fileupload">
            <span class="btn btn-default btn-file">
                <span class="fileupload-new">{{ $title }}</span>
                <input id="{{ $element_id }}" type="file" name="files[]" {{ ($multi)?'multiple':''}}  >
            </span>
        </div>
        <br />
        <div id="{{ $element_id }}_progress" class="progress progress-mini">
            <div class="bar progress-bar progress-bar-danger"></div>
        </div>
        <br />
        <span id="loading-pictures" style="display:none;" ><img src="{{URL::to('/') }}/images/loading.gif" />loading existing pictures...</span>



        <div id="{{ $element_id }}_files" class="files">
            <input type="hidden" name="parent_id" value="{{ $parent_id }}" />
            <input type="hidden" name="{{ $element_id }}_ns" value="{{ $ns }}" />
            <ul style="margin-left:-25px">
                <?php

                    $allin = Input::old();
                    $showold = false;

                    if( count($allin) > 0){
                        $showold = true;
                    }

                    if( !is_null($formdata) && isset($formdata['_id']) && $showold == false ){

                        /* external detail template */

                                                // display previously saved data
                        //for($t = 0; $t < count($filename);$t++){

                        $files = Uploaded::where('parent_id',$formdata['_id'] )->get();


                        if( count($files->toArray()) > 0){
                            foreach ($files->toArray() as $fd) {
                                //print_r($fd);

                                if($prefix != ''){
                                    $detailview = $prefix.'.wdetail';
                                }else{
                                    $detailview = 'wupload.detail';
                                }

                                $thumb = View::make($detailview)
                                                ->with('filedata',$fd)
                                                ->render();


                                //if($fd['ns'] == $ns){
                                    print $thumb;
                                //}

                            }

                        }


                    }

                    // display re-populated data from error form
                    if($showold && isset( $allin['parent_id'])){

                        //print_r($allin);

                        $files = Uploaded::where('parent_id',$allin['parent_id'] )->get();

                        $ns = (isset($allin[$element_id.'_ns']))?$allin[$element_id.'_ns']:'';

                        if( count($files->toArray()) > 0){
                            foreach ($files->toArray() as $fd) {
                                //print_r($fd);

                                if($prefix != ''){
                                    $detailview = $prefix.'.wdetail';
                                }else{
                                    $detailview = 'wupload.detail';
                                }

                                $thumb = View::make($detailview)
                                                ->with('filedata',$fd)
                                                ->render();

                                if($fd['ns'] == $ns){
                                    print $thumb;
                                }


                            }

                        }

                    }
                ?>
            </ul>
        </div>
        <div id="{{ $element_id }}_uploadedform">
            <ul style="list-style:none">
            </ul>
        </div>
    </div>
</div>

<style type="text/css">
    .file_del, .file_copy{
        cursor: pointer;
    }
</style>

<script type="text/javascript">

$(document).ready(function(){

    var url = '{{ URL::to($url) }}?parclass={{ $parent_class }}&parid={{ $parent_id }}&ns={{ $ns }}';

    var clip = new ZeroClipboard($('.file_copy').each(function(){ }),{
        moviePath: '{{ URL::to('js/zeroclipboard')}}/ZeroClipboard.swf'
    });

    $('#{{ $element_id }}_files').on('click',function(e){

        if ($(e.target).is('.file_del')) {
            var _id = e.target.id;
            var answer = confirm("Are you sure you want to delete this item ?");

            console.log($(e.target).parent());

            if (answer == true){
                $('#par_' + _id).remove();
                //$(e.target).parent().remove();
                $('#fdel_'+e.target.id).remove();
                /*
                $.post('',{'id':_id}, function(data) {
                    if(data.status == 'OK'){



                        alert("Item id : " + _id + " deleted");
                    }
                },'json');
                */
            }else{
                alert("Deletion cancelled");
            }
        }
    });

    $('#{{ $element_id }}').fileupload({
        url: url,
        dataType: 'json',
        done: function (e, data) {
            $('#{{ $element_id }}_progress .bar').css(
                'width',
                '0%'
            );

            if(data.result.status == 'OK'){

                $.each(data.result.files, function (index, file) {

                    @if($prefix == '')
                        {{-- View::make('wupload.jsdetail') --}}
                    @else
                        {{-- View::make($prefix.'.wjsdetail') --}}
                    @endif

                    console.log(thumb);

                    @if($singlefile == true)
                        $('#{{ $element_id }}_files ul').html(thumb);
                    @else
                        $(thumb).prependTo('#{{ $element_id }}_files ul');
                    @endif

                    @if($singlefile == true)
                        $('#{{ $element_id }}_uploadedform ul').html(upl);
                    @else
                        $(upl).prependTo('#{{ $element_id }}_uploadedform ul');
                    @endif

                    clip = new ZeroClipboard($('.file_copy').each(function(){ }),{
                        moviePath: '{{ URL::to('js/zeroclipboard')}}/ZeroClipboard.swf'
                    });

                });
                //$('audio').audioPlayer();
                //videojs(document.getElementsByClassName('video-js')[0], {}, function(){});
            }else{
                alert(data.result.message)
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#{{ $element_id }}_progress .bar').css(
                'width',
                progress + '%'
            );
        }
    })
    .prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');


});


</script>

