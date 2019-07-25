define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'typeanalysis/index' + location.search,
                    add_url: 'typeanalysis/add',
                    edit_url: 'typeanalysis/edit',
                    del_url: 'typeanalysis/del',
                    multi_url: 'typeanalysis/multi',
                    table: 'typeanalysis',
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
                        {field: 'year', title: __('Year'), searchList:$.getJSON("typeanalysis/yearlist")},
                        {field: 'month', title: __('Month'),operate:'IN',addclass:'selectpage',data:'data-source="typeanalysis/monthlist" data-multiple="true"'},
                        {field: 'sourcetype_0', title: __('Sourcetype_0'), operate:false},
                        {field: 'sourcetype_1', title: __('Sourcetype_1'), operate:false},
                        {field: 'sourcetype_2', title: __('Sourcetype_2'), operate:false},
                        {field: 'sourcetype_3', title: __('Sourcetype_3'), operate:false},
                        {field: 'sourcetype_4', title: __('Sourcetype_4'), operate:false},
                        {field: 'sourcetype_5', title: __('Sourcetype_5'), operate:false},
                        {field: 'sourcetype_6', title: __('Sourcetype_6'),operate:false},
                        {field: 'sourcetype_7', title: __('Sourcetype_7'), operate:false},
                        {field: 'sourcetype_8', title: __('Sourcetype_8'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, buttons:[{
                            name: 'supplier_enginecabinet',
                            title: '机柜',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '机柜',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=0';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_intel',
                            title: '带宽',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '带宽',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=1';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_ip',
                            title: 'IP地址',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: 'IP地址',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=2';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_tran',
                            title: '传输',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '传输',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=3';
                            },
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_elect_fee',
                            title: '电费',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '电费',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=4';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_depart',
                            title: '物业费',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '物业费',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=5';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_highprotect',
                            title: '高防',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '高防',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=6';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_adsl',
                            title: 'ADSL',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: 'ADSL',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=7';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_other',
                            title: '其他',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '其他',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'typeanalysis/supplier?str='+row.year+'-'+row.month+'&type=8';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        }],formatter: Table.api.formatter.buttons}]
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