<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use fast\Random;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Style;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_NumberFormat;

/**
 * 付款管理
 *
 * @icon fa fa-circle-o
 */
class Payment extends Backend
{
    
    /**
     * Payment模型对象
     * @var \app\admin\model\Payment
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Payment;
        $this->view->assign("currencyList", $this->model->getCurrencyList());
        $this->view->assign("paystatusList", $this->model->getPaystatusList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("stateList", $this->model->getStateList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['supplier','contract','bill'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['supplier','contract','bill'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $str=['supplier_id' => '供应商',
            'currency'    => '费用币种',
            'price2'       => '价格',
            'payimage'    => '付款凭证',
            'bill_id2'  => '账单编号',
            'status'      => '状态',
            'notecontent'=>'备注',
            'state'       => '费用币种'];
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            foreach ($params as $key=>$va){
                if(is_array($va)){
                    foreach ($va as $kk=> $vv){
                        if(empty($vv)){
                            $this->error($str[$key].'不能为空');
                        }
                    }
                }
                if(empty($va)){
                    if($key!='notecontent')
                    $this->error($str[$key].'不能为空');
                }
            }
            if(count($params['bill_id2'])>1){
                $addlist=array();
                foreach ($params['bill_id2'] as $kk=>$vv){
                    $addlist[$kk]['bill_id']=$vv;
                    $addlist[$kk]['price']=$params['price2'][$kk];
                    $addlist[$kk]['supplier_id']=$params['supplier_id'];
                    $addlist[$kk]['payaccount_id']=$params['payaccount_id'];
                    $addlist[$kk]['currency']=$params['currency'];
                    $addlist[$kk]['paydate']=$params['paydate'];
                    $addlist[$kk]['paystatus']=$params['paystatus'];
                    $addlist[$kk]['notecontent']=$params['notecontent'];
                    $addlist[$kk]['payimage']=$params['payimage'];
                    $addlist[$kk]['status']=$params['status'];
                    $addlist[$kk]['state']=$params['state'];
                    $bill_price[$kk]=Db::table('fa_bill')->field('price,contract_id')->where('id='.$addlist[$kk]['bill_id'])->find();
                    $addlist[$kk]['contract_id']=$bill_price[$kk]['contract_id'];
                    if($addlist[$kk]['price']>$bill_price[$kk]['price'])
                        $this->error('付款金额不能大于账单金额，请修改价格！');
                }
                $result = $this->model->allowField(true)->saveAll($addlist);
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }

            }

            $params['price']=$params['price2'][1];
            $params['bill_id']=$params['bill_id2'][1];
            $bill_price=Db::table('fa_bill')->field('price,contract_id')->where('id='.$params['bill_id2'][1])->find();
            if ($params) {
                if($bill_price['price']<$params['price'])
                    $this->error('付款金额不能大于账单金额，请修改价格！');
                $params['contract_id']=$bill_price['contract_id'];
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $bill_price=Db::table('fa_bill')->field('price,contract_id')->where('id='.$params['bill_id'])->find();
            if ($params) {
                if($bill_price['price']<$params['price'])
                    $this->error('付款金额不能大于账单金额，请修改价格！');
                $params['contract_id']=$bill_price['contract_id'];
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 自定义导出
     */
    public function export()
    {
        vendor("PHPExcel.PHPExcel.PHPExcel");
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel.Shared_Date");
        vendor("PHPExcel.PHPExcel.Style");
        vendor("PHPExcel.PHPExcel.Style.Alignment");
        vendor("PHPExcel.PHPExcel.Style.Border");
        vendor("PHPExcel.PHPExcel.Style.Fill");
        vendor("PHPExcel.PHPExcel.NumberFormat");

        set_time_limit(5);
//            $starttime = $this->request->post('starttime');
//            $endtime = $this->request->post('endtime');

        $excel = new PHPExcel();

        $excel->getProperties()
            ->setCreator("供应链")
            ->setLastModifiedBy("供应链")
            ->setTitle("付款管理")
            ->setSubject("导出");
        $excel->getDefaultStyle()->getFont()->setName('Microsoft Yahei');
        $excel->getDefaultStyle()->getFont()->setSize(12);

        $this->sharedStyle = new PHPExcel_Style();
        $this->sharedStyle->applyFromArray(
            array(
                'fill'      => array(
                    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '000000')
                ),
                'font'      => array(
                    'color' => array('rgb' => "000000"),
                ),
                'alignment' => array(
                    'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'indent'     => 1
                ),
                'borders'   => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                )
            ));

        $worksheet = $excel->setActiveSheetIndex(0);
        $worksheet->setTitle('付款管理(导出时间'.date('YmdHis').')');

        //$where=" a.create_time>='$starttime' and a.create_time<='$endtime'";
        //0申请，1申请成功，2已进入，3已离开，4登记完成，5申请已撤销
        $str=['name'=>'供应商名称','state'=>'销账状态','0'=>'已销账','1'=>'部分销账','2'=>'待销账','period'=>'账单周期','price'=>'账单金额','bill_paid'=>'已付金额'];
        $resultarr=Db::table('fa_bill')->query('select supplier_id,state,sum(price) as price,sum(bill_unpaid) as bill_unpaid,sum(bill_paid) as bill_paid,period from fa_bill group by supplier_id,period,state order by period asc,supplier_id desc');
        $supplier=Db::table('fa_supplier')->field('id,name')->select();
        $arrsupplier=array();
        foreach ($supplier as $value){
            $arrsupplier[$value['id']]=$value['name'];
        }
        $list=array();
        $xi=0;
        foreach ($resultarr as $key=> $value){
            if($value['state']!=2){
                $list[$value['supplier_id']]['name']=$arrsupplier[$value['supplier_id']];
                //$list[$value['supplier_id']]['period']=$value['period'];
                if($value['state']==0){
                    $list[$value['supplier_id']]['state_0'][$xi]['state']=$value['state'];
                    $list[$value['supplier_id']]['state_0'][$xi]['period']=$value['period'];
                    $list[$value['supplier_id']]['state_0'][$xi]['price']=round($value['price'],2);
                    $list[$value['supplier_id']]['state_0'][$xi]['bill_paid']=round($value['bill_paid'],2);
                    $xi++;
                }
                if($value['state']==1){
                    $list[$value['supplier_id']]['state_1'][$xi]['state']=$value['state'];
                    $list[$value['supplier_id']]['state_1'][$xi]['period']=$value['period'];
                    $list[$value['supplier_id']]['state_1'][$xi]['price']=round($value['price'],2);
                    $list[$value['supplier_id']]['state_1'][$xi]['bill_paid']=round($value['bill_paid'],2);
                    $xi++;
                    $list[$value['supplier_id']]['state_2'][$xi]['state']=$value['state'];
                    $list[$value['supplier_id']]['state_2'][$xi]['period']=$value['period'];
                    $list[$value['supplier_id']]['state_2'][$xi]['price']=round($value['price'],2);
                    $list[$value['supplier_id']]['state_2'][$xi]['bill_unpaid']=round($value['bill_paid'],2);
                    $xi++;
                }

            }
        }
        $temparr=array();
        $ii=1;
        foreach ($list as $key=>$value){
            for($i=0;$i<4;$i++){
                if($ii%4==1){
                    $temparr[$ii]['name']=$value['name'];
                    $temparr[$ii]['type']=$str['state'];
                    $jj=0;
                    if(!empty($value['state_0'])){
                        foreach ($value['state_0'] as $kk => $vv){
                            $temparr[$ii]['0_'.$jj]=$str['0'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_1'])){
                        foreach ($value['state_1'] as $kk => $vv){
                            $temparr[$ii]['1_'.$jj]=$str['1'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_2'])){
                        foreach ($value['state_2'] as $kk => $vv){
                            $temparr[$ii]['2_'.$jj]=$str['2'];
                            $jj++;
                        }
                    }
                }
                if($ii%4==2){
                    $temparr[$ii]['name']='';
                    $temparr[$ii]['type']=$str['period'];
                    $jj=0;
                    if(!empty($value['state_0'])){
                        foreach ($value['state_0'] as $kk => $vv){
                            $temparr[$ii]['0_'.$jj]=$vv['period'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_1'])){
                        foreach ($value['state_1'] as $kk => $vv){
                            $temparr[$ii]['1_'.$jj]=$vv['period'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_2'])){
                        foreach ($value['state_2'] as $kk => $vv){
                            $temparr[$ii]['2_'.$jj]=$vv['period'];
                            $jj++;
                        }
                    }
            }
                if($ii%4==3){
                    $temparr[$ii]['name']='';
                    $temparr[$ii]['type']=$str['price'];
                    $jj=0;
                    if(!empty($value['state_0'])){
                        foreach ($value['state_0'] as $kk => $vv){
                            $temparr[$ii]['0_'.$jj]=$vv['price'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_1'])){
                        foreach ($value['state_1'] as $kk => $vv){
                            $temparr[$ii]['1_'.$jj]=$vv['price'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_2'])){
                        foreach ($value['state_2'] as $kk => $vv){
                            $temparr[$ii]['2_'.$jj]=$vv['price'];
                            $jj++;
                        }
                    }
                }
                if($ii%4==0){
                    $temparr[$ii]['name']='';
                    $temparr[$ii]['type']=$str['bill_paid'];
                    $jj=0;
                    if(!empty($value['state_0'])){
                        foreach ($value['state_0'] as $kk => $vv){
                            $temparr[$ii]['0_'.$jj]=$vv['bill_paid'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_1'])){
                        foreach ($value['state_1'] as $kk => $vv){
                            $temparr[$ii]['1_'.$jj]=$vv['bill_paid'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_2'])){
                        foreach ($value['state_2'] as $kk => $vv){
                            $temparr[$ii]['2_'.$jj]=$vv['bill_unpaid'];
                            $jj++;
                        }
                    }
                }
                $ii++;
            }
        }
        $line = 0;
        //$list = ['0'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test'],'1'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test'],'2'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test']];
        $styleArray = array(
            'font' => array(
                'bold'  => false,
                'color' => array('rgb' => '000000'),
                'size'  => 12,
                'name'  => 'Verdana'
            ));
        $stylered = array(
            'font' => array(
                'bold'  => false,
                'color' => array('rgb' => '000000'),
                'size'  => 12,
                'name'  => 'Verdana'
            ),
            'fill' => array (
                'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR ,
                'rotation'   => 90,
                'startcolor' => array (
                    'argb' => 'FF3030'
                ),
                'endcolor'   => array (
                    'argb' => 'FF3030'
                )
            ));
        $stylegreen = array(
            'font' => array(
                'bold'  => false,
                'color' => array('rgb' => '000000'),
                'size'  => 12,
                'name'  => 'Verdana'
            ), 'fill' => array (
                'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR ,
                'rotation'   => 90,
                'startcolor' => array (
                    'argb' => '458B00'
                ),
                'endcolor'   => array (
                    'argb' => '458B00'
                )
            ));
        $styleyellow = array(
            'font' => array(
                'bold'  => false,
                'color' => array('rgb' => '000000'),
                'size'  => 12,
                'name'  => 'Verdana'
            ), 'fill' => array (
                'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR ,
                'rotation'   => 90,
                'startcolor' => array (
                    'argb' => 'EEEE00'
                ),
                'endcolor'   => array (
                    'argb' => 'EEEE00'
                )
            ));
        $list = $items = collection($temparr)->toArray();
        $nums=1;
        foreach ($items as $index => $item) {
            $line++;
            $col = 0;
            foreach ($item as $field => $value) {


                $worksheet->setCellValueByColumnAndRow($col, $line, $value);
                $worksheet->getStyleByColumnAndRow($col, $line)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $worksheet->getCellByColumnAndRow($col, $line)->getStyle()->applyFromArray($styleArray);
                if($line%4==1){
                    if($col>1){
                        switch ($value) {
                            case '已销账':
                                $newline=$line+1;
                                $worksheet->getCellByColumnAndRow($col, $newline)->getStyle()->applyFromArray($stylegreen);
                                $nums++;
                                break;
                            /*case '部分销账':
                                $newline=$line+1;
                                $worksheet->getCellByColumnAndRow($col, $newline)->getStyle()->applyFromArray($stylered);
                                break;
                            case '待销账':
                                $newline=$line+1;
                                $worksheet->getCellByColumnAndRow($col, $newline)->getStyle()->applyFromArray($styleyellow);
                                break;*/
                        }
                    }
                }
                if($line%4==2){
                    if($col>$nums){
                        $current_month=date("m");
                        $str_array=explode('-',$value);
                        $re=$current_month-$str_array[1];
                        if($re>3){
                            $worksheet->getCellByColumnAndRow($col, $line)->getStyle()->applyFromArray($stylered);
                        }elseif ($re==3){
                            $worksheet->getCellByColumnAndRow($col, $line)->getStyle()->applyFromArray($styleyellow);
                        }
                    }
                }
                $col++;
            }
        }
//        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
//        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
//        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
//        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
//        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
//        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
//        $first = array_keys(reset($list));
//        foreach ($first as $index => $item) {
//            $worksheet->setCellValueByColumnAndRow($index, 1, $str[$item]);
//        }

        $excel->createSheet();
        // Redirect output to a client’s web browser (Excel2007)
        $title = date("YmdHis").Random::alnum(6);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');
        return 'success';
    }
    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed   $searchfields   快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function buildparams($searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');
        $filter = $this->request->get("filter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", "id");
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset", 0);
        $limit = $this->request->get("limit", 0);
        $filter = (array)json_decode($filter, true);
        foreach ($filter as $key=>$value){
            switch ($key){
                case 'bill.bill_code':
                case 'supplier.name':
                case 'contract.contract_no':
                    break;
                default:
                    unset($filter[$key]);
                    $filter['fa_payment.'.$key]=$value;
                    break;
            }

        }
        $op = (array)\GuzzleHttp\json_decode($op, true);
        foreach ($op as $key=>$value){
            switch ($key){
                case 'bill.bill_code':
                case 'supplier.name':
                case 'contract.contract_no':
                    break;
                default:
                    unset($op[$key]);
                    $op['fa_payment.'.$key]=$value;
                    break;
            }

        }
        $filter = $filter ? $filter : [];
        $where = [];
        $tableName = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => & $item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }
        $sort='fa_payment.'.$sort;

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym) {
                case '=':
                case '<>':
                    $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'LIKE':
                case 'LIKE %...%':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
        }
        $where = function ($query) use ($where) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
    }
}
