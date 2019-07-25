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
use think\Session;

/**
 * 成本报管理
 *
 * @icon fa fa-circle-o
 */
class Cosanalysis extends Backend
{
    
    /**
     * Cosanalysis模型对象
     * @var \app\admin\model\Cosanalysis
     */
    protected $model = null;
    protected $noNeedRight = ['yearlist','monthlist','supplier'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Cosanalysis;

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
        if(isset($_GET['filter'])&&$_GET['filter']!='{}'){
            $filter = $this->request->get("filter/a");
            $params=\GuzzleHttp\json_decode($filter[0],true);
            if(isset($params['year'])&&isset($params['month'])){
                $arrmonth=explode(',',$params['month']);
                $searchtime=array();
                foreach ($arrmonth as $key=>$value){
                    $searchtime[$key]=$params['year'].'-'.$value;
                }
                Session::set('arrtime',$searchtime);
                $list=array();
                foreach ($searchtime as $key => $value){
                    $list[$key]['id']=$key+1;
                    $list[$key]['year'] = substr($value,0,4);
                    $list[$key]['month'] = substr($value,5,2);
                    $result = Db::table('fa_bill')->field('sum(price) as sum,purpose')->where('period',$value)->group('purpose')->order('purpose','asc')->select();
                    $arrp=array();
                    foreach ($result as $kk =>$vv){
                        $arrp[]=$kk;
                        $list[$key]['purpose_'.$vv['purpose']]=$vv['sum'];
                    }

                    $diff=array_diff([0,1,2,3,4,5,6],$arrp);
                    foreach ($diff as $ii=>$jj){
                        $list[$key]['purpose_'.$jj]=0;
                    }
                }
                $total=count($list);
                $list = collection($list)->toArray();
                $result = array("total" => $total, "rows" => $list);

                return json($result);
            }

//            var_dump($list);
//            exit;
        }
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id','year','month','purpose_0','purpose_1','purpose_2','purpose_3','purpose_4','purpose_5','purpose_6']);
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        Session::delete('arrtime');
        return $this->view->fetch();
    }

    /**
     * 年份
     */
    public function yearlist()
    {
        $date=date('Y');
        $list=array();
        for($i=-5;$i<5;$i++){
            $list[]=$date+$i;
        }
        return json($list);
    }
    /**
     * 月份
     */
    public function monthlist()
    {
        $list=[0=>['id'=>'01','name'=>'1月'],1=>['id'=>'02','name'=>'2月'],2=>['id'=>'03','name'=>'3月'],3=>['id'=>'04','name'=>'4月'],4=>['id'=>'05','name'=>'5月'],5=>['id'=>'06','name'=>'6月'],6=>['id'=>'07','name'=>'7月'],7=>['id'=>'08','name'=>'8月'],8=>['id'=>'09','name'=>'9月'],9=>['id'=>10,'name'=>'10月'],10=>['id'=>11,'name'=>'11月'],11=>['id'=>12,'name'=>'12月']];
        $total=12;
        return json(['list' => $list, 'total' => $total]);
    }

    /**
     * 供应商报表
     */
    public function supplier()
    {
        $filter = $this->request->get("str/a");
        $type = $this->request->get("type/a");
        $where=[
            'period'=>$filter[0],
            'purpose'=>$type[0]
        ];
        $result = Db::table('fa_bill')->field('sum(price) as sum,purpose,supplier_id')->where($where)->group('supplier_id')->order('purpose','asc')->select();
        $supplier=Db::table('fa_supplier')->field('id,name')->select();
        $arrsupplier=array();
        foreach ($supplier as $value){
            $arrsupplier[$value['id']]=$value['name'];
        }
        foreach ($result as $kk=> $vv){
            $result[$kk]['supplier_name']=$arrsupplier[$vv['supplier_id']];
        }
//        var_dump($result);
//        exit;
        $arrtype=['0' => 'IDC',
            '1' => '云平台',
            '2' => 'IDC&云',
            '3' => 'DIA',
            '4' => '电费',
            '5' => '物业',
            '6' => '其他'];
        $this->view->assign('type',$arrtype[$type[0]]);
        $this->view->assign('row',$result);
        return $this->view->fetch();

        //var_dump($type);
    }

    /**
     * 自定义导出
     */
    public function export()
    {
        if(!isset($_SESSION['think']['arrtime'])||empty($_SESSION['think']['arrtime'])){
            $this->error('暂无数据！');
        }
//        var_dump($_SESSION['think']['arrtime']);
//        exit;
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
            ->setTitle("成本报表")
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
        $worksheet->setTitle('成本报表(导出时间'.date('YmdHis').')');

        //$where=" a.create_time>='$starttime' and a.create_time<='$endtime'";
        //0申请，1申请成功，2已进入，3已离开，4登记完成，5申请已撤销
        $str=['id'=>'序号','year'=>'年份','month'=>'月份','purpose_0' => 'IDC',
            'purpose_1' => '云平台',
            'purpose_2' => 'IDC&云',
            'purpose_3' => 'DIA',
            'purpose_4' => '电费',
            'purpose_5' => '物业',
            'purpose_6' => '其他',
            'name'=>'供应商',
            'sum'=>'金额'
            ];
        $list=array();
        $searchtime=$_SESSION['think']['arrtime'];
        foreach ($searchtime as $key => $value){
            $list[$key]['id']=$key+1;
            $list[$key]['year'] = substr($value,0,4);
            $list[$key]['month'] = substr($value,5,2);
            $result = Db::table('fa_bill')->field('sum(price) as sum,purpose')->where('period',$value)->group('purpose')->order('purpose','asc')->select();
            $arrp=array();
            foreach ($result as $kk =>$vv){
                $arrp[]=$kk;
                $list[$key]['purpose_'.$vv['purpose']]=$vv['sum'];
            }

            $diff=array_diff([0,1,2,3,4,5,6],$arrp);
            foreach ($diff as $ii=>$jj){
                $list[$key]['purpose_'.$jj]=0;
            }
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
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $first = array_keys(reset($list));
        foreach ($first as $index => $item) {
            $worksheet->setCellValueByColumnAndRow($index, 1, $str[$item]);
        }
        $excel->createSheet();
        $result = Db::table('fa_bill')->field('sum(price) as sum,purpose,supplier_id,period')->group('purpose,period,supplier_id')->order('purpose asc,period desc,supplier_id asc')->select();
        $supplier=Db::table('fa_supplier')->field('id,name')->select();
        $arrsupplier=array();
        foreach ($supplier as $value){
            $arrsupplier[$value['id']]=$value['name'];
        }
        $sub_result=array();
        foreach ($result as $jkey=>$jvalue){
            $sub_result[$jvalue['period'].'-'.$str['purpose_'.$jvalue['purpose']]][$jkey]['name']=$arrsupplier[$jvalue['supplier_id']];
            $sub_result[$jvalue['period'].'-'.$str['purpose_'.$jvalue['purpose']]][$jkey]['sum']=$jvalue['sum'];
        }
//        var_dump($sub_result);
//        exit;
        $tempnum=1;
            foreach ($sub_result as $kk=>$vv){
                $worksheet2 = $excel->setActiveSheetIndex($tempnum);
                $worksheet2->setTitle($kk);
                $list_supplier=array();
                foreach ($vv as $ww => $yy){
                    $list_supplier[$ww]['name']=$yy['name'];
                    $list_supplier[$ww]['sum']=$yy['sum'];
                }
                $line2 = 1;
                //$list = ['0'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test'],'1'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test'],'2'=>['admin_id'=>1,'id'=>12,'idc_sign'=>'test']];
                $styleArray2 = array(
                    'font' => array(
                        'bold'  => false,
                        'color' => array('rgb' => '000000'),
                        'size'  => 12,
                        'name'  => 'Verdana'
                    ));
                $list2 = $items2 = collection($list_supplier)->toArray();
                foreach ($items2 as $index => $item) {
                    $line2++;
                    $col2 = 0;
                    foreach ($item as $field => $value) {

                        $worksheet2->setCellValueByColumnAndRow($col2, $line2, $value);
                        $worksheet2->getStyleByColumnAndRow($col2, $line2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet2->getCellByColumnAndRow($col2, $line2)->getStyle()->applyFromArray($styleArray2);
                        $col2++;
                    }
                }
                $first2 = array_keys(reset($list2));
                foreach ($first2 as $index => $item) {
                    $worksheet2->setCellValueByColumnAndRow($index, 1, $str[$item]);
                }
                $tempnum++;
                $excel->createSheet();
            }





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
