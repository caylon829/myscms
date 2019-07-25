define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'contract/index' + location.search,
                    add_url: 'contract/add',
                    edit_url: 'contract/edit',
                    import_url:'contract/import',
                    del_url: 'contract/del',
                    multi_url: 'contract/multi',
                    table: 'contract',
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
                        {field: 'contract_aname', title: __('Contract_aname'),operate:'Like'},
                        {field: 'supplier_id', title: __('Supplier_id'),searchList:JSON.parse(Config.supplierList), formatter: Table.api.formatter.normal},
                        {field: 'contract_no', title: __('Contract_no'),operate:'Like'},
                        {field: 'supplier_no', title: __('Supplier_no'),operate:'Like'},
                        {field: 'startdate', title: __('Startdate'), operate:'RANGE', addclass:'datetimerange',sortable:true},
                        {field: 'user_id', title: __('User_id'),operate:'Like'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN',sortable:true},
                        {field: 'currency', title: __('Currency'), searchList: {"1":__('Currency 1'),"2":__('Currency 2'),"3":__('Currency 3'),"4":__('Currency 4')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'enddate', title: __('Enddate'), operate:'RANGE',formatter:function (value,row,index) {
                            var time1=new Date();
                            var time2=new Date(value.replace(/-/g,"\/"));
                            //console.log(value.replace(/-/g,"/"));
                            if(time1>time2){
                                return "<span style='color:red;font-weight: bold'>"+row.enddate+"</span>";
                            }else{
                                return "<span style='color:green;font-weight: bold'>"+row.enddate+"</span>";
                            }

                            },sortable:true},
                        {field: 'servicelevel', title: __('Servicelevel'),operate:false},
                        {field: 'service', title: __('Service'),operate:false},
                        {field: 'dept', title: __('Dept'),operate:false},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image,operate:false},
                        {field: 'contact_person', title: __('Contact_person'),operate:false},
                        {field: 'contact', title: __('Contact'),operate:false},
                        {field: 'sub_contractor', title: __('Sub_contractor'),operate:false},
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

        mylist: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'contract/index' + location.search,
                    import_url:'contract/import',
                    add_url: 'contract/add',
                    edit_url: 'contract/edit',
                    del_url: 'contract/del',
                    multi_url: 'contract/multi',
                    table: 'contract',
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
                        {field: 'contract_aname', title: __('Contract_aname'),operate:'Like'},
                        {field: 'supplier_id', title: __('Supplier_id'),searchList:JSON.parse(Config.supplierList), formatter: Table.api.formatter.normal},
                        {field: 'contract_no', title: __('Contract_no'),operate:'Like'},
                        {field: 'supplier_no', title: __('Supplier_no'),operate:'Like'},
                        {field: 'startdate', title: __('Startdate'), operate:'RANGE', addclass:'datetimerange',sortable:true},
                        {field: 'user_id', title: __('User_id'),operate:'Like'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN',sortable:true},
                        {field: 'currency', title: __('Currency'), searchList: {"1":__('Currency 1'),"2":__('Currency 2'),"3":__('Currency 3'),"4":__('Currency 4')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'enddate', title: __('Enddate'), operate:'RANGE',formatter:function (value,row,index) {
                                var time1=new Date();
                                var time2=new Date(value.replace(/-/g,"\/"));
                                //console.log(value.replace(/-/g,"/"));
                                if(time1>time2){
                                    return "<span style='color:red;font-weight: bold'>"+row.enddate+"</span>";
                                }else{
                                    return "<span style='color:green;font-weight: bold'>"+row.enddate+"</span>";
                                }

                            },sortable:true},
                        {field: 'servicelevel', title: __('Servicelevel'),operate:false},
                        {field: 'service', title: __('Service'),operate:false},
                        {field: 'dept', title: __('Dept'),operate:false},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image,operate:false},
                        {field: 'contact_person', title: __('Contact_person'),operate:false},
                        {field: 'contact', title: __('Contact'),operate:false},
                        {field: 'sub_contractor', title: __('Sub_contractor'),operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        //{field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2')}, formatter: Table.api.formatter.normal},
                        //{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
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
                url: 'contract/recyclebin' + location.search,
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
                                    url: 'contract/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'contract/destroy',
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