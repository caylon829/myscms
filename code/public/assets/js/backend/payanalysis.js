define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'payanalysis/index' + location.search,
                    add_url: 'payanalysis/add',
                    edit_url: 'payanalysis/edit',
                    del_url: 'payanalysis/del',
                    multi_url: 'payanalysis/multi',
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
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'supplier.name', title: __('Supplier_id'),operate:false},
                        {field: 'supplier.id', title: __('Supplier_id'), formatter: Table.api.formatter.normal,operate:'IN',addclass:'selectpage',data:'data-source="supplier/index" data-multiple="true"',visible:false},
                        {field: 'bill.bill_code', title: __('Bill_id'),operate:false},
                        {field: 'bill.period', title: __('Period'),operate:false,sortable:true},
                        {field: 'price', title: __('Price'), operate:"BETWEEN"},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, buttons:[{
                            name: 'scan',
                            title: '详细',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '详细',
                            icon: 'fa fa-list',
                            url: function (row,column) {
                                return 'payanalysis/detail?str='+row.bill_id+'-'+row.payaccount_id;
                            }
                            //extend: ['data-area=[100px,200px]'],
                        }],formatter: Table.api.formatter.buttons}
                    ]
                ],
                //禁用默认搜索
                search: false,
                showColumns: false,
                showToggle:false,
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
                url: 'payanalysis/recyclebin' + location.search,
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
                                    url: 'payanalysis/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'payanalysis/destroy',
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
            Controller.api.bindevent();
        },
        edit: function () {
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