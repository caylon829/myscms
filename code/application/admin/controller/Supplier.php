<?php

namespace app\admin\controller;

use app\common\controller\Backend;
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
 * 供应商
 *
 * @icon fa fa-circle-o
 */
class Supplier extends Backend
{
    
    /**
     * Supplier模型对象
     * @var \app\admin\model\Supplier
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Supplier;
        $this->view->assign("levelList", $this->model->getLevelList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("stateList", $this->model->getStateList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $result=Db::table('fa_contract')->field('id')->where('supplier_id','=',$ids)->find();
            if(!empty($result)){
                $this->error('该供应商存有合同，暂时无法删除，请核对！');
            }
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
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
                ->setTitle("供应商信息")
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
            $worksheet->setTitle('供应商(导出时间'.date('YmdHis').')');

            //$where=" a.create_time>='$starttime' and a.create_time<='$endtime'";
            //0申请，1申请成功，2已进入，3已离开，4登记完成，5申请已撤销
            $str=['id'=>'序号','name'=>'供应商名称','level'=>'级别','startdate'=>'合作起始日期','enddate'=>'合作终止日期'];
            $resultarr=Db::table('fa_supplier')->query('select id,name,level,startdate,enddate from fa_supplier order by enddate desc,enterdate desc');
            $list=array();
            foreach ($resultarr as $key=> $value){

                $list[$key]['id']=$value['id'];
                $list[$key]['name']=$value['name'];
                //0申请，1申请成功，2已进入，3已离开，4登记完成，5申请已撤销
                switch ($value['level']){
                    case 'c1':
                        $list[$key]['level']='钻石';
                        break;
                    case 'c2':
                        $list[$key]['level']='金牌';
                        break;
                    case 'c3':
                        $list[$key]['level']='银牌';
                        break;
                    case 'c4':
                        $list[$key]['level']='铜牌';
                        break;
                    default:
                        break;
                }
                $list[$key]['startdate']=$value['startdate'];
                $list[$key]['enddate']=$value['enddate'];
            }
//        var_dump($list);
//        exit;
            $line = 1;
            //$list = ['0'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test'],'1'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test'],'2'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test']];
            $styleArray = array(
                'font' => array(
                    'bold'  => false,
                    'color' => array('rgb' => '000000'),
                    'size'  => 12,
                    'name'  => 'Verdana'
                ));
            $list = $items = collection($list)->toArray();
            foreach ($items as $index => $item) {
                $line++;
                $col = 0;
                foreach ($item as $field => $value) {

                    $worksheet->setCellValueByColumnAndRow($col, $line, $value);
                    $worksheet->getStyleByColumnAndRow($col, $line)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $worksheet->getCellByColumnAndRow($col, $line)->getStyle()->applyFromArray($styleArray);
                    $col++;
                }
            }
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $first = array_keys(reset($list));
            foreach ($first as $index => $item) {
                $worksheet->setCellValueByColumnAndRow($index, 1, $str[$item]);
            }

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
