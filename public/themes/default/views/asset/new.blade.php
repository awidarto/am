@extends('layout.fixedtwo')


@section('left')

        <h5>Device Info</h5>

        <div class="row">
            <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
                {{ Former::text('SKU','Asset Code')->class('idgen form-control') }}
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                <br />
                <span class="btn btn-primary gen" style="margin-top:9px;" >Generate</span>
            </div>
        </div>


        {{ Former::select('status')->options(array('inactive'=>'Inactive','active'=>'Active'))->label('Status') }}

        {{ Former::select('assetType','Device Type')->options( Assets::getType()->TypeToSelection('type','type',true) ) }}

        {{ Former::select('locationId','Location')->id('location')->options( Assets::getLocation()->LocationToSelection('_id','name',true) ) }}
        {{ Former::select('rackId','Rack')->id('rack')->options( Assets::getRack()->RackToSelection('_id','SKU',true) ) }}
        {{ Former::text('itemDescription','Description') }}

        <div class="row">
            <div class="col-md-6">
                <h5>Host Info</h5>
                {{ Former::text('IP','IP Address') }}
                {{ Former::text('hostName','Host Name') }}
                {{ Former::text('OS','Operating System') }}
            </div>
            <div class="col-md-6">
                <h5>Status</h5>

                {{ Former::select('powerStatus')->label('Power Status')->options(array('1'=>'Yes','0'=>'No')) }}
                {{ Former::select('labelStatus')->label('Label Status')->options(array('1'=>'Yes','0'=>'No')) }}
                {{ Former::select('virtualStatus')->label('Virtual Status')->options(array('1'=>'Yes','0'=>'No')) }}
            </div>
        </div>

        {{ Form::submit('Save',array('class'=>'btn btn-primary'))}}&nbsp;&nbsp;
        {{ HTML::link($back,'Cancel',array('class'=>'btn'))}}

@stop

@section('right')
        <h5>Owner & Person In Charge</h5>
        {{ Former::text('owner','Owner') }}

        {{ Former::text('PIC','Person In Charge') }}

        {{ Former::text('PicPhone','PIC Phone') }}
        {{ Former::text('PicEmail','PIC Email') }}

        {{ Former::text('contractNumber','Contract Number') }}

        {{ Former::text('tags','Tags')->class('tag_keyword') }}

        <h5>Pictures</h5>
        <?php
            $fupload = new Wupload();
            $temp_id = str_random(10);
        ?>
        {{ $fupload->id('imageupload')
            ->ns('assetpic')
            ->parentid($temp_id)
            ->parentclass('asset')
            ->title('Select Picture')
            ->label('Upload Picture')
            ->url('upload/file')
            ->singlefile(false)
            ->prefix('asset')
            ->multi(true)
            ->make() }}


        <?php

            //$fupload = new Fupload();
        ?>
        {{-- $fupload->id('imageupload')->title('Select Picture')->label('Upload Picture')
            ->url('upload/asset')
            ->singlefile(false)
            ->prefix('asset')
            ->multi(true)->make() --}}

@stop

@section('modals')

@stop

@section('aux')

<script type="text/javascript">


$(document).ready(function() {


    $('.pick-a-color').pickAColor();

    $('.gen').on('click',function(){
        var id = makeid(8);
        $('.idgen').val(id);
        return false;
    })

    $('#name').keyup(function(){
        var title = $('#name').val();
        var slug = string_to_slug(title);
        $('#permalink').val(slug);
    });

    $('#location').on('change',function(){
        var location = $('#location').val();
        console.log(location);

        $.post('{{ URL::to('asset/rack' ) }}',
            {
                loc : location
            },
            function(data){
                var opt = updateselector(data.html);
                $('#rack').html(opt);
            },'json'
        );

    })

    function updateselector(data){
        var opt = '';
        for(var k in data){
            opt += '<option value="' + k + '">' + data[k] +'</option>';
        }
        return opt;
    }

    function makeid( len )
    {
        var text = "";
        //var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        for( var i=0; i < len; i++ )
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }


});

</script>

@stop