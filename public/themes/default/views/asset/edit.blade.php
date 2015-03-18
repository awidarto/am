@extends('layout.fixedtwo')

@section('left')
        {{ Former::hidden('id')->value($formdata['_id']) }}

        <h5>Device Info</h5>

        {{ Former::text('SKU','Asset Code') }}

        {{ Former::select('status')->options(array('inactive'=>'Inactive','active'=>'Active'))->label('Status') }}

        {{ Former::select('assetType','Device Type')->options( Assets::getType()->TypeToSelection('type','type',true) ) }}

        {{ Former::select('locationId','Location')->id('location')->options( Assets::getLocation()->LocationToSelection('_id','name',true) ) }}
        {{ Former::select('rackId','Rack')->id('rack')->options( Assets::getRack()->RackToSelection('_id','SKU',true) ) }}
        {{ Former::text('itemDescription','Description') }}

        <div class="row">
            <div class="col-md-6">
                <h4>Host Info</h4>
                {{ Former::text('IP','IP Address') }}
                {{ Former::text('hostName','Host Name') }}
                {{ Former::text('OS','Operating System') }}
            </div>
            <div class="col-md-6">
                <h4>Status</h4>

                {{ Former::select('powerStatus')->label('Power Status')->options(array('1'=>'Yes','0'=>'No')) }}
                {{ Former::select('labelStatus')->label('Label Status')->options(array('1'=>'Yes','0'=>'No')) }}
                {{ Former::select('virtualStatus')->label('Virtual Status')->options(array('1'=>'Yes','0'=>'No')) }}
            </div>
        </div>
        {{ Form::submit('Save',array('class'=>'btn btn-primary'))}}&nbsp;&nbsp;
        {{ HTML::link($back,'Cancel',array('class'=>'btn'))}}

@stop

@section('right')
        <h4>Owner & Person In Charge</h4>
        {{ Former::text('owner','Owner') }}

        {{ Former::text('PIC','Person In Charge') }}

        {{ Former::text('PicPhone','PIC Phone') }}
        {{ Former::text('PicEmail','PIC Email') }}

        {{ Former::text('contractNumber','Contract Number') }}

        {{ Former::text('tags','Tags')->class('tag_keyword') }}

        <h5>Pictures</h5>
        <?php
            $fupload = new Wupload();
        ?>
        {{ $fupload->id('imageupload')
            ->ns('assetpic')
            ->parentid($formdata['_id'])
            ->parentclass('asset')
            ->title('Select Picture')
            ->label('Upload Picture')
            ->url('upload/file')
            ->singlefile(false)
            ->prefix('asset')
            ->multi(true)
            ->make($formdata) }}


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