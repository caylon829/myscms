<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Invoice extends Model
{

    use SoftDelete;

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'invoice';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'title_text',
        'type_text',
        'tax_text',
        'status_text',
        'state_text'
    ];
    

    
    public function getTitleList()
    {
        return ['t1' => __('Title t1'), 't2' => __('Title t2'), 't3' => __('Title t3'), 't4' => __('Title t4'), 't5' => __('Title t5')];
    }

    public function getTypeList()
    {
        return ['ty1' => __('Type ty1'), 'ty2' => __('Type ty2'), 'ty3' => __('Type ty3')];
    }

    public function getTaxList()
    {
        return ['tax1' => __('Tax tax1'), 'tax2' => __('Tax tax2')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStateList()
    {
        return ['0' => __('State 0'), '1' => __('State 1'), '2' => __('State 2')];
    }


    public function getTitleTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['title']) ? $data['title'] : '');
        $list = $this->getTitleList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTaxTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['tax']) ? $data['tax'] : '');
        $list = $this->getTaxList();
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
