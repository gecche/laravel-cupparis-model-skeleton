@extends($layoutView)
@section('content')

    {{--@if (isset($post))--}}
    {{--<pre>--}}
    {{--{{print_r($post,true)}}--}}
    {{--</pre>--}}
    {{--@endif--}}

    <div class="margin-top-20">

        <div class="panel panel-default col col-sm-12">

            <div class="panel-heading">
                <span class="title">Creazione automatica</span>
            </div>

            <div class="panel-body">
                <div class="col col-sm-12">
                    <div class="">
                        <h4>Scegli il tipo di creazione automatica</h4>
                    </div>
                </div>

                <hr/>

                <div class="col col-sm-12 text-center">
                    <div class="panel panel-success col col-sm-4 noborder">
                        <div class="panel-heading">
                            <a href="/cupparis/modelskeleton/migration">
                                Migrazione completa
                            </a>
                        </div>
                        <div class="panel-body">
                            Creazione di tabella, modello e modelconf
                        </div>
                    </div>
                    <div class="panel panel-warning col col-sm-4 noborder">
                        <div class="panel-heading">
                            <a href="/cupparis/modelskeleton/model">
                                Modello e modelconf
                            </a>
                        </div>
                        <div class="panel-body">
                            Creazione del modello e del modelconf a partire da una tabella già presente nel db
                        </div>
                    </div>
                    <div class="panel panel-danger col col-sm-4 noborder">
                        <div class="panel-heading">
                            <a href="/cupparis/modelskeleton/modelconf">
                                Solo modelconf
                            </a>
                        </div>
                        <div class="panel-body">
                            Creazione solo del modelconf a partire dal modello
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>

        jQuery(function () {

        });

    </script>
@stop
