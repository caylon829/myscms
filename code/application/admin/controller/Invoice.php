<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use fast\Random;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Style;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_NumberFormat;

/**
 * 发票管理
 *
 * @icon fa fa-circle-o
 */
class Invoice extends Backend
{
    
    /**
     * Invoice模型对象
     * @var \app\admin\model\Invoice
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Invoice;
        $this->view->assign("titleList", $this->model->getTitleList());
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("taxList", $this->model->getTaxList());
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
                ->order('fa_invoice.id', $order)
                ->count();

            $list = $this->model
                ->with(['supplier','contract','bill'])
                ->where($where)
                ->order('fa_invoice.id', $order)
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
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $bill=Db::table('fa_bill')->field('contract_id')->where('id='.$params['bill_id'])->find();
            if ($params) {
                $params['contract_id']=$bill['contract_id'];
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
            $bill=Db::table('fa_bill')->field('contract_id')->where('id='.$params['bill_id'])->find();
            if ($params) {
                $params['contract_id']=$bill['contract_id'];
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
            ->setTitle("发票管理")
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
        $worksheet->setTitle('发票管理(导出时间'.date('YmdHis').')');

        //$where=" a.create_time>='$starttime' and a.create_time<='$endtime'";
        //0申请，1申请成功，2已进入，3已离开，4登记完成，5申请已撤销
        $str=['name'=>'供应商名称','status'=>'发票状态','0'=>'已开','1'=>'部分','2'=>'待开','period'=>'账单周期','price'=>'账单金额','invoice_price'=>'发票金额'];
        $resultarr=Db::table('fa_invoice')->query('select a.supplier_id,b.name,c.status,sum(c.price) as price,c.period,sum(a.price) as invoice_price from fa_invoice a LEFT JOIN fa_supplier b on a.supplier_id=b.id LEFT JOIN fa_bill c on a.bill_id=c.id group by a.supplier_id,c.period,c.status order by c.period asc,a.supplier_id desc');
        $list=array();
        $xi=0;
        foreach ($resultarr as $key=> $value){
            if($value['status']!=2){
                $list[$value['supplier_id']]['name']=$value['name'];
                //$list[$value['supplier_id']]['period']=$value['period'];
                if($value['status']==0){
                    $list[$value['supplier_id']]['state_0'][$xi]['status']=$value['status'];
                    $list[$value['supplier_id']]['state_0'][$xi]['period']=$value['period'];
                    $list[$value['supplier_id']]['state_0'][$xi]['price']=round($value['price'],2);
                    $list[$value['supplier_id']]['state_0'][$xi]['invoice_price']=round($value['invoice_price'],2);
                    $xi++;
                }
                if($value['status']==1){
                    $list[$value['supplier_id']]['state_1'][$xi]['state']=$value['status'];
                    $list[$value['supplier_id']]['state_1'][$xi]['period']=$value['period'];
                    $list[$value['supplier_id']]['state_1'][$xi]['price']=round($value['price'],2);
                    $list[$value['supplier_id']]['state_1'][$xi]['invoice_price']=round($value['invoice_price'],2);
                    $xi++;
                    $list[$value['supplier_id']]['state_2'][$xi]['state']=$value['status'];
                    $list[$value['supplier_id']]['state_2'][$xi]['period']=$value['period'];
                    $list[$value['supplier_id']]['state_2'][$xi]['price']=round($value['price'],2);
                    $list[$value['supplier_id']]['state_2'][$xi]['invoice_price']=round($value['invoice_price'],2);
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
                    $temparr[$ii]['type']=$str['status'];
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
                    $temparr[$ii]['type']=$str['invoice_price'];
                    $jj=0;
                    if(!empty($value['state_0'])){
                        foreach ($value['state_0'] as $kk => $vv){
                            $temparr[$ii]['0_'.$jj]=$vv['invoice_price'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_1'])){
                        foreach ($value['state_1'] as $kk => $vv){
                            $temparr[$ii]['1_'.$jj]=$vv['invoice_price'];
                            $jj++;
                        }
                    }
                    if(!empty($value['state_2'])){
                        foreach ($value['state_2'] as $kk => $vv){
                            $temparr[$ii]['2_'.$jj]=$vv['invoice_price'];
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
                        //'0'=>'已开','1'=>'部分','2'=>'待开'
                        switch ($value) {
                            case '已开':
                                $newline=$line+1;
                                $worksheet->getCellByColumnAndRow($col, $newline)->getStyle()->applyFromArray($stylegreen);
                                $nums++;
                                break;
                            /*case '部分':
                                $newline=$line+1;
                                $worksheet->getCellByColumnAndRow($col, $newline)->getStyle()->applyFromArray($stylered);
                                break;
                            case '待开':
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
}
