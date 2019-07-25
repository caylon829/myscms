<?php

namespace app\admin\model;

use think\Model;


class UseLog extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'admin_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function getContentAttr($value, $data)
    {
        $str=[
            'paytype'  =>['open_bank'=>'开户银行','bank'=>'付款银行','pay'=>'付款账户'],
            'bill'     =>['bill_code'   => '账单编号'],
            'invoice'  =>['no'   => '发票号'],
            'contract' =>['contract_no'  => '自有合同编号'],
            'supplier' =>['name' => '供应商名称'],
            'payment'  =>['bill_id'=>'付款id']
        ];
        $value = \GuzzleHttp\json_decode($value,true);
        $result='';
        foreach ($value['row'] as $kk=>$vv){
            if(isset($value['action'])&&isset($str[$value['action']])&&isset($str[$value['action']][$kk]))
            $result.=$str[$value['action']][$kk].':'.$vv.'/';
        }
        return rtrim($result, "/");
    }
    







}
