@extends('layouts/app')
@section('content')

    {{--@if (isset($post))--}}
    {{--<pre>--}}
        {{--{{print_r($post,true)}}--}}
    {{--</pre>--}}
    {{--@endif--}}

    <div>
        <form name="permissions" action="/superuser/migration3" method="post">

            {{ csrf_field() }}
            <textarea name="migrationValuesJson" class="hide">{!! $migrationValuesJson !!}</textarea>
            <textarea name="modelValuesJson" class="hide">{!! $modelValuesJson !!}</textarea>

            @include('superuser.includes.modelconf')


            <div class="col col-sm-12 text-center">
                <button class="btn btn-default" >
                    Salva
                </button>
                <a href="/superuser/migration" class="btn btn-danger">
                    Indietro
                </a>
            </div>

            <br/>


        </form>
    </div>


    <script>

        $(function () {
        });

    </script>
@stop
