define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cosanalysis/index' + location.search,
                    add_url: 'cosanalysis/add',
                    edit_url: 'cosanalysis/edit',
                    del_url: 'cosanalysis/del',
                    multi_url: 'cosanalysis/multi',
                    table: 'cosanalysis',
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
                        {field: 'year', title: __('Year'), searchList:$.getJSON("cosanalysis/yearlist")},
                        {field: 'month', title: __('Month'),operate:'IN',addclass:'selectpage',data:'data-source="cosanalysis/monthlist" data-multiple="true"'},
                        {field: 'purpose_0', title: __('Purpose_0'), operate:false},
                        {field: 'purpose_1', title: __('Purpose_1'), operate:false},
                        {field: 'purpose_2', title: __('Purpose_2'), operate:false},
                        {field: 'purpose_3', title: __('Purpose_3'), operate:false},
                        {field: 'purpose_4', title: __('Purpose_4'), operate:false},
                        {field: 'purpose_5', title: __('Purpose_5'), operate:false},
                        {field: 'purpose_6', title: __('Purpose_6'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, buttons:[{
                                name: 'supplier_idc',
                                title: 'IDC',
                                classname: 'btn btn-xs btn-primary btn-dialog',
                                text: 'IDC',
                                icon: 'fa fa-check',
                                url: function (row,column) {
                                    return 'cosanalysis/supplier?str='+row.year+'-'+row.month+'&type=0';
                                }
                                //extend: ['data-area=[100px,200px]'],
                            },{
                            name: 'supplier_yun',
                            title: '云平台',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '云平台',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'cosanalysis/supplier?str='+row.year+'-'+row.month+'&type=1';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_idc_yun',
                            title: 'IDC&云',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: 'IDC&云',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'cosanalysis/supplier?str='+row.year+'-'+row.month+'&type=2';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_dia',
                            title: 'DIA',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: 'DIA',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'cosanalysis/supplier?str='+row.year+'-'+row.month+'&type=3';
                            },
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_elect_fee',
                            title: '电费',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '电费',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'cosanalysis/supplier?str='+row.year+'-'+row.month+'&type=4';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_depart',
                            title: '物业',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '物业',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'cosanalysis/supplier?str='+row.year+'-'+row.month+'&type=5';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        },{
                            name: 'supplier_other',
                            title: '其他',
                            classname: 'btn btn-xs btn-primary btn-dialog',
                            text: '其他',
                            icon: 'fa fa-check',
                            url: function (row,column) {
                                return 'cosanalysis/supplier?str='+row.year+'-'+row.month+'&type=6';
                            }
                            //extend: ['data-area=[100px,200px]'],
                        }],formatter: Table.api.formatter.buttons}
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