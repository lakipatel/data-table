(function($) {

    $.dataTable = function(element, options) {

        var defaults = {
            uniqueID: '',
            ajaxURI: '',
            columns: [],
            rowPerPage: 20,
            sortBy: '',
            sortByDirection: '',
            loadingTxt: 'Loading...'
        };

        var $this = this;

        $this.opt = {};

        var $element = $(element),
            element = element,
            queryData = {action: 'list', page:1, limit: 1, filters:''},
            currentDataSet = {total_pages: 1, current_page: 1};

        var setPaginationInfo = function() {
            var paginationInfoEle = $element.find('.dt--pagination-info');
            var paginationInfoTrans = paginationInfoEle.data('trans');
            paginationInfoTrans = paginationInfoTrans.replace(':from', currentDataSet.from);
            paginationInfoTrans = paginationInfoTrans.replace(':to', currentDataSet.to);
            paginationInfoTrans = paginationInfoTrans.replace(':total', currentDataSet.total_records);
            paginationInfoEle.text(paginationInfoTrans);

            $element.find('.dt--total-pages').text(currentDataSet.total_pages);
            $element.find('.dt--go-to-page').val(currentDataSet.current_page);
            $element.find('.dt--go-to-page').attr('max', currentDataSet.total_pages);

            if(currentDataSet.current_page < currentDataSet.total_pages) {
                $element.find('.dt--go-to-next').removeAttr('disabled');
            } else {
                $element.find('.dt--go-to-next').attr('disabled', 'disabled');
            }

            if(currentDataSet.current_page > 1) {
                $element.find('.dt--go-to-previous').removeAttr('disabled');
            } else {
                $element.find('.dt--go-to-previous').attr('disabled', 'disabled');
            }
        };

        var dataRowHTML = function() {

            var tableRows = '';

            if(currentDataSet.data.length > 0) {
                for(var i = 0; i < currentDataSet.data.length; i++) {
                    tableRows += '<tr>';
                    for(var x in $this.opt.columns) {
                        if(typeof currentDataSet.data[i][x] == 'undefined') {
                            tableRows += '<td>N/A</td>';
                        } else {
                            tableRows += '<td>'+ currentDataSet.data[i][x] +'</td>';
                        }
                    }
                    tableRows += '</tr>';
                }
            } else {
                tableRows = '<tr><td align="center" colspan="'+Object.keys($this.opt.columns).length+'">No data found.</td></tr>';
            }
            $element.find('.dt--table tbody').html(tableRows);
        };

        var getData = function() {
            $element.find('.dt--table tbody').html('<tr><td class="text-center" style="height: 100px; vertical-align: middle;" colspan="' + Object.keys($this.opt.columns).length + '">'+$this.opt.loadingTxt+'</td></tr>');

            var filters = '';
            if(queryData.action == 'filter') {
                filters = '&'+queryData.filters;
            }

            $.get($this.opt.ajaxURI + '?' + $.param(queryData)+filters, function(res) {
                if(res.status == false) {
                    alert( res.message );
                } else if(res.status == true) {
                    currentDataSet = res.data;

                    if(currentDataSet.current_page > currentDataSet.total_pages && currentDataSet.total_pages > 0) {
                        queryData.page = currentDataSet.total_pages;
                        getData();
                        return;
                    }

                    setPaginationInfo();
                    dataRowHTML();

                }
            }, 'json').fail(function() {
                alert( 'Something went wrong.' );
            });
        };

        var initListing = function() {
            queryData = {action: 'list', page:1, limit: $this.opt.rowPerPage, filters:''};
            if($this.opt.sortBy.length > 0 && $this.opt.sortByDirection.length > 2) {
                queryData.sort_by = $this.opt.sortBy;
                queryData.sort_by_direction = $this.opt.sortByDirection;
            }
            getData();
        };

        var searchOnColumns = function() {
            queryData.page = 1;
            queryData.action = 'search-columns';
            queryData.filters = {};

            var searchableColumns = $element.find('.dt--table thead tr').last().find('input, select').serializeArray();
            for(var i in searchableColumns) {
                if(searchableColumns[i].value.length > 0) {
                    queryData.filters[searchableColumns[i].name] = searchableColumns[i].value;
                }
            }

            if(Object.keys(queryData.filters).length < 1) {
                initListing();
            } else {
                getData();
            }
            $this.resetSearch('column');
        };

        var sorting = function() {
            var sortType = $(this).hasClass('dt--sorting-asc') ? 'desc' : 'asc';
            $element.find('.dt--sorting').removeClass('dt--sorting-asc dt--sorting-desc');
            $element.find('.dt--sorting > i.glyphicon').addClass('glyphicon-sort').removeClass('glyphicon-sort-by-attributes glyphicon-sort-by-attributes-alt');
            if(sortType == 'asc') {
                $(this).addClass('dt--sorting-asc');
                $(this).find('.glyphicon').addClass('glyphicon-sort-by-attributes').removeClass('glyphicon-sort');
            } else {
                $(this).addClass('dt--sorting-desc');
                $(this).find('.glyphicon').addClass('glyphicon-sort-by-attributes-alt').removeClass('glyphicon-sort');
            }
            queryData.sort_by = $(this).data('column');
            queryData.sort_by_direction = sortType;
            getData();
        };

        var globalSearch = function(e) {
            if(e.type == 'keyup' && e.which != 13) {
                return;
            }
            $this.resetSearch('global');

            var searchKeyword = $element.find('.dt--global-search input[name=search]').val();

            if(searchKeyword.length < 1) {
                initListing();
            } else {
                queryData.page = 1;
                queryData.action = 'search';
                queryData.filters = searchKeyword;
                getData();
            }
        };

        var bind = function() {

            $element.find('.dt--reload-data').click($this.reload);

            $element.find('.dt--go-to-next').click($this.nextPage);

            $element.find('.dt--go-to-previous').click($this.previousPage);

            $element.find('.dt--go-to-page').keypress(function(e) {
                if(e.which == 13) {
                    $this.jumpToPage($(this).val());
                }
            });

            $element.find('.dt--show-entries-options').change(function() {
                var perPage = parseInt($(this).val());
                if(perPage > 0) {
                    queryData.limit = perPage;
                    getData();
                }
            });

            $element.find('.dt--table thead tr').last().find('input, select').change(searchOnColumns);

            $element.find('.dt--sorting').click(sorting);

            $element.find('.dt--global-search input[name=search]').keyup(globalSearch);
            $element.find('.dt--global-search a').click(globalSearch);

            $element.find('.dt--download').click($this.download);

            initListing();
        };

        $this.download = function(){
            var filters = '';
            if(queryData.action == 'filter') {
                filters = '&'+queryData.filters;
            }
            window.open($this.opt.ajaxURI + '?' + $.param(queryData)+filters+'&download=csv');
        };

        $this.jumpToPage = function(pageNo) {
            if(pageNo > 0 && currentDataSet.total_pages >= pageNo && pageNo != currentDataSet.current_page) {
                queryData.page = pageNo;
                getData();
            } else {
                $element.find('.dt--go-to-page').val(currentDataSet.current_page);
            }
        };

        $this.nextPage = function() {
            $this.jumpToPage( parseInt(currentDataSet.current_page) + 1 );
        };

        $this.previousPage = function() {
            $this.jumpToPage( parseInt(currentDataSet.current_page) - 1 );
        };

        $this.resetSearch = function(except) {
            if(except !== 'global') {
                $element.find('.dt--global-search input[name=search]').val('');
            }

            if(except !== 'column') {
                $element.find('.dt--table thead tr').last().find('input, select').each(function() {
                    $(this).val('');
                });
            }
        };

        $this.reload = function() {
            getData();
        };

        var init = function() {
            $this.opt = $.extend({}, defaults, options);
            bind();
        };

        init();

    };

    $.fn.dataTable = function(options) {

        var arg = arguments;

        return this.each(function() {
            var data = $(this).data('dataTable');
            if (undefined == data) {
                data = new $.dataTable(this, options);
                $(this).data('dataTable', data);
            }

            if (typeof options === 'string') {
                if (arg.length > 1) {
                    data[options].apply(data, Array.prototype.slice.call(arg, 1));
                } else {
                    data[options]();
                }
            }
        });
    }

})(jQuery);