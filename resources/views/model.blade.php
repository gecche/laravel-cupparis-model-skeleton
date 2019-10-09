@extends('app')
@section('content')

    {{--@if (isset($post))--}}
    {{--<pre>--}}
        {{--{{print_r($post,true)}}--}}
    {{--</pre>--}}
    {{--@endif--}}

    <div>
        <form name="superuser_model" action="/cupparis/modelskeleton/model" method="post">
            {{ csrf_field() }}
        <div class="col col-sm-12">
            <div class="">
                <h2>Modello</h2>
            </div>
            <div class="">
                <div class="col col-sm-6">
                    Nome tabella
                </div>
                <div class="col col-sm-6">
                    <select class="form-control" name="nome_tabella">
                        @foreach ($model['options']['tables'] as $optionKey => $optionValue)
                            <option value="{{$optionKey}}">
                                {!! $optionValue !!}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

            <hr/>

            <div class="col col-sm-12 text-center">
                <button class="btn btn-default" >
                    Continua
                </button>
                <a href="/cupparis/modelskeleton/migrations" class="btn btn-danger">
                    Indietro
                </a>
            </div>

{{--

<div class="col col-md-6 col-sm-12 panel panel-default">
<div class="panel-heading">
   Ruoli
</div>
<div class="panel-body">

   @foreach($permissions['roles'] as $role)
   <div class="col col-md-3 col-sm-6">
       <button class="btn btn-default" data-role="{{$role}}">
       {{$role}}
       </button>
   </div>
       @endforeach
</div>
</div>

--}}
        </form>
    </div>


    <script>

        jQuery(function () {
        });

    </script>
@stop
