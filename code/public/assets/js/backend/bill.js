define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    $(document).on("click","#c-bill_unpaid",function () {
       var price=parseFloat($("#c-price").val());
        var paid=parseFloat($("#c-bill_paid").val());
        if(price<paid){
            Layer.msg('已付金额大于账单价格，请重新填写已付金额');
            $("#c-bill_paid").val(0);
            return false;
        }
        $("#c-price").val(price);
        $("#c-bill_paid").val(paid);
        var result=price-paid;
        if(result>=0){
            $("#c-bill_unpaid").val(result);
        }else {
            $("#c-bill_unpaid").val(0);
        }

    });
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bill/index' + location.search,
                    add_url: 'bill/add',
                    edit_url: 'bill/edit',
                    del_url: 'bill/del',
                    multi_url: 'bill/multi',
                    table: 'bill',
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
                        {field: 'bill_code', title: __('Bill_code'),operate:'LIKE'},
                        {field: 'supplier.name', title: __('Supplier.name'),operate:'LIKE'},
                        {field: 'contract.contract_no', title: __('Contract.contract_no'),operate:'LIKE'},
                        {field: 'price', title: __('Price'),sortable:true,operate:'BETWEEN'},
                        {field: 'currency', title: __('Currency'), searchList: {"1":__('Currency 1'),"2":__('Currency 2'),"3":__('Currency 3'),"4":__('Currency 4')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'paytitle', title: __('Paytitle'), searchList: {"1":__('Paytitle 1'),"2":__('Paytitle 2'),"3":__('Paytitle 3'),"4":__('Paytitle 4')}, formatter: Table.api.formatter.normal},
                        // {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        // {field: 'bill_paid', title: __('Bill_paid'), operate:'BETWEEN'},
                        // {field: 'bill_unpaid', title: __('Bill_unpaid'), operate:'BETWEEN'},
                        {field: 'verifyswitch', title: __('Verifyswitch'), searchList: {"2":'否决',"1":'通过',"0":'未查看'},formatter: function(value,row,index){
                                if(value===2){return '<i class="fa fa-circle" style="color:#e74c3c">否决</i>';}
                                if(value===1){return '<i class="fa fa-circle" style="color:#18bc9c">通过</i>';}
                                if(value===0){return '<i class="fa fa-circle" style="color:#d2d6de">未查看</i>';}
                            }},
                        {field: 'financialswitch', title: __('Financialswitch'), searchList: {"2":'否决',"1":'通过',"0":'未查看'},formatter: function(value,row,index){
                                if(value===2){return '<i class="fa fa-circle" style="color:#e74c3c">否决</i>';}
                                if(value===1){return '<i class="fa fa-circle" style="color:#18bc9c">通过</i>';}
                                if(value===0){return '<i class="fa fa-circle" style="color:#d2d6de">未查看</i>';}
                            }},
                        {field: 'status', title: __('Status'), searchList: {"normal":"已开","locked":"部分","hidden":"待开"}, formatter:Table.api.formatter.normal,sortable:true},
                       // {field: 'approveswitch', title: __('Approveswitch'), searchList: {"1":__('Yes'),"0":__('No')}, formatter: Table.api.formatter.toggle},
                        {field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'period_startdate', title: __('Period_startdate'), operate:'<=',addclass:'datetimepicker ', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'period_enddate', title: __('Period_enddate'), operate:'>=', addclass:'datetimepicker ', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'buttons', width: "120px", title: __('权限'),operate:false, table: table, events: Table.api.events.operate,buttons: [{
                                name: 'verify',
                                title: '使用者审核',
                                classname: 'btn btn-xs btn-primary btn-dialog',
                                text: '使用者审核',
                                icon: 'fa fa-check',
                                url: 'bill/verify',
                                hidden:function(row){
                                    if(row.verifyswitch>0||row.financialswitch>1){
                                        return true;
                                    }
                                }
                            },{
                                name: 'financialverify',
                                title: '财务审核',
                                classname: 'btn btn-xs btn-primary btn-dialog',
                                text: '财务审核',
                                icon: 'fa fa-check',
                                url: 'bill/financialverify',
                                //extend: ['data-area=[100px,200px]'],
                                hidden:function(row){
                                    //console.log(row);
                                    if(row.financialswitch>0||row.verifyswitch>1){
                                        return true;
                                    }
                                }
                            },],formatter: Table.api.formatter.buttons},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function (value, row, index) {
                        //     if(row.financialswitch>1||row.verifyswitch>1){
                        //         return '<a href="/admin/bill/edit/ids/'+ row["id"] + '" class="btn btn-xs btn-success btn-editone" title=""><i class="fa fa-pencil"></i></a>';
                        //         //return '<a href="/admin/bill/edit/ids/'+ row["id"] + '" class="btn btn-xs btn-success btn-editone" title=""><i class="fa fa-pencil"></i></a>  ' + '<a href="javascript:;" class="btn btn-xs btn-danger btn-delone" title=""><i class="fa fa-trash"></i></a>';
                        //     }
                        //     }},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Controller.api.operate}
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

        mylist: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bill/index' + location.search,
                    add_url: 'bill/add',
                    edit_url: 'bill/edit',
                    del_url: 'bill/del',
                    multi_url: 'bill/multi',
                    table: 'bill',
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
                        {field: 'bill_code', title: __('Bill_code'),operate:'LIKE'},
                        {field: 'supplier_id', title: __('Supplier.name'),searchList:JSON.parse(Config.supplierList), formatter: Table.api.formatter.normal},
                        {field: 'contract.contract_no', title: __('Contract.contract_no'),operate:'LIKE'},
                        {field: 'price', title: __('Price'),sortable:true,operate:'BETWEEN'},
                        {field: 'currency', title: __('Currency'), searchList: {"1":__('Currency 1'),"2":__('Currency 2'),"3":__('Currency 3'),"4":__('Currency 4')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'paytitle', title: __('Paytitle'), searchList: {"1":__('Paytitle 1'),"2":__('Paytitle 2'),"3":__('Paytitle 3'),"4":__('Paytitle 4')}, formatter: Table.api.formatter.normal},
                        // {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        // {field: 'bill_paid', title: __('Bill_paid'), operate:'BETWEEN'},
                        // {field: 'bill_unpaid', title: __('Bill_unpaid'), operate:'BETWEEN'},
                        {field: 'verifyswitch', title: __('Verifyswitch'), searchList: {"2":'否决',"1":'通过',"0":'未查看'},formatter: function(value,row,index){
                                if(value===2){return '<i class="fa fa-circle" style="color:#e74c3c">否决</i>';}
                                if(value===1){return '<i class="fa fa-circle" style="color:#18bc9c">通过</i>';}
                                if(value===0){return '<i class="fa fa-circle" style="color:#d2d6de">未查看</i>';}
                            }},
                        {field: 'financialswitch', title: __('Financialswitch'), searchList: {"2":'否决',"1":'通过',"0":'未查看'},formatter: function(value,row,index){
                                if(value===2){return '<i class="fa fa-circle" style="color:#e74c3c">否决</i>';}
                                if(value===1){return '<i class="fa fa-circle" style="color:#18bc9c">通过</i>';}
                                if(value===0){return '<i class="fa fa-circle" style="color:#d2d6de">未查看</i>';}
                            }},
                        {field: 'status', title: __('Status'), searchList: {"normal":"已开","locked":"部分","hidden":"待开"}, formatter:Table.api.formatter.normal,sortable:true},
                        // {field: 'approveswitch', title: __('Approveswitch'), searchList: {"1":__('Yes'),"0":__('No')}, formatter: Table.api.formatter.toggle},
                        {field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2')}, formatter: Table.api.formatter.normal,sortable:true},
                        {field: 'period_startdate', title: __('Period_startdate'), operate:'<=',addclass:'datetimepicker ', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'period_enddate', title: __('Period_enddate'), operate:'>=', addclass:'datetimepicker ', formatter: Table.api.formatter.datetime,sortable:true},
                        {field: 'buttons', width: "120px", title: __('权限'),operate:false, table: table, events: Table.api.events.operate,buttons: [{
                                name: 'verify',
                                title: '使用者审核',
                                classname: 'btn btn-xs btn-primary btn-dialog',
                                text: '使用者审核',
                                icon: 'fa fa-check',
                                url: 'bill/verify',
                                hidden:function(row){
                                    if(row.verifyswitch>0||row.financialswitch>1){
                                        return true;
                                    }
                                }
                            },{
                                name: 'financialverify',
                                title: '财务审核',
                                classname: 'btn btn-xs btn-primary btn-dialog',
                                text: '财务审核',
                                icon: 'fa fa-check',
                                url: 'bill/financialverify',
                                //extend: ['data-area=[100px,200px]'],
                                hidden:function(row){
                                    //console.log(row);
                                    if(row.financialswitch>0||row.verifyswitch>1){
                                        return true;
                                    }
                                }
                            },],formatter: Table.api.formatter.buttons},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function (value, row, index) {
                        //     if(row.financialswitch>1||row.verifyswitch>1){
                        //         return '<a href="/admin/bill/edit/ids/'+ row["id"] + '" class="btn btn-xs btn-success btn-editone" title=""><i class="fa fa-pencil"></i></a>';
                        //         //return '<a href="/admin/bill/edit/ids/'+ row["id"] + '" class="btn btn-xs btn-success btn-editone" title=""><i class="fa fa-pencil"></i></a>  ' + '<a href="javascript:;" class="btn btn-xs btn-danger btn-delone" title=""><i class="fa fa-trash"></i></a>';
                        //     }
                        //     }},
                        //{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Controller.api.operate}
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
                url: 'bill/recyclebin' + location.search,
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
                                    url: 'bill/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'bill/destroy',
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
        verify:function(){
            $(document).on("click","btn-verify",function () {
               Fast.api.close();
            });
            Controller.api.bindevent();
        },
        financialverify:function(){
            $(document).on("click","btn-financialverify",function () {
                Fast.api.close();
            });
            Controller.api.bindevent();
        },
        add: function () {
            $("#c-contract_id").data("params", function (obj) {
                return {custom: {supplier_id: $("#c-supplier_id").val()}};
            });

            Controller.api.bindevent();

        },
        edit: function () {
            $("#c-contract_id").data("params", function (obj) {
                return {custom: {supplier_id: $("#c-supplier_id").val()}};
            });
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            operate: function (value, row, index) {
                var table = this.table;
                // 操作配置
                var options = table ? table.bootstrapTable('getOptions') : {};
                // 默认按钮组
                var buttons = $.extend([], this.buttons || []);
                // 所有按钮名称
                var names = [];
                buttons.forEach(function (item) {
                    names.push(item.name);
                });
                if (options.extend.dragsort_url !== '' && names.indexOf('dragsort') === -1) {
                    buttons.push({
                        name: 'dragsort',
                        icon: 'fa fa-arrows',
                        title: __('Drag to sort'),
                        extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-primary btn-dragsort'
                    });
                }
                if(row.financialswitch>1||row.verifyswitch>1){
                if (options.extend.edit_url !== '' && names.indexOf('edit') === -1) {
                    buttons.push({
                        name: 'edit',
                        icon: 'fa fa-pencil',
                        title: '编辑',
                        extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-success btn-editone',
                        url: options.extend.edit_url
                    });
                }}
                return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
            }
        }
    };
    return Controller;
});