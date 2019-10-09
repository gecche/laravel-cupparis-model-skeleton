@extends('layouts/app')
@section('content')

    {{--@if (isset($post))--}}
    {{--<pre>--}}
        {{--{{print_r($post,true)}}--}}
    {{--</pre>--}}
    {{--@endif--}}

    <div>
        <form name="permissions" action="/superuser/migration2" method="post">
            {{ csrf_field() }}
        <div class="col col-sm-12 panel panel-default">
            <div class="panel-heading">
                <h2>Migrazione</h2>
            </div>
            <div class="panel-body">
                <div class="col col-sm-12">
                    Tabella {{$migration['nome_tabella']}}
                    <input class="form-control" type="hidden" name="nome_tabella"
                           value="{{$migration['nome_tabella']}}"
                    >
                </div>
                <div class="col col-sm-12">
                    Campi (id inserito automaticamente)
                </div>

                <table class="table table-responsive table-bordered">

                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Info</th>
                        <th>Nullable</th>
                        <th>Default</th>
                        <th>Index</th>
                        <th>Relazione tabella</th>
                        <th>Relazione campo</th>
                        <th>OnDelete</th>
                        <th>OnUpdate</th>
                    </tr>
                @foreach ($migration['campi'] as $nome_campo => $value_campo)
                    <tr>
                        <td>
                            {{$nome_campo}}
                            <input class="form-control" type="hidden" name="campi[{{$nome_campo}}][nome]"
                                   value=""
                            >
                        </td>
                        <td>
                            <select class="form-control" name="campi[{{$nome_campo}}][tipo]">
                                @foreach ($migration['options']['tipo_campi'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="form-control" type="text" name="campi[{{$nome_campo}}][info]" value=""/>
                        </td>
                        <td>
                            <select class="form-control" name="campi[{{$nome_campo}}][nullable]">
                                @foreach ($migration['options']['nullable'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="form-control" type="text" name="campi[{{$nome_campo}}][default]" value=""/>
                        </td>
                        <td>
                            <select class="form-control" name="campi[{{$nome_campo}}][index]">
                                @foreach ($migration['options']['index'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-control" name="campi[{{$nome_campo}}][relazione_tabella]">
                                @foreach ($migration['options']['tables'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="form-control" type="text" name="campi[{{$nome_campo}}][relazione_campo]" value="id"/>
                        </td>
                        <td>
                            <select class="form-control" name="campi[{{$nome_campo}}][ondelete]">
                                @foreach ($migration['options']['ondelete'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-control" name="campi[{{$nome_campo}}][onupdate]">
                                @foreach ($migration['options']['onupdate'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
                    <tr>
                        <td>Timestamps</td>
                        <td colspan="9">
                           <select class="form-control" name="campi[timestamps]">
                               @foreach ($migration['options']['timestamps'] as $optionKey => $optionValue)
                                <option value="{{$optionKey}}">
                                    {!! $optionValue !!}
                                </option>
                               @endforeach
                           </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Ownerships</td>
                        <td colspan="9">
                            <select class="form-control" name="campi[ownerships]">
                                @foreach ($migration['options']['ownerships'] as $optionKey => $optionValue)
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

            @include('superuser.includes.model')

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
            {{--@include('superuser.includes.modelconf', ['campi' => $migration['campi']])--}}


            <div class="col col-sm-12 text-center">
                <button class="btn btn-default" >
                    Avanti
                </button>
                <a href="/superuser/migration" class="btn btn-danger">
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

        $(function () {
        });

    </script>
@stop
