@extends('app')
@section('content')

    {{--@if (isset($post))--}}
    {{--<pre>--}}
        {{--{{print_r($post,true)}}--}}
    {{--</pre>--}}
    {{--@endif--}}

    <div>
        <form name="suepruser_model" action="/cupparis/modelskeleton/model2" method="post">

            {{ csrf_field() }}
            <textarea name="migrationValuesJson" class="hide">{!! $migrationValuesJson !!}</textarea>

            @include('modelskeleton::includes.model')

            <div class="col col-sm-12 panel panel-success">
                <div class="panel-heading">
                    <h2>ModelsConfs</h2>
                </div>
                <div class="panel-body">
                    <table class="table table-responsive table-bordered">
                        <tr>
                            <td>Crea models confs</td>
                            <td>
                                <select class="form-control" name="crea_modelsconfs">
                                    @foreach ($modelsConfs['options']['crea_modelsconfs'] as $optionKey => $optionValue)
                                        <option value="{{$optionKey}}">
                                            {!! $optionValue !!}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>


            <div class="col col-sm-12 text-center">
                <button class="btn btn-default" >
                    Avanti
                </button>
                <a href="/cupparis/modelskeleton/model" class="btn btn-danger">
                    Indietro
                </a>
            </div>

            <br/>

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
