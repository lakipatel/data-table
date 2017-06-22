<div class="row" id="{{ $uniqueID }}">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-sm-6">
                        @if( count($searchableColumns) > 0 )
                        <div class="input-group dt--global-search" style="width: 220px;">
                            <input name="search" class="form-control" type="search" placeholder="@lang('data-table::messages.search_placeholder')">
                            <a href="javascript:;" class="{{ config('data-table.search_btn') }}"><i class="{{ config('data-table.search_btn_icon') }}"></i></a>
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-6 text-right">
                        {{--@if( count($filters) > 0 )
                        <a class="{{ config('data-table.filter_btn') }}" href="javascript:;" title="@lang('data-table::messages.filter')"><i class="{{ config('data-table.filter_btn_icon') }}"></i></a>
                        @endif--}}

                        @if( count($downloadOptions) > 0 && (config('data-table.download_csv') || config('data-table.download_excel') || config('data-table.download_pdf')))
                        <a class="{{ config('data-table.download_btn') }} dt--download" href="javascript:;" title="@lang('data-table::messages.download')"><i class="{{ config('data-table.download_btn_icon') }}"></i></a>
                        @endif
                        {{--<a class="{{ config('data-table.show_hide_column_btn') }}" href="javascript:;" title="@lang('data-table::messages.show_hide_columns')"><i class="{{ config('data-table.show_hide_column_btn_icon') }}"></i></a>--}}
                        <a class="{{ config('data-table.refresh_btn') }} dt--reload-data" href="javascript:;" title="@lang('data-table::messages.reload_data')"><i class="{{ config('data-table.refresh_btn_icon') }}"></i></a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered dt--table">
                    <thead>
                        <tr>
                            @foreach($columns as $id => $label)
                                @php
                                $haveSorting = in_array($id, $sortableColumns);
                                $sortDirection = ($haveSorting && $id == $orderBy['sort'] ? strtolower($orderBy['sort_direction']) : false);
                                $sortIconClass = $sortDirection ? ($sortDirection == 'asc' ? 'glyphicon-sort-by-attributes' : 'glyphicon-sort-by-attributes-alt') : 'glyphicon-sort';
                                @endphp
                                <th data-column="{{ $id }}" class="column-{{ $id }} {{ $haveSorting ? 'dt--sorting' : '' }}{{ $sortDirection ? ' dt--sorting-' . $orderBy['sort_direction'] : '' }}" {!! $haveSorting ? ' style="cursor: pointer;"' : '' !!}>
                                    {!! $label !!}
                                    @if($haveSorting)
                                        <i class="glyphicon {{ $sortIconClass }} pull-right"></i>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                        @if( count($searchableColumns) > 0 )
                        <tr>
                            @foreach ($columns as $id => $title)
                                @if ( isset($searchableColumns[$id]) )
                                    <th class="column-search-{{ $id }}">
                                        @if( $searchableColumns[$id] == 'integer' )
                                            <input name="{{ $id }}" class="form-control" type="number" autocomplete="off" />
                                        @elseif( is_array($searchableColumns[$id]) )
                                            <select name="{{ $id }}" class="form-control input-sm">
                                                <option value="">All</option>
                                                @foreach( $searchableColumns[$id] as $val => $title )
                                                    <option value="{{ $val }}">{{ $title }}</option>
                                                @endforeach
                                            </select>
                                        @elseif( $searchableColumns[$id] == 'date' )
                                            <input name="{{ $id }}" placeholder="mm/dd/yyyy" class="form-control date-picker" type="text" autocomplete="off" />
                                        @else
                                            <input name="{{ $id }}" class="form-control" type="text" autocomplete="off" />
                                        @endif
                                    </th>
                                @else
                                    <th class="column-search-{{ $id }}"></th>
                                @endif
                            @endforeach
                        </tr>
                    @endif
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="{{ count($columns) }}" class="text-center">
                                <a class="{{ config('data-table.next_page_btn') }} pull-right dt--go-to-next" href="javascript:;" title="@lang('data-table::messages.next_page')" disabled><i class="{{ config('data-table.next_page_btn_icon') }}"></i></a>
                                <a class="{{ config('data-table.previous_page_btn') }} pull-right dt--go-to-previous" style="margin-right: 5px;" href="javascript:;" title="@lang('data-table::messages.previous_page')" disabled><i class="{{ config('data-table.previous_page_btn_icon') }}"></i></a>
                                <div class="pull-right" style="margin-top: 5px; margin-right: 10px">
                                    @lang('data-table::messages.go_to_page') <input type="number" min="1" max="1" value="1" size="3" width="10px" class="text-center dt--go-to-page"> @lang('data-table::messages.of') <span class="dt--total-pages">0</span>
                                </div>
                                <div class="pull-left dt--pagination-info" style="margin-top: 5px;" data-trans="@lang('data-table::messages.pagination_info')">@lang('data-table::messages.pagination_info', ['from' => 0, 'to' => 0, 'total' => 0])</div>
                                <div style="margin-top: 5px;">
                                    @lang('data-table::messages.show') <select class="dt--show-entries-options">
                                        @foreach($rowPerPageOptions as $rowPerPageOptions)
                                            <option value="{{ $rowPerPageOptions }}"{!! $rowPerPageOptions == $rowPerPage ? ' selected="selected"' : '' !!}>{{ $rowPerPageOptions }}</option>
                                        @endforeach
                                    </select> @lang('data-table::messages.entries')
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

    </div>
</div>

@push('scripts')
<script>
    $('#{{ $uniqueID }}').dataTable({
        uniqueID: '{{ $uniqueID }}',
        ajaxURI: '{{ $ajaxDataURI }}',
        columns: JSON.parse('{!! json_encode($columns) !!}'),
        rowPerPage: '{{ $rowPerPage }}',
        sortBy: '{{ $orderBy['sort'] }}',
        sortByDirection: '{{ $orderBy['sort_direction'] }}',
        orderBy: JSON.parse('{!! json_encode($orderBy) !!}'),
        loadingTxt: '{!! config('data-table.loading_txt') !!}'
    });
</script>
@endpush