        <div class="col col-sm-12 panel panel-success">
                <div class="panel-heading">
                    <h2>ModelsConfs</h2>
                </div>
                <div class="panel-body">
                    <table class="table table-responsive table-bordered">
                        <tr>
                        <td>Nome file</td>
                        <td>
                            <input class="form-control" type="text" name="nome_file_modelsconfs"
                                   value="{{$modelsConfs['nome_file_modelsconfs']}}"
                            >
                        </td>
                    </tr>
                    </table>

                    <div class="col col-sm-12">
                        Campi Search
                    </div>

                    <table class="table table-responsive table-bordered">

                        <tr>
                            <th>Nome</th>
                            <th>Operatore</th>
                            <th>Type</th>
                        </tr>
                        @foreach ($modelsConfs['campi'] as $nome_campo => $value_campo)
                            <tr>
                                <td>
                                    <input class="form-control" type="text" name="modelsconfs-searchfields[nome][]"
                                           value="{{$nome_campo}}"
                                    >
                                </td>
                                <td>
                                    <select class="form-control" name="modelsconfs-searchfields[operator][]">
                                        @foreach ($modelsConfs['options']['search_operator'] as $optionKey => $optionValue)
                                            <option value="{{$optionKey}}" @if($optionKey == $value_campo['defaultConf']['search']['operator'])selected="selected"@endif>
                                                {!! $optionValue !!}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="modelsconfs-searchfields[type][]">
                                        @foreach ($modelsConfs['options']['search_types'] as $optionKey => $optionValue)
                                            <option value="{{$optionKey}}" @if($optionKey == $value_campo['defaultConf']['search']['type'])selected="selected"@endif>
                                                {!! $optionValue !!}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="col col-sm-12">
                        Campi List
                    </div>

                    <table class="table table-responsive table-bordered">

                        <tr>
                            <th>Nome</th>
                            <th>Type</th>
                            <th>Order</th>
                        </tr>
                        @foreach ($modelsConfs['campi'] as $nome_campo => $value_campo)
                            <tr>
                                <td>
                                    <input class="form-control" type="text" name="modelsconfs-listfields[nome][]"
                                           value="{{$nome_campo}}"
                                    >
                                </td>
                                <td>
                                    <select class="form-control" name="modelsconfs-listfields[type][]">
                                        @foreach ($modelsConfs['options']['list_types'] as $optionKey => $optionValue)
                                            <option value="{{$optionKey}}" @if($optionKey == $value_campo['defaultConf']['list']['type'])selected="selected"@endif>
                                                {!! $optionValue !!}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="modelsconfs-listfields[order][]">
                                        @foreach ($modelsConfs['options']['list_orders'] as $optionKey => $optionValue)
                                            <option value="{{$optionKey}}" @if($optionKey == $value_campo['defaultConf']['list']['order'])selected="selected"@endif>
                                                {!! $optionValue !!}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                            </tr>
                        @endforeach
                        @foreach ($modelsConfs['relazioni'] as $nome_campo => $value_campo)
                            <tr>
                                <td>
                                    <input class="form-control" type="text" name="modelsconfs-listfields[nome][]"
                                           value="{{$nome_campo}}"
                                    >
                                </td>
                                <td>
                                    <select class="form-control" name="modelsconfs-listfields[type][]">
                                        @foreach ($modelsConfs['options']['list_types'] as $optionKey => $optionValue)
                                            <option value="{{$optionKey}}" @if($optionKey == $value_campo['defaultConf']['list']['type'])selected="selected"@endif>
                                                {!! $optionValue !!}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="modelsconfs-listfields[order][]">
                                        @foreach ($modelsConfs['options']['list_orders'] as $optionKey => $optionValue)
                                            <option value="{{$optionKey}}" @if($optionKey == $value_campo['defaultConf']['list']['order'])selected="selected"@endif>
                                                {!! $optionValue !!}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                            </tr>
                        @endforeach
                    </table>

                    <div class="col col-sm-12">
                        Campi Edit
                    </div>

                    <table class="table table-responsive table-bordered">

                        <tr>
                            <th>Nome</th>
                            <th>Type</th>
                        </tr>
                        @foreach ($modelsConfs['campi'] as $nome_campo => $value_campo)
                            <tr>
                                <td>
                                    <input class="form-control" type="text" name="modelsconfs-editfields[nome][]"
                                           value="{{$nome_campo}}"
                                    >
                                </td>
                                <td>
                                    <select class="form-control" name="modelsconfs-editfields[type][]">
                                        @foreach ($modelsConfs['options']['edit_types'] as $optionKey => $optionValue)
                                            <option value="{{$optionKey}}" @if($optionKey == $value_campo['defaultConf']['edit']['type'])selected="selected"@endif>
                                                {!! $optionValue !!}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                            </tr>
                        @endforeach
                        @foreach ($modelsConfs['relazioni'] as $nome_campo => $value_campo)
                            <tr>
                                <td>
                                    <input class="form-control" type="text" name="modelsconfs-editfields[nome][]"
                                           value="{{$nome_campo}}"
                                    >
                                </td>
                                <td>
                                    <select class="form-control" name="modelsconfs-editfields[type][]">
                                        @foreach ($modelsConfs['options']['edit_types'] as $optionKey => $optionValue)
                                            <option value="{{$optionKey}}" @if($optionKey == $value_campo['defaultConf']['edit']['type'])selected="selected"@endif>
                                                {!! $optionValue !!}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>


    <script>

        jQuery(function () {
        });

    </script>
