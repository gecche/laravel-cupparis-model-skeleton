
<div class="col col-sm-12 panel panel-info">
            <div class="panel-heading">
                <h2>Modello</h2>
            </div>
            <div class="panel-body">
                <table class="table table-responsive table-bordered">
                    <tr>
                        <td>Crea modello</td>
                        <td colspan="5">
                            <select class="form-control" name="crea_modello">
                                @foreach ($model['options']['crea_modello'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Nome classe</td>
                        <td colspan="5">
                            <input class="form-control" type="text" name="nome_modello"
                                   value="{{$model['name']}}"
                            >
                        </td>
                    </tr>
                    <tr>
                        <td>Traduzione (singulare|plurale)</td>
                        <td colspan="2">
                            <input class="form-control" type="text" name="lang_modello_singolare"
                                   value="{{snake_case($model['name'])}}"
                            >
                        </td>
                        <td colspan="3">
                            <input class="form-control" type="text" name="lang_modello_plurale"
                                   value="{{snake_case($model['name'])}}"
                            >
                        </td>
                    </tr>
                    {{--<tr>--}}
                        {{--<td>Traits</td>--}}
                        {{--<td colspan="5">--}}
                            {{--<select class="form-control" name="traits[]" multiple="multiple">--}}
                                {{--@foreach ($model['options']['traits'] as $optionKey => $optionValue)--}}
                                    {{--<option value="{{$optionKey}}">--}}
                                        {{--{!! $optionValue !!}--}}
                                    {{--</option>--}}
                                {{--@endforeach--}}
                            {{--</select>--}}
                        {{--</td>--}}
                    {{--</tr>--}}
                    <tr>
                        <td>Columns for select list</td>
                        <td colspan="5">
                            <select class="form-control" name="columns_for_select_list[]" multiple="multiple">
                                @foreach ($model['options']['campi'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Columns for autocomplete</td>
                        <td colspan="5">
                            <select class="form-control" name="columns_for_autocomplete[]" multiple="multiple">
                                @foreach ($model['options']['campi'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Columns for default order</td>
                        <td colspan="">
                            <select class="form-control" name="columns_for_default_order[]">
                                @foreach ($model['options']['campi'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td colspan="">
                            <select class="form-control" name="columns_for_default_order_direction[]">
                                @foreach ($model['options']['order'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionKey !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td colspan="3">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="">
                            <select class="form-control" name="columns_for_default_order[]">
                                @foreach ($model['options']['campi'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td colspan="">
                            <select class="form-control" name="columns_for_default_order_direction[]">
                                @foreach ($model['options']['order'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionKey !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td colspan="3">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="">
                            <select class="form-control" name="columns_for_default_order[]">
                                @foreach ($model['options']['campi'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionValue !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td colspan="">
                            <select class="form-control" name="columns_for_default_order_direction[]">
                                @foreach ($model['options']['order'] as $optionKey => $optionValue)
                                    <option value="{{$optionKey}}">
                                        {!! $optionKey !!}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td colspan="3">
                            &nbsp;
                        </td>
                    </tr>


                    <tr>
                        <td colspan="7">Relazioni</td>
                    </tr>
                    <tr>
                        <td>Nome</td>
                        <td>Tipo</td>
                        <td>Modello</td>
                        <td>Tabella</td>
                        <td>ForeignKey</td>
                        <td>OtherKey</td>
                        <td>PivotKey</td>
                    </tr>
                    @foreach ([1,2,3,4,5,6] as $n)

                        <tr>
                            <td colspan="">
                                <input class="form-control" type="text" name="relation_names[]"
                                       value=""
                                >
                            </td>
                            <td colspan="">
                                <select class="form-control" name="relation_types[]">
                                    @foreach ($model['options']['relations-types'] as $optionKey => $optionValue)
                                        <option value="{{$optionKey}}">
                                            {!! $optionValue !!}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td colspan="">
                                <select class="form-control" name="relation_models[]">
                                    @foreach ($model['options']['relations-models'] as $optionKey => $optionValue)
                                        <option value="{{$optionKey}}">
                                            {!! $optionValue !!}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td colspan="">
                                <select class="form-control" name="relation_tables[]">
                                    @foreach ($model['options']['tables'] as $optionKey => $optionValue)
                                        <option value="{{$optionKey}}">
                                            {!! $optionValue !!}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td colspan="">
                                <input class="form-control" type="text" name="relation_foreignkey[]"
                                       value=""
                                >
                            </td>
                            <td colspan="">
                                <input class="form-control" type="text" name="relation_otherkey[]"
                                       value=""
                                >
                            </td>
                            <td colspan="">
                                <input class="form-control" type="text" name="relation_pivotkey[]"
                                       value=""
                                >
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
