define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'invoice/index' + location.search,
                    add_url: 'invoice/add',
                    edit_url: 'invoice/edit',
                    del_url: 'invoice/del',
                    multi_url: 'invoice/multi',
                    table: 'invoice',
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
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'startdate', title: __('Startdate'), operate:'RANGE', addclass:'datetimerange',sortable:true},
                        {field: 'supplier.name', title: __('Supplier.name'),operate:'LIKE'},
                        {field: 'contract.contract_no', title: __('Contract.contract_no'),operate:'LIKE'},
                        {field: 'bill.bill_code', title: __('Bill.bill_code'),operate:'LIKE'},
                        {field: 'title', title: __('Title'), searchList: {"t1":__('Title t1'),"t2":__('Title t2'),"t3":__('Title t3'),"t4":__('Title t4'),"t5":__('Title t5')}, formatter: Table.api.formatter.normal},
                        {field: 'price', title: __('Price'), operate:'BETWEEN',sortable:true},
                        {field: 'no', title: __('No'),operate:'LIKE'},
                        {field: 'type', title: __('Type'), searchList: {"ty1":__('Type ty1'),"ty2":__('Type ty2'),"ty3":__('Type ty3')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'tax', title: __('Tax'), searchList: {"tax1":__('Tax tax1'),"tax2":__('Tax tax2')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'incomedate', title: __('Incomedate'), operate:'RANGE', addclass:'datetimerange',sortable:true},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image,operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        //{field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2')}, formatter: Table.api.formatter.normal},
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
                url: 'invoice/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
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
                                    url: 'invoice/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'invoice/destroy',
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
            $("#c-bill_id").data("params", function (obj) {
                return {custom: {supplier_id: $("#c-supplier_id").val()}};
            });
            Controller.api.bindevent();
        },
        edit: function () {
            $("#c-bill_id").data("params", function (obj) {
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