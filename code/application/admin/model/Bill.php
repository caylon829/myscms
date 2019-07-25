<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Bill extends Model
{

    use SoftDelete;

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'bill';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'paytitle_text',
        'status_text',
        'state_text'
    ];
    

    
    public function getPaytitleList()
    {
        return ['1' => __('Paytitle 1'), '2' => __('Paytitle 2'), '3' => __('Paytitle 3'), '4' => __('Paytitle 4')];
    }

    public function getStatusList()
    {
        return ['normal' => '已开','locked' => '部分', 'hidden' => '待开'];
    }

    public function getStateList()
    {
        return ['0' => __('State 0'), '1' => __('State 1'), '2' => __('State 2')];
    }
    public function getSoucetypeList()
    {
        return [
            '0' => __('Sourcetype 0'),
            '1' => __('Sourcetype 1'),
            '2' => __('Sourcetype 2'),
            '3' => __('Sourcetype 3'),
            '4' => __('Sourcetype 4'),
            '5' => __('Sourcetype 5'),
            '6' => __('Sourcetype 6'),
            '7' => __('Sourcetype 7'),
            '8' => __('Sourcetype 8')];
    }

    public function getPurposeList()
    {
        return [
            '0' => __('Purpose 0'),
            '1' => __('Purpose 1'),
            '2' => __('Purpose 2'),
            '3' => __('Purpose 3'),
            '4' => __('Purpose 4'),
            '5' => __('Purpose 5'),
            '6' => __('Purpose 6')];
    }


    public function getPaytitleTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['paytitle']) ? $data['paytitle'] : '');
        $list = $this->getPaytitleList();
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

    public function getCurrencyList()
    {
        return ['1' => __('Currency 1'), '2' => __('Currency 2'), '3' => __('Currency 3'), '4' => __('Currency 4')];
    }
    public function getCurrencyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['currency']) ? $data['currency'] : '');
        $list = $this->getCurrencyList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getList()
    {
        $result = [];
        $list = $this->field('id,bill_code')->select();

        foreach ($list as $v) {
            $result[$v['id']] = $v['bill_code'];
        }
        return $result;
    }

    public function supplier()
    {
        return $this->belongsTo('Supplier', 'supplier_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function contract()
    {
        return $this->belongsTo('Contract', 'contract_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
