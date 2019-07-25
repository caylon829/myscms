define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'supplier/index' + location.search,
                    add_url: 'supplier/add',
                    edit_url: 'supplier/edit',
                    del_url: 'supplier/del',
                    multi_url: 'supplier/multi',
                    table: 'supplier',
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
                        {field: 'name', title: __('Name'),operate:'LIKE'},
                        {field: 'level', title: __('Level'), searchList: {"c1":__('Level c1'),"c2":__('Level c2'),"c3":__('Level c3'),"c4":__('Level c4')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'enterdate', title: __('Enterdate'), operate:'RANGE', addclass:'datetimerange',sortable:true},
                        {field: 'startdate', title: __('Startdate'), operate:'RANGE', addclass:'datetimerange',sortable:true},
                        {field: 'enddate', title: __('Enddate'), operate:'RANGE', addclass:'datetimerange',sortable:true},
                        {field: 'linkman', title: __('Linkman'),operate:'LIKE'},
                        {field: 'contact', title: __('Contact'),operate:false},
                        {field: 'secretswitch', title: __('Secretswitch'), searchList: {"1":__('Yes'),"0":__('No')}, formatter: Table.api.formatter.toggle},
                        {field: 'keepswitch', title: __('Keepswitch'), searchList: {"1":__('Yes'),"0":__('No')}, formatter: Table.api.formatter.toggle},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image
                            , operate: false
                        },
                        //{field: 'refreshtime', title: __('Refreshtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        //{field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        //{field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        //{field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        //{field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate
                        ,
                            buttons: [
                                //{name: 'addpaytype', text: '添加付款账号', title: '添加付款账号', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'page/detail'},
                                {name: 'paytype', text: '付款账号', title: '付款账号', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'paytype/mylist/supplier_id/{ids}'},

                                {name: 'contract', text: '合同', title: '合同信息', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'contract/mylist/supplier_id/{ids}'},
                                {name: 'detail', text: '账单', title: '账单信息', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'bill/mylist/supplier_id/{ids}'},
                                //{name: 'detail', text: '历史', title: '历史信息', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'contract/mylist/supplier_id/{ids}'}
                            ],
                        }
                    ]
                ],

                //禁用默认搜索
                search: false,
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
                url: 'supplier/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), align: 'left'},
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
                                    url: 'supplier/restore',
                                    refresh: true
                                },

                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'supplier/destroy',
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