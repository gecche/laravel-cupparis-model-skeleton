@extends($layoutView)
@section('content')

    {{--@if (isset($post))--}}
    {{--<pre>--}}
        {{--{{print_r($post,true)}}--}}
    {{--</pre>--}}
    {{--@endif--}}

    <div>
        <form name="permissions" action="/cupparis/modelskeleton/migration3" method="post">

            {{ csrf_field() }}
            <textarea name="migrationValuesJson" class="hide">{!! $migrationValuesJson !!}</textarea>
            <textarea name="modelValuesJson" class="hide">{!! $modelValuesJson !!}</textarea>

            @include('modelskeleton::includes.modelconf')


            <div class="col col-sm-12 text-center">
                <button class="btn btn-default" >
                    Salva
                </button>
                <a href="/cupparis/modelskeleton/migration" class="btn btn-danger">
                    Indietro
                </a>
            </div>

            <br/>


        </form>
    </div>


    <script>

        jQuery(function () {
        });

    </script>
@stop
