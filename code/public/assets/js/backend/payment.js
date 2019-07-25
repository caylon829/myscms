define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'payment/index' + location.search,
                    add_url: 'payment/add',
                    edit_url: 'payment/edit',
                    del_url: 'payment/del',
                    multi_url: 'payment/multi',
                    table: 'payment',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'supplier.name', title: __('Supplier.name'), operate:'LIKE'},
                        {field: 'contract.contract_no', title: __('Contract.contract_no'), operate:'LIKE'},
                        {field: 'bill.bill_code', title: __('Bill.bill_code'), operate:'LIKE'},
                        {field: 'currency', title: __('Currency'), searchList: {"1":__('Currency 1'),"2":__('Currency 2'),"3":__('Currency 3'),"4":__('Currency 4')}, formatter: Table.api.formatter.normal},
                        {field: 'price', title: __('Price'), operate:'BETWEEN',sortable:true},
                        {field: 'paydate', title: __('Paydate'), operate:'RANGE', addclass:'datetimerange',sortable:true},
                        {field: 'paystatus', title: __('Paystatus'), searchList: {"1":__('Paystatus 1'),"2":__('Paystatus 2'),"3":__('Paystatus 3')}, formatter: Table.api.formatter.status,sortable:true},
                        {field: 'payimage', title: __('Payimage'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'state', title: __('State'), searchList: {"1":__('State 1'),"2":__('State 2'),"3":__('State 3'),"4":__('State 4')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                //禁用默认搜索
                search: false,
                showColumns: false,
                showToggle:false,
                showExport:false,
                //启用普通表单搜索
                commonSearch: true,
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: true,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'payment/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'payment/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'payment/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            $("input[id^='c-bill_id']").data("params", function (obj) {
                return {custom: {supplier_id: $("#c-supplier_id").val()}};
            });
            $("#c-payaccount_id").data("params", function (obj) {
                return {custom: {supplier_id: $("#c-supplier_id").val()}};
            });
            Controller.api.bindevent();
            $('.btn-append').click(function () {
                var last_child=$(".append:last");
                var index=last_child.data('listidx');
                var the_index=index+1;
                var html='<div class="form-group"><label class="control-label col-xs-12 col-sm-2">账单编号:</label> <div class="col-xs-12 col-sm-8"> <input id="c-bill_id_'+the_index+'" data-rule="required" data-field="bill_code" data-source="bill/index" class="form-control selectpage" name="row[bill_id2]['+the_index+']" type="text" value=""> </div> </div><div class="form-group"> <label class="control-label col-xs-12 col-sm-2">价格:</label> <div class="col-xs-12 col-sm-8"> <input id="c-price_'+the_index+'" data-rule="required" class="form-control" step="0.01" name="row[price2]['+the_index+']" type="number" value=""> </div> </div><input type="hidden" class="append" data-listidx="'+the_index+'">';
                last_child.after(html);
                $("input[id^='c-bill_id']").data("params", function (obj) {
                    return {custom: {supplier_id: $("#c-supplier_id").val()}};
                });
                Controller.api.bindevent();
            });
        },
        edit: function () {
            $("input[id^='c-bill_id']").data("params", function (obj) {
                return {custom: {supplier_id: $("#c-supplier_id").val()}};
            });
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});