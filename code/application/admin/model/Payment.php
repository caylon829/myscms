<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Payment extends Model
{

    use SoftDelete;

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'payment';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'currency_text',
        'paystatus_text',
        'status_text',
        'state_text'
    ];
    

    
    public function getCurrencyList()
    {
        return ['1' => __('Currency 1'), '2' => __('Currency 2'), '3' => __('Currency 3'), '4' => __('Currency 4')];
    }

    public function getPaystatusList()
    {
        return ['1' => __('Paystatus 1'), '2' => __('Paystatus 2'), '3' => __('Paystatus 3')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStateList()
    {
        return ['1' => __('State 1'), '2' => __('State 2'), '3' => __('State 3'), '4' => __('State 4')];
    }


    public function getCurrencyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['currency']) ? $data['currency'] : '');
        $list = $this->getCurrencyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPaystatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['paystatus']) ? $data['paystatus'] : '');
        $list = $this->getPaystatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['state']) ? $data['state'] : '');
        $list = $this->getStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function supplier()
    {
        return $this->belongsTo('Supplier', 'supplier_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function contract()
    {
        return $this->belongsTo('Contract', 'contract_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function bill()
    {
        return $this->belongsTo('Bill', 'bill_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
