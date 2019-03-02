<?php

namespace App\Http\Controllers;

use App\Client;
use App\Operator;
use App\AKVRDaily;
use Illuminate\Http\Request;
use DB;
use App\Log;
class VRChartController extends Controller
{
    use Log;
    public function index(){
        $this->log('VR Chart','VR Chart ','VR');
        $data['title']="VR Transaction";
        DB::enableQueryLog();
        $year = DB::select("SELECT DISTINCT(year(transdate)) as year FROM AK_VR_Daily");
        $year_options = [];
        foreach ($year as $y){
            $year_options[$y->{'year'}]=$y->{'year'};
        }
        $data['year_options'] = $year_options;
        $department = Client::select('Department')->distinct()->get()->toArray();
        $department_options = [];
        foreach ($department as $d){
            $department_options[$d['Department']]=$d['Department'];
        }
        $data['department_options'] = $department_options;
//        $data['year_options'] = AKVRDaily::select(DB::raw('DISTINCT (year(transdate))'))->pluck('transdate');
//        dd($data['year_options']);
        return view('vr.vrchart',compact('data'));
    }
    //.......................JSON Data For Top Ten Kam in VR................................
    public function VRTopTenKamChart(){
        try{
            $query = "SELECT client.KAM, SUM(AK_VR_Daily.Amount) as Amount FROM AK_VR_Daily JOIN client where client.client_id=AK_VR_Daily.client_id and year(AK_VR_Daily.transdate)=year(CURDATE()-interval 1 day) and AK_VR_Daily.client_id not in (109) and month(AK_VR_Daily.transdate)=month(CURDATE()-interval 1 day) GROUP by client.KAM ORDER by Amount DESC LIMIT 10";
            DB::enableQueryLog();
            $result=DB::select($query);
            $rows = array();
            $table = array();
            $table['cols'] = array(

                array('label' => 'Client Id', 'type' => 'string'),
                array('label' => 'Total Amount', 'type' => 'number'),
                array('label' => 'Total Amount', 'type' => 'number')

            );
            /* Extract the information from $result */
            foreach($result as $r)
                $rows[] = array('c' => array(
                    array('v' => (string) $r->{'KAM'}),
                    array('v' => (int) $r->{'Amount'}),
                    array('v' => ((int) $r->{'Amount'})/1000000)
                ));



            $table['rows'] = $rows;

            $jsonTable = json_encode($table);

            return $jsonTable;
        }catch(Exception $e){
            \LOG::error($e-getMessage());
        }
    }

    //.......................JSON Data For Top Ten Client in VR................................
    public function VRTopTenClientChart(){
        try{
            $query = "SELECT client.client_name, SUM(AK_VR_Daily.Amount) as Amount FROM AK_VR_Daily JOIN client where client.client_id=AK_VR_Daily.client_id and AK_VR_Daily.client_id != 109 and year(AK_VR_Daily.transdate)=year(CURDATE()-interval 1 day) and month(AK_VR_Daily.transdate)=month(CURDATE()-interval 1 day) GROUP by client.client_name ORDER by Amount DESC LIMIT 10";
            DB::enableQueryLog();
            $result=DB::select($query);
            $rows = array();
            $table = array();
            $table['cols'] = array(

                array('label' => 'Client Id', 'type' => 'string'),
                array('label' => 'Total Amount', 'type' => 'number'),
                array('label' => 'Total Amount', 'type' => 'number')

            );
            /* Extract the information from $result */
            foreach($result as $r)
                $rows[] = array('c' => array(
                    array('v' => (string) $r->{'client_name'}),
                    array('v' => (int) $r->{'Amount'}),
                    array('v' => ((int) $r->{'Amount'})/1000000)
                ));

            $table['rows'] = $rows;

            $jsonTable = json_encode($table);

            return $jsonTable;
        }catch(Exception $e){
            \LOG::error($e-getMessage());
        }
    }


    //.......................JSON Data For Operator wise Chart for current month donut chart in VR................................
    public function VROpearatorChart(){
        try{
            $query = "SELECT SUM(AK_VR_Daily.Amount), operator.operator_name FROM AK_VR_Daily JOIN operator where year(AK_VR_Daily.transdate)= year(CURDATE() - INTERVAL 1 day) and month(AK_VR_Daily.transdate)= month(CURDATE() - INTERVAL 1 day) and operator.operator_id=AK_VR_Daily.operator_id group by operator.operator_name";
            DB::enableQueryLog();
            $result=DB::select($query);
            $rows = array();
            $table = array();
            $table['cols'] = array(
                array('label' => 'Operator Name', 'type' => 'string'),
                array('label' => 'Total Amount', 'type' => 'number')
            );
            /* Extract the information from $result */
            foreach($result as $r)
                $rows[] = array('c' => array(array('v' => (string) $r->{'operator_name'}),array('v' => (int) $r->{'SUM(AK_VR_Daily.Amount)'})));

            $table['rows'] = $rows;

            $jsonTable = json_encode($table);

            return $jsonTable;
        }catch(Exception $e){
            \LOG::error($e->getMessage());
        }
    }

    //.......................JSON Data For  VR Transaction count in VR................................
    public function VRTransactionCountChart(Request $request){
        try{
            $year=$request->input('year');
            $month=$request->input('month');
            $quarter=$request->input('quarter');
            $operator=$request->input('operatorName');
            $year=str_replace(',', '', $year);
            $query="";
            $pdoparameter=[];

            if($year==null && $quarter==null && $month==null){
                $query="SELECT YEAR(transdate) as id, sum(totalcnt) as Amount FROM AK_VR_Daily GROUP by id";
            }
            elseif ($year!=null && $quarter!=null && $month==null) {
                $query = "SELECT QUARTER(transdate) as id, sum(totalcnt) as Amount FROM AK_VR_Daily WHERE YEAR(transdate)=:year GROUP by id";
                $pdoparameter=['year'=>$year];
            }
            elseif ($year!=null && $quarter==null && $month==null) {
                $query = "SELECT MONTH(transdate) as id, sum(totalcnt) as Amount FROM AK_VR_Daily WHERE YEAR(transdate)=:year GROUP by id";
                $pdoparameter=['year'=>$year];
            }
            elseif ($year!=null && $quarter==null && $month!=null) {
                $query = "SELECT DAY(transdate) as id, sum(totalcnt) as Amount FROM AK_VR_Daily WHERE YEAR(transdate)= :year and MONTH(transdate)= :month GROUP by id";
                $pdoparameter=['year'=>$year, 'month'=>$month];
            }

            DB::enableQueryLog();
            $result=DB::select($query,$pdoparameter);

            $rows = array();
            $table = array();

            $table['cols'] = array(
                array('label' => 'id', 'type' => 'string'),
                array('label' => 'Count', 'type' => 'number'),
                array('label' => 'Count', 'type' => 'number'),
                array('role' => 'style', 'type' => 'string'),
            );

            foreach($result as $r)
                $rows[] = array('c' => array(
                    array('v' => (string) $r->{'id'}),
                    array('v' => (int) $r->{'Amount'}),
                    array('v' => (int) $r->{'Amount'}/1000),
                    array('v' => "#6EDDF9")
                ));
            if( $year != null && $month != null){
                $query3 = "SELECT COUNT(transdate) cnt FROM `AK_VR_Daily` WHERE (SELECT year(max(transdate)) FROM AK_VR_Daily) = :year and (SELECT month(max(transdate)) FROM AK_VR_Daily) = :month";
                $result3=DB::select($query3, ['year'=>$year, 'month'=>$month]);
                if($result3[0]->{'cnt'}){
                    $query4 = "SELECT transdate a, weekday(transdate) weekday, sum(totalcnt) amount, (SELECT sum(totalcnt) FROM AK_VR_Daily WHERE transdate = (a - INTERVAL 1 day)) priviousday , (100 + ( 100 * ((SELECT sum(totalcnt) FROM AK_VR_Daily WHERE transdate = (a - INTERVAL 7 day)) - (SELECT sum(totalcnt) FROM AK_VR_Daily WHERE transdate = (a - INTERVAL 8 day))) / (SELECT sum(totalcnt) FROM AK_VR_Daily WHERE transdate = (a - INTERVAL 8 day)) ) ) per FROM AK_VR_Daily WHERE transdate > (SELECT max(transdate) - INTERVAL 7 day FROM AK_VR_Daily) GROUP by transdate";
                    DB::enableQueryLog();
                    $result4 = DB::select($query4);
                    $weekdayper = [];
                    $lastDate = "";
                    $lastAmount = 0;
                    $lastweekday = "";
                    foreach($result4 as $r4){
                        $weekdayper[$r4->{'weekday'}] = $r4->{'per'};
                        $lastDate = $r4->{'a'};
                        $lastAmount = $r4->{'amount'};
                        $lastweekday = $r4->{'weekday'};
                    }

                    $date = date_create($lastDate);
                    $cday = date_format($date,"d");
                    $month = date_format($date,"m") - 1;
                    $year = date_format($date,"Y");
                    $lday=cal_days_in_month(CAL_GREGORIAN,$month,$year);

                    $j = 0; $lastAmountStore = $lastAmount;
                    for($i=$cday+1;$i<=$lday;$i++){
                        if($j%7==0) $lastAmount = $lastAmountStore;
                        $j++;
                        $lastweekday++;
                        $lastweekday%=7;
                        $lastAmount = ($lastAmount/100)*$weekdayper[$lastweekday];
                        $rows[] = array('c' => array(
                            array('v' => (string) $i),
                            array('v' => (int) $lastAmount),
                            array('v' => ((int) $lastAmount)/1000),
                            array('v' => "#D7F7FE")
                        ));
                    }
                }
            }

            $table['rows'] = $rows;

            // convert data into JSON format
            $jsonTable = json_encode($table);
            return $jsonTable;
        }catch(Exception $e){
            \LOG::error($e-getMessage());
        }
    }

    //.......................JSON Data For VR Client who are mostly impact in negatively or positively................................
    public function VRImpactClientChart(Request $request){
        try{
            $fromDate=$request->input('fromDate');
            $toDate=$request->input('toDate');
            $operation=$request->input('operation');


            $resultToquery="";$resultFromquery="";$resultMid1query="";$resultMid2query="";
            $totalAmountDiff=0;$amountDiff=0;$others=0;
            $resultTopdoparameter=[];
            $resultFrompdoparameter=[];
            $resultMid1pdoparameter=[];
            $resultMid2pdoparameter=[];
            DB::enableQueryLog();
            if($fromDate!=null && $toDate!=null && $operation=="day"){
                $resultToquery="SELECT  AK_VR_Daily.transdate id, sum(AK_VR_Daily.amount) amount FROM `AK_VR_Daily` WHERE AK_VR_Daily.transdate= :toDate  group by id";
                $resultTopdoparameter=['toDate'=>$toDate];
                $resultFromquery="SELECT AK_VR_Daily.transdate id, sum(AK_VR_Daily.amount) amount FROM `AK_VR_Daily` WHERE AK_VR_Daily.transdate=(:fromDate)  group by id";
                $resultFrompdoparameter=['fromDate'=>$fromDate];
                $resultMid1query="SELECT ifnull(k.c1,k.c2) id,ifnull(k.m1,0)-ifnull(k.m2,0) amount from (SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where transdate =:toDate GROUP BY AK_VR_Daily.client_id) b left JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where transdate =(:fromDate) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 UNION ALL SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where transdate =:toDate1 GROUP BY AK_VR_Daily.client_id) b RIGHT JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where transdate =(:fromDate1) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 where b.c1 is null) k ORDER by amount DESC LIMIT 4";
                $resultMid1pdoparameter=['toDate'=>$toDate,'toDate1'=>$toDate,'fromDate'=>$fromDate,'fromDate1'=>$fromDate];
                $resultMid2query="SELECT ifnull(k.c1,k.c2) id,ifnull(k.m1,0)-ifnull(k.m2,0) amount from (SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where transdate =:toDate GROUP BY AK_VR_Daily.client_id) b left JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where transdate =(:fromDate ) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 UNION ALL SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where transdate =:toDate1 GROUP BY AK_VR_Daily.client_id) b RIGHT JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where transdate =(:fromDate1 ) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 where b.c1 is null) k ORDER by amount ASC LIMIT 4";
                $resultMid2pdoparameter=['toDate'=>$toDate,'toDate1'=>$toDate,'fromDate'=>$fromDate,'fromDate1'=>$fromDate];

            }
            elseif($fromDate!=null && $toDate!=null && $operation=="month"){
                $resultToquery="SELECT  month(AK_VR_Daily.transdate) id, sum(AK_VR_Daily.amount) amount FROM `AK_VR_Daily` WHERE year(transdate) =year(:toDate) and month(transdate) =month(:toDate1) group by id";
                $resultTopdoparameter=['toDate'=>$toDate,'toDate1'=>$toDate];
                $resultFromquery="SELECT month(AK_VR_Daily.transdate) id, sum(AK_VR_Daily.amount) amount FROM `AK_VR_Daily` WHERE year(transdate) =year(:fromDate ) and month(transdate) =month(:fromDate1 ) group by id";
                $resultFrompdoparameter=['fromDate'=>$fromDate,'fromDate1'=>$fromDate];
                $resultMid1query="SELECT ifnull(k.c1,k.c2) id,ifnull(k.m1,0)-ifnull(k.m2,0) amount from (SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where year(transdate) =year(:toDate) and month(transdate) =month(:toDate1) GROUP BY AK_VR_Daily.client_id) b left JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where year(transdate) =year(:fromDate ) and month(transdate) =month(:fromDate1 ) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 UNION ALL SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where year(transdate) =year(:toDate2) and month(transdate) =month(:toDate3) GROUP BY AK_VR_Daily.client_id) b RIGHT JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where year(transdate) =year(:fromDate2 ) and month(transdate) =month(:fromDate3 ) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 where b.c1 is null) k ORDER by amount DESC LIMIT 4";
                $resultMid1pdoparameter=['toDate'=>$toDate,'toDate1'=>$toDate,'toDate2'=>$toDate,'toDate3'=>$toDate,'fromDate'=>$fromDate,'fromDate1'=>$fromDate,'fromDate2'=>$fromDate,'fromDate3'=>$fromDate];
                $resultMid2query="SELECT ifnull(k.c1,k.c2) id,ifnull(k.m1,0)-ifnull(k.m2,0) amount from (SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where year(transdate) =year(:toDate) and month(transdate) =month(:toDate1) GROUP BY AK_VR_Daily.client_id) b left JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where year(transdate) =year(:fromDate ) and month(transdate) =month(:fromDate1 ) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 UNION ALL SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where year(transdate) =year(:toDate2) and month(transdate) =month(:toDate3) GROUP BY AK_VR_Daily.client_id) b RIGHT JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where year(transdate) =year(:fromDate2 ) and month(transdate) =month(:fromDate3 ) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 where b.c1 is null) k ORDER by amount ASC LIMIT 4";
                $resultMid2pdoparameter=['toDate'=>$toDate,'toDate1'=>$toDate,'toDate2'=>$toDate,'toDate3'=>$toDate,'fromDate'=>$fromDate,'fromDate1'=>$fromDate,'fromDate2'=>$fromDate,'fromDate3'=>$fromDate];

            }
            elseif($fromDate!=null && $toDate!=null && $operation=="year"){
                $resultToquery="SELECT  year(AK_VR_Daily.transdate) id, sum(AK_VR_Daily.amount) amount FROM `AK_VR_Daily` WHERE year(transdate) =year(:toDate) group by id";
                $resultTopdoparameter=['toDate'=>$toDate];
                $resultFromquery="SELECT year(AK_VR_Daily.transdate) id, sum(AK_VR_Daily.amount) amount FROM `AK_VR_Daily` WHERE year(transdate) =year(:fromDate) group by id";
                $resultFrompdoparameter=['fromDate'=>$fromDate];
                $resultMid1query="SELECT ifnull(k.c1,k.c2) id,ifnull(k.m1,0)-ifnull(k.m2,0) amount from (SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where year(transdate) =year(:toDate) GROUP BY AK_VR_Daily.client_id) b left JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where year(transdate) =year(:fromDate) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 UNION ALL SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where year(transdate) =year(:toDate1)  GROUP BY AK_VR_Daily.client_id) b RIGHT JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where year(transdate) =year(:fromDate1)  GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 where b.c1 is null) k ORDER by amount DESC LIMIT 4";
                $resultMid1pdoparameter=['toDate'=>$toDate,'toDate1'=>$toDate,'fromDate'=>$fromDate,'fromDate1'=>$fromDate];
                $resultMid2query="SELECT ifnull(k.c1,k.c2) id,ifnull(k.m1,0)-ifnull(k.m2,0) amount from (SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where year(transdate) =year(:toDate) GROUP BY AK_VR_Daily.client_id) b left JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where year(transdate) =year(:fromDate) GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 UNION ALL SELECT * from (SELECT AK_VR_Daily.client_id c1,sum(amount) m1 from AK_VR_Daily where year(transdate) =year(:toDate1)  GROUP BY AK_VR_Daily.client_id) b RIGHT JOIN (SELECT AK_VR_Daily.client_id c2,sum(amount) m2 from AK_VR_Daily where year(transdate) =year(:fromDate1)  GROUP BY AK_VR_Daily.client_id) a on a.c2=b.c1 where b.c1 is null) k ORDER by amount ASC LIMIT 4";
                $resultMid2pdoparameter=['toDate'=>$toDate,'toDate1'=>$toDate,'fromDate'=>$fromDate,'fromDate1'=>$fromDate];
            }
            $resultTo=DB::select($resultToquery,$resultTopdoparameter);
            $resultFrom=DB::select($resultFromquery,$resultFrompdoparameter);
            $resultMid1=DB::select($resultMid1query,$resultMid1pdoparameter);
            $resultMid2=DB::select($resultMid2query,$resultMid2pdoparameter);

            $rows = array();
            $table = array();

            $table['cols'] = array(
                array('label' => 'id', 'type' => 'string'),
                array('label' => 'bottom', 'type' => 'number'),
                array('label' => 'bottom1', 'type' => 'number'),
                array('label' => 'top', 'type' => 'number'),
                array('label' => 'top1', 'type' => 'number'),
                array('role' => 'style', 'type' => 'string'),
                array('role' => 'tooltip', 'type' => 'string', 'p' => array('html' => true))
            );
            $top=0;$firstdate="";$firstamount="";
            $lastdate="";$lastamount="";
            foreach ($resultTo as $result) {
                $totalAmountDiff= (int) $result->{'amount'};
                $firstdate=$result->{'id'};
                if($operation=="month")$firstdate=$result->{'id'}.", ".date('Y', strtotime($toDate));
                $firstamount= (int) $result->{'amount'};
            }
            foreach ($resultFrom as $result) {
                $lastamount= (int) $result->{'amount'};
                $totalAmountDiff-= (int) $result->{'amount'};
                $lastdate=$result->{'id'};
                if($operation=="month")$lastdate=$result->{'id'}.", ".date('Y', strtotime($fromDate));

                $rows[] = array('c' => array(
                    array('v' => (string) $lastdate),
                    array('v' => (double) $top),
                    array('v' => (double) $top),
                    array('v' => (double) $result->{'amount'}+$top),
                    array('v' => (double) $result->{'amount'}+$top),
                    array('v' => (string) "#2ea9b2"),
                    array('v' => (string) "<div style=\"text-align:left;min-width: 220px;padding-left: 10px;\">".$lastdate." Amount: <b>".number_format( (int) $result->{'amount'}+$top)."</b></div>")
                ));
                $top+= (int) $result->{'amount'};
            }
            $sortArray = [];
            foreach ($resultMid1 as $result) {
                $sortArray[] = array(
                    'id' => (string) $result->{'id'},
                    'amount' => (double) $result->{'amount'},
                    'color' => (string) "#2ea232"
                );
                $amountDiff+=$result->{'amount'};
            }
            foreach ($resultMid2 as $result) {
                $sortArray[] = array(
                    'id' => (string) $result->{'id'},
                    'amount' => (double) $result->{'amount'},
                    'color' => (string) "#fe0b1b"
                );
                $amountDiff+=$result->{'amount'};
            }

            $sortArray[] = array(
                'id' => (string) "Others",
                'amount' => (double) $totalAmountDiff-$amountDiff,
                'color' => (string) "#f39c12"
            );
            usort($sortArray, function($a, $b) {return $b['amount'] <=> $a['amount'];});

            foreach ($sortArray as $value){
                $resultLastAmount="";
                $resultFirstAmount="";
                $tempfirstamount=0;
                $templastamount=0;
                $amount = $value['amount'];
                if($value['id']!='Others') {
                    if($operation=="day"){
                        $queryLastAmount ="SELECT SUM(Amount) as amount FROM `AK_VR_Daily` WHERE client_id=:id and transdate=:fromDate";
                        $queryFirstAmount ="SELECT SUM(Amount) as amount FROM `AK_VR_Daily` WHERE client_id=:id and transdate=:toDate";
                        $resultLastAmount=DB::select($queryLastAmount,['fromDate'=>$fromDate,'id'=>$value['id']]);
                        $resultFirstAmount=DB::select($queryFirstAmount,['toDate'=>$toDate,'id'=>$value['id']]);
                    }elseif ($operation=="month") {
                        $queryLastAmount ="SELECT SUM(Amount) as amount FROM `AK_VR_Daily` WHERE client_id=:id and year(transdate)=year(:fromDate) and month(transdate)=month(:fromDate1)";
                        $queryFirstAmount ="SELECT SUM(Amount) as amount FROM `AK_VR_Daily` WHERE client_id=:id and year(transdate)=year(:toDate) and month(transdate)=month(:toDate1)";
                        $resultLastAmount=DB::select($queryLastAmount,['fromDate'=>$fromDate, 'fromDate1'=>$fromDate,'id'=>$value['id']]);
                        $resultFirstAmount=DB::select($queryFirstAmount,['toDate'=>$toDate, 'toDate1'=>$toDate,'id'=>$value['id']]);
                    }elseif ($operation=="year") {
                        $queryLastAmount ="SELECT SUM(Amount) as amount FROM `AK_VR_Daily` WHERE client_id=:id and year(transdate)=year(:fromDate)";
                        $queryFirstAmount ="SELECT SUM(Amount) as amount FROM `AK_VR_Daily` WHERE client_id=:id and year(transdate)=year(:toDate)";
                        $resultLastAmount=DB::select($queryLastAmount,['fromDate'=>$fromDate,'id'=>$value['id']]);
                        $resultFirstAmount=DB::select($queryFirstAmount,['toDate'=>$toDate,'id'=>$value['id']]);
                    }

                    foreach ($resultFirstAmount as $r) {
                        $tempfirstamount = (int) $r->{'amount'};
                    }
                    foreach ($resultLastAmount as $r) {
                        $templastamount = (int) $r->{'amount'};
                    }

                    $client = Client::select(DB::raw('concat(client_name,"\n",Department) client_name'))->where('client_id', $value['id'])->get();
                    foreach ($client as $c){
                        $value['id'] = $c->{'client_name'};
                    }

                }else{
                    $tempfirstamount = (int) $top+$amount;
                    $templastamount = (int) $top;
                }
                $rows[] = array('c' => array(
                    array('v' => (string) $value['id']),
                    array('v' => (double) $top),
                    array('v' => (double) $top),
                    array('v' => (double) $amount+$top),
                    array('v' => (double) $amount+$top),
                    array('v' => (string) $value['color']),
                    array('v' => (string) "<div style=\"text-align:left;min-width: 220px;padding-left: 10px;\">Client name: <b>".$value['id']."</b><br/>"
                        .$lastdate." amount: <b>".number_format($templastamount)."</b><br/>"
                        .$firstdate." amount: <b>".number_format($tempfirstamount)."</b><br/>"
                        ."Amount change: <b>".number_format($amount)."</b> (<b>".number_format((float)(($amount/$lastamount)*100), 2, '.', '')."%</b>)<br/></div>")
                ));
                $top+= (int) $amount;
            }

            $rows[] = array('c' => array(
                array('v' => (string) $firstdate),
                array('v' => (double) 0),
                array('v' => (double) 0),
                array('v' => (double) $firstamount),
                array('v' => (double) $firstamount),
                array('v' => (string) "#2ea9b2"),
                array('v' => (string) "<div style=\"text-align:left;min-width: 220px;padding-left: 10px;\">".$firstdate." Amount: <b>".number_format($firstamount)."</b> (<b>".number_format((float)((($firstamount/$lastamount)-1)*100), 2, '.', '')."%</b>)<br/></div>")
            ));

            $table['rows'] = $rows;

            // // convert data into JSON format
            $jsonTable = json_encode($table);
            return $jsonTable;
        }catch(Exception $e){
            \LOG::error($e-getMessage());
        }
    }

    //.......................JSON Data For VRHourwise chart in VR................................
    public function VRHourWiseChart(){
        $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');

        $query = "SELECT DISTINCT(transdate) as transdate , month(transdate) as month, day(transdate) as day FROM VRData ORDER BY transdate DESC LIMIT 3";
        DB::enableQueryLog();
        $result=DB::select($query);
        $lvl= array();
        foreach($result as $r){
            $lvl[]= array('d' => $r->{'day'}, 'm' => $r->{'month'} );
        }

        $rows = array();
        $table = array();
        $table['cols'] = array(
            array('label' => 'hour', 'type' => 'string'),
            array('label' => $lvl[2]['d']."-".$months[$lvl[2]['m']], 'type' => 'number'),
            array('label' => $lvl[1]['d']."-".$months[$lvl[1]['m']], 'type' => 'number'),
            array('label' => $lvl[0]['d']."-".$months[$lvl[0]['m']], 'type' => 'number'),
            array('label' => 'Average', 'type' => 'number'),
        );

        $query = "SELECT month(transdate) as m, day(transdate) as d, SUM(Amount) as amount FROM VRData WHERE transdate BETWEEN ((SELECT MAX(transdate) FROM VRData) - INTERVAL 2 day) and (SELECT MAX(transdate) FROM VRData) GROUP BY Hrs, transdate";
        $result=DB::select($query);
        $query = "SELECT SUM(Amount) as amount FROM VRData WHERE transdate BETWEEN ((SELECT MAX(transdate) FROM VRData) - INTERVAL 29 day) and (SELECT MAX(transdate) FROM VRData) GROUP BY Hrs";
        $result1=DB::select($query);
//        dd($result1[0]->{'amount'});

        $i=0;
        for($j=1;$j<=24;$j++){
            $temp = array();
            $temp[] = array('v' => $j);
            //" day1";
            $n = ((int) $result[$i++]->{'amount'});
            $temp[] = array('v' => $n);
            //" day2";
            $n = ((int) $result[$i++]->{'amount'});
            $temp[] = array('v' => $n);
            //" day3";
            $n = ((int) $result[$i++]->{'amount'});
            $temp[] = array('v' => $n);
            //" avareg";
            $n = ((int) ((int) $result1[$j-1]->{'amount'}));
            $average= ((int) ($n/30));
            $temp[] = array('v' => $average);
            $rows[] = array('c' => $temp);


        }


        $table['rows'] = $rows;

        $jsonTable = json_encode($table);

        return $jsonTable;
    }

    //.......................JSON Data For VR easy-noneasy chart in VR................................
    public function VREasyChart(){
        try{
            $query = "SELECT SUM(AK_VR_Daily.Amount) as Amount, client.easy_client as id FROM AK_VR_Daily JOIN client where year(AK_VR_Daily.transdate)= year(CURDATE() - INTERVAL 1 day) and month(AK_VR_Daily.transdate)= month(CURDATE() - INTERVAL 1 day) and client.client_id=AK_VR_Daily.client_id group by client.easy_client";
            DB::enableQueryLog();
            $result=DB::select($query);
            $easy=0;
            $nonEasy=0;
            foreach($result as $r) {
                $id=$r->{'id'}+0;
                if($id<=0){
                    $easy+=$r->{'Amount'};
                }
                else {
                    $nonEasy+=$r->{'Amount'};
                }
            }
            $rows = array();
            $table = array();
            $table['cols'] = array(

                array('label' => 'Easy Non Easy', 'type' => 'string'),
                array('label' => 'Total Amount', 'type' => 'number')

            );
            $rows[] = array('c' => array(array('v' => "Others"),array('v' => (int)$easy)));
            $rows[] = array('c' => array(array('v' => "Easy"),array('v' => (int)$nonEasy)));

            $table['rows'] = $rows;

            $jsonTable = json_encode($table);

            return $jsonTable;
        }catch(Exception $e){
            \LOG::error($e-getMessage());
        }
    }

    //.......................JSON Data For VR department wise chart in VR................................
    public function VRDepartmentChart(){
        try{
            $query = "SELECT SUM(AK_VR_Daily.Amount) as Amount, client.Department as id FROM AK_VR_Daily JOIN client where year(AK_VR_Daily.transdate)= year(CURDATE() - INTERVAL 1 day) and month(AK_VR_Daily.transdate)= month(CURDATE() - INTERVAL 1 day) and client.client_id=AK_VR_Daily.client_id group by client.Department";
            DB::enableQueryLog();
            $result=DB::select($query);
            $rows = array();
            $table = array();
            $table['cols'] = array(

                array('label' => 'Department', 'type' => 'string'),
                array('label' => 'Total Amount', 'type' => 'number')

            );
            /* Extract the information from $result */
            foreach($result as $r) {
                $rows[] = array('c' => array(array('v' => (string)$r->{'id'}), array('v' => (int)$r->{'Amount'})));
            }

            $table['rows'] = $rows;
            $jsonTable = json_encode($table);

            return $jsonTable;
        }catch(Exception $e){
            \LOG::error($e-getMessage());
        }
    }

    //.......................JSON Data For VR daily wise chart in VR................................
    public function VRDynamicChart(Request $request){
        $year=$request->input('year');
        $month=$request->input('month');
        $quarter=$request->input('quarter');
        $operator=$request->input('operatorName');
        $department=$request->input('department');
        $department1=$request->input('department1');
        $year=str_replace(',', '', $year);
        $query="";
        $query2="";
        $result2="";
        DB::enableQueryLog();

        if($year==null && $quarter==null && $month==null){
            $query = "SELECT YEAR(transdate) as id, SUM(Amount) as Amount FROM AK_VR_Daily GROUP by id";
            $result=DB::select($query);
        }
        elseif ($year!=null && $quarter!=null && $month==null) {
            $query = "SELECT QUARTER(transdate) as id, SUM(Amount) as Amount FROM AK_VR_Daily WHERE YEAR(transdate)=:year GROUP by id";
            $result=DB::select($query, ['year'=>$year]);
        }
        elseif ($year!=null && $quarter==null && $month==null) {
            $query = "SELECT MONTH(transdate) as id, SUM(Amount) as Amount FROM AK_VR_Daily WHERE YEAR(transdate)=:year GROUP by id";
            $result=DB::select($query, ['year'=>$year]);
        }
        elseif ($year!=null && $quarter==null && $month!=null) {
            $query = "SELECT DAY(transdate) as id, SUM(Amount) as Amount FROM AK_VR_Daily WHERE YEAR(transdate)=:year and MONTH(transdate)=:month GROUP by id";
            $result=DB::select($query, ['year'=>$year,'month'=>$month]);
        }

        if($department!=null){
            if($year==null && $quarter==null && $month==null){
                $query2 = "SELECT YEAR(AK_VR_Daily.transdate) as id, SUM(AK_VR_Daily.Amount) as Amount FROM AK_VR_Daily JOIN client where client.client_id=AK_VR_Daily.client_id and client.Department=:department GROUP by id";
                $result2=DB::select($query2, ['department'=>$department]);
            }
            elseif ($year!=null && $quarter!=null && $month==null) {
                $query2 = "SELECT QUARTER(AK_VR_Daily.transdate) as id, SUM(AK_VR_Daily.Amount) as Amount FROM AK_VR_Daily JOIN client where client.client_id=AK_VR_Daily.client_id and client.Department=:department and YEAR(transdate)=:year GROUP by id";
                $result2=DB::select($query2, ['department'=>$department, 'year'=>$year]);
            }
            elseif ($year!=null && $quarter==null && $month==null) {
                $query2 = "SELECT MONTH(AK_VR_Daily.transdate) as id, SUM(AK_VR_Daily.Amount) as Amount FROM AK_VR_Daily JOIN client where client.client_id=AK_VR_Daily.client_id and client.Department=:department and YEAR(transdate)=:year GROUP by id";
                $result2=DB::select($query2, ['department'=>$department, 'year'=>$year]);
            }
            elseif ($year!=null && $quarter==null && $month!=null) {
                $query2 = "SELECT DAY(AK_VR_Daily.transdate) as id, SUM(AK_VR_Daily.Amount) as Amount FROM AK_VR_Daily JOIN client where client.client_id=AK_VR_Daily.client_id and client.Department=:department and YEAR(transdate)=:year and MONTH(AK_VR_Daily.transdate)=:month GROUP by id";
                $result2=DB::select($query2, ['department'=>$department, 'year'=>$year, 'month'=>$month]);
            }
        }

        else if($operator=="Easy"){

            if($year==null && $quarter==null && $month==null){
                $query2 = "SELECT YEAR(f.transdate) id,sum(f.Amount) Amount from ( select t.transdate,t.Amount from AK_VR_Daily t inner join client c on t.client_id=c.client_id WHERE c.easy_client!=0) f group by id";
                $result2=DB::select($query2);
            }
            elseif ($year!=null && $quarter!=null && $month==null) {
                $query2 = "SELECT QUARTER(f.transdate) id,sum(f.Amount) Amount from ( select t.transdate,t.Amount from AK_VR_Daily t inner join client c on t.client_id=c.client_id WHERE c.easy_client!=0 and Year(t.transdate)=:year) f group by id ";
                $result2=DB::select($query2, ['year'=>$year]);
            }
            elseif ($year!=null && $quarter==null && $month==null) {
                $query2 = "SELECT Month(f.transdate) id,sum(f.Amount) Amount from ( select t.transdate,t.Amount from AK_VR_Daily t inner join client c on t.client_id=c.client_id WHERE c.easy_client!=0 and Year(t.transdate)=:year) f group by id ";
                $result2=DB::select($query2, ['year'=>$year]);
            }
            elseif ($year!=null && $quarter==null && $month!=null) {
                $query2 = "SELECT day(f.transdate) id,sum(f.Amount) Amount from ( select t.transdate,t.Amount from AK_VR_Daily t inner join client c on t.client_id=c.client_id WHERE c.easy_client!=0 and Year(t.transdate)=:year and Month(transdate)=:month) f group by id ";
                $result2=DB::select($query2, ['year'=>$year, 'month'=>$month]);
            }


        }

        else if ($operator=="Others"){
            if($year==null && $quarter==null && $month==null){
                $query2 = "SELECT YEAR(f.transdate) id,sum(f.Amount) Amount from ( select t.transdate,t.Amount from AK_VR_Daily t inner join client c on t.client_id=c.client_id WHERE c.easy_client=0) f group by id";
                $result2=DB::select($query2);
            }
            elseif ($year!=null && $quarter!=null && $month==null) {
                $query2 = "SELECT QUARTER(f.transdate) id,sum(f.Amount) Amount from ( select t.transdate,t.Amount from AK_VR_Daily t inner join client c on t.client_id=c.client_id WHERE c.easy_client=0 and Year(t.transdate)=:year) f group by id ";
                $result2=DB::select($query2, ['year'=>$year]);
            }
            elseif ($year!=null && $quarter==null && $month==null) {
                $query2 = "SELECT Month(f.transdate) id,sum(f.Amount) Amount from ( select t.transdate,t.Amount from AK_VR_Daily t inner join client c on t.client_id=c.client_id WHERE c.easy_client=0 and Year(t.transdate)=:year) f group by id ";
                $result2=DB::select($query2, ['year'=>$year]);
            }
            elseif ($year!=null && $quarter==null && $month!=null) {
                $query2 = "SELECT day(f.transdate) id,sum(f.Amount) Amount from ( select t.transdate,t.Amount from AK_VR_Daily t inner join client c on t.client_id=c.client_id WHERE c.easy_client=0 and Year(t.transdate)=:year and Month(transdate)=:month) f group by id ";
                $result2=DB::select($query2, ['year'=>$year, 'month'=>$month]);
            }
        }
        else {
            $test= Operator::all();
            $operatorList="";
            $operatorList2="";
            foreach ($test as $value) {
                $operatorList[$value->{'operator_name'}]=$value->{'operator_id'};
            }

            if(isset($operatorList[$operator]))
            $operator= (int)($operatorList[$operator]+0);

            if($year==null && $quarter==null && $month==null && $operator!=null){
                $query2 = "SELECT YEAR(transdate) as id, SUM(Amount) as Amount FROM AK_VR_Daily WHERE operator_id=:operator GROUP by id";
                $result2=DB::select($query2, ['operator'=>$operator]);
            }
            elseif ($year!=null && $quarter!=null && $month==null && $operator!=null) {
                $query2 = "SELECT QUARTER(transdate) as id, SUM(Amount) as Amount FROM AK_VR_Daily WHERE operator_id=:operator and YEAR(transdate)=:year GROUP by id";
                $result2=DB::select($query2, ['operator'=>$operator, 'year'=>$year]);
            }
            elseif ($year!=null && $quarter==null && $month==null && $operator!=null) {
                $query2 = "SELECT MONTH(transdate) as id, SUM(Amount) as Amount FROM AK_VR_Daily WHERE  operator_id=:operator and YEAR(transdate)=:year GROUP by id";
                $result2=DB::select($query2, ['operator'=>$operator, 'year'=>$year]);
            }
            elseif ($year!=null && $quarter==null && $month!=null && $operator!=null) {
                $query2 = "SELECT DAY(transdate) as id, SUM(Amount) as Amount FROM AK_VR_Daily WHERE  operator_id=:operator and YEAR(transdate)=:year and MONTH(transdate)=:month GROUP by id";
                $result2=DB::select($query2, ['operator'=>$operator, 'year'=>$year, 'month'=>$month]);
            }
        }


        $masterTable = array();
        // $mTable = array();
        $rows = array();
        $table = array();
        $table2 = array();
        $rows2 = array();

        $table['cols'] = array(
            array('label' => 'id', 'type' => 'string'),
            array('label' => 'Total Amount', 'type' => 'number'),
            array('label' => 'hello', 'type' => 'number'),
            array('role' => 'style', 'type' => 'string')
        );


        foreach($result as $r) {
            $temp = array();
            $temp[] = array('v' => (string) $r->{'id'});
            $n = ((int) $r->{'Amount'});

            $temp[] = array('v' => $n);

            $temp[] = array('v' => ($n/1000000));
            $temp[] = array('v' => "#FAC40F");
            $rows[] = array('c' => $temp);
        }
        if( $year != null && $month != null && $operator==null && $department==null){
            $query3 = "SELECT COUNT(transdate) cnt FROM `AK_VR_Daily` WHERE (SELECT year(max(transdate)) FROM AK_VR_Daily) = :year and (SELECT month(max(transdate)) FROM AK_VR_Daily) = :month";
            $result3=DB::select($query3, ['year'=>$year, 'month'=>$month]);
            if($result3[0]->{'cnt'}){
                $query4 = "SELECT transdate a, weekday(transdate) weekday, sum(Amount) amount, (SELECT sum(Amount) FROM AK_VR_Daily WHERE transdate = (a - INTERVAL 1 day)) priviousday , (100 + ( 100 * ((SELECT sum(Amount) FROM AK_VR_Daily WHERE transdate = (a - INTERVAL 7 day)) - (SELECT sum(Amount) FROM AK_VR_Daily WHERE transdate = (a - INTERVAL 8 day))) / (SELECT sum(Amount) FROM AK_VR_Daily WHERE transdate = (a - INTERVAL 8 day)) ) ) per FROM AK_VR_Daily WHERE transdate > (SELECT max(transdate) - INTERVAL 7 day FROM AK_VR_Daily) GROUP by transdate";
                DB::enableQueryLog();
                $result4 = DB::select($query4);
                $weekdayper = [];
                $lastDate = "";
                $lastAmount = 0;
                $lastweekday = "";
                foreach($result4 as $r4){
                    $weekdayper[$r4->{'weekday'}] = $r4->{'per'};
                    $lastDate = $r4->{'a'};
                    $lastAmount = $r4->{'amount'};
                    $lastweekday = $r4->{'weekday'};
                }

                $date = date_create($lastDate);
                $cday = date_format($date,"d");
                $month = date_format($date,"m") - 1;
                $year = date_format($date,"Y");
                $lday=cal_days_in_month(CAL_GREGORIAN,$month,$year);

                $j = 0; $lastAmountStore = $lastAmount;
                for($i=$cday+1;$i<=$lday;$i++){
                    if($j%7==0) $lastAmount = $lastAmountStore;
                    $j++;
                    $lastweekday++;
                    $lastweekday%=7;
                    $lastAmount = ($lastAmount/100)*$weekdayper[$lastweekday];
                    $rows[] = array('c' => array(
                        array('v' => (string) $i),
                        array('v' => (int) $lastAmount),
                        array('v' => ((int) $lastAmount)/1000000),
                        array('v' => "#F9E79F")
                    ));
                }
            }
        }

        $table['rows'] = $rows;

        if($operator!=null || $department!=null ){
            $table2['cols'] = array(
                array('label' => 'id', 'type' => 'string'),
                array('label' => $request->input('operatorName').$request->input('department').' Amount', 'type' => 'number'),
                array('label' => 'h', 'type' => 'number'),
                array('role' => 'style', 'type' => 'string')
            );

            foreach($result2 as $r) {
                $temp = array();
                $temp[] = array('v' => (string) $r->{'id'});
                $n = ((int) $r->{'Amount'});
                $temp[] = array('v' => $n);
                $temp[] = array('v' => ($n/1000000));
                $temp[] = array('v' => "#FAC40F");

                $rows2[] = array('c' => $temp);
            }
            $table2['rows'] = $rows2;
        }
        if($department1!=null){
            $masterTable['data'] = array(
                $table2, $table
            );
        }
        else
            $masterTable['data'] = array(
                $table, $table2
            );


        // convert data into JSON format
        $jsonTable = json_encode($masterTable);
        return $jsonTable;

    }

    //.......................JSON Data For VR chart in VR which compare multiple day,month,year................................
    public function VRDynamicChartFilter(Request $request){
        $pdoparameter = [];
        $year=$request->input('year');
        $month=$request->input('month');
        $day=$request->input('day');
        $week_day=$request->input('weekday');
        $operator=$request->input('operator');
        $department=$request->input('department');
        $easy=$request->input('easy');
        $month_data = explode(",", $month);
        $day_data = explode(",", $week_day);
        $year_data = explode(",", $year);


        if($request->input('month')!=null){
            $data=explode(',',$request->input('month'));
            $month="(";
            foreach($data as $key => $value){
                $pdoparameter['month'.$key]=$value;
                if($key==0)$month.=' :month'.$key;
                else $month.=',:month'.$key;
            }
            $month.=") ";
        }

        if($request->input('day')!=null){
            $data=explode(',',$request->input('day'));
            $day="(";
            foreach($data as $key => $value){
                $pdoparameter['day'.$key]=$value;
                if($key==0)$day.=' :day'.$key;
                else $day.=',:day'.$key;
            }
            $day.=") ";
        }

        if($request->input('weekday')!=null){
            $data=explode(',',$request->input('weekday'));
            $week_day="(";
            foreach($data as $key => $value){
                $pdoparameter['weekday'.$key]=$value;
                if($key==0)$week_day.=' :weekday'.$key;
                else $week_day.=',:weekday'.$key;
            }
            $week_day.=") ";
        }

        if($request->input('operator')!=null){
            $operator ="( :operator )";
            $pdoparameter['operator']=$request->input('operator');
        }

        if($request->input('department')!=null){
            $department ="( :department )";
            $pdoparameter['department']=$request->input('department');
        }

//        if($request->input('easy')!=null){
//            $easy ="( :easy )";
//            $pdoparameter['easy']=$request->input('easy');
//        }


        DB::enableQueryLog();
        if($month==null && $year==null && $day!=null){
            $string = "select day(AK_VR_Daily.transdate) as day,sum(AK_VR_Daily.Amount) as amount from AK_VR_Daily ";
            if($operator!=null)  $string.=", operator ";
            if($department!=null||$easy!=null)  $string.=", client ";
            $string.=" where Day(AK_VR_Daily.transdate) in ".$day." ";
            if($operator!=null)  $string.=" and operator.operator_name=".$operator." and operator.operator_id=AK_VR_Daily.operator_id ";
            if($department!=null)  $string.=" and client.Department=".$department." and client.client_id=AK_VR_Daily.client_id ";
            if($easy!=null){
                if($easy=="Others")$string.=" and client.easy_client=0 ";
                if($easy=="Easy")$string.=" and client.easy_client!=0 ";
            }
            $string.=" Group by day";
            $result = DB::select($string, $pdoparameter);

            $rows = array();
            $table = array();
            $tempArray=array();
            $tempArray[]=array('label' => 'id', 'type' => 'string');
            $tempArray[]=array('label' => " Amount  ", 'type' => 'number');
            $tempArray[]=array('label' => " Amount  ", 'type' => 'number');

            $table['cols']=$tempArray;
            $tempArray=array();
            $temp=array();
            foreach($result as $r){
                $temp=array();
                $temp[] = array('v' => (string) $r->{'day'});
                $temp[] = array('v' => $r->{'amount'});
                $temp[] = array('v' => ($r->{'amount'}/1000000));
                $rows[] = array('c' => $temp);
            }
            $table['rows'] = $rows;
            $jsonTable = json_encode($table);
            echo $jsonTable;
        }
        else if($day==null && $year==null && $month!=null){
            $string = "select month(AK_VR_Daily.transdate) as month, sum(AK_VR_Daily.Amount) as amount from AK_VR_Daily ";
            if($operator!=null)  $string.=", operator ";
            if($department!=null||$easy!=null)  $string.=", client ";
            $string.=" where Month(AK_VR_Daily.transdate) in ".$month." ";
            if($operator!=null)  $string.=" and operator.operator_name=".$operator." and operator.operator_id=AK_VR_Daily.operator_id ";
            if($department!=null)  $string.=" and client.Department=".$department." and client.client_id=AK_VR_Daily.client_id ";
            if($easy!=null){
                if($easy=="Others")$string.=" and client.easy_client=0 ";
                if($easy=="Easy")$string.=" and client.easy_client!=0 ";
            }
            $string.=" Group by month";
            $result = DB::select($string, $pdoparameter);

            $rows = array();
            $table = array();
            $tempArray=array();
            $tempArray[]=array('label' => 'id', 'type' => 'string');
            $tempArray[]=array('label' => " Amount  ", 'type' => 'number');
            $tempArray[]=array('label' => " Amount  ", 'type' => 'number');
            $table['cols']=$tempArray;
            $temp=array();
            foreach($result as $r){
                $temp=array();
                $temp[] = array('v' => (string) $r->{'month'});
                $temp[] = array('v' => $r->{'amount'});
                $temp[] = array('v' => ($r->{'amount'}/1000000));
                $rows[] = array('c' => $temp);
            }
            $table['rows'] = $rows;
            $jsonTable = json_encode($table);
            echo $jsonTable;

        }
        else if($day==null && $month==null && $year!=null){
            if($request->input('year')!=null){
                $data=explode(',',$request->input('year'));
                $year="(";
                foreach($data as $key => $value){
                    $pdoparameter['year'.$key]=$value;
                    if($key==0)$year.=' :year'.$key;
                    else $year.=',:year'.$key;
                }
                $year.=") ";
            }
            $string = "select YEAR(AK_VR_Daily.transdate) as y, sum(AK_VR_Daily.Amount) as amount from AK_VR_Daily ";
            if($operator!=null)  $string.=", operator ";
            if($department!=null||$easy!=null)  $string.=", client ";
            $string.=" where YEAR(AK_VR_Daily.transdate) in ".$year." ";
            if($operator!=null)  $string.=" and operator.operator_name=".$operator." and operator.operator_id=AK_VR_Daily.operator_id ";
            if($department!=null)  $string.=" and client.Department=".$department." and client.client_id=AK_VR_Daily.client_id ";
            if($easy!=null){
                if($easy=="Others")$string.=" and client.easy_client=0 ";
                if($easy=="Easy")$string.=" and client.easy_client!=0 ";
            }
            $string.=" Group by y";
            $result = DB::select($string, $pdoparameter);
            $rows = array();
            $table = array();
            $tempArray=array();
            $tempArray[]=array('label' => 'id', 'type' => 'string');
            $tempArray[]=array('label' => " Amount  ", 'type' => 'number');
            $tempArray[]=array('label' => " Amount  ", 'type' => 'number');
            $table['cols']=$tempArray;
            foreach($result as $r){
                $temp=array();
                $temp[] = array('v' => (string) $r->{'y'});
                $temp[] = array('v' => $r->{'amount'});
                $temp[] = array('v' => ($r->{'amount'}/1000000));
                $rows[] = array('c' => $temp);
            }
            $table['rows'] = $rows;
            $jsonTable = json_encode($table);
            return $jsonTable;

        }
        else if ($year==null && $day!=null && $month!=null){
            $string = "select month(AK_VR_Daily.transdate) as month,Day(AK_VR_Daily.transdate) as day, sum(AK_VR_Daily.Amount) as amount from AK_VR_Daily ";
            if($operator!=null)  $string.=", operator ";
            if($department!=null||$easy!=null)  $string.=", client ";
            $string.=" where Day(AK_VR_Daily.transdate) in ".$day." ";
            $string.=" and Month(AK_VR_Daily.transdate) in ".$month." ";
            if($operator!=null)  $string.=" and operator.operator_name=".$operator." and operator.operator_id=AK_VR_Daily.operator_id ";
            if($department!=null)  $string.=" and client.Department=".$department." and client.client_id=AK_VR_Daily.client_id ";
            if($easy!=null){
                if($easy=="Others")$string.=" and client.easy_client=0 ";
                if($easy=="Easy")$string.=" and client.easy_client!=0 ";
            }
            $string.=" Group by day,month";
            $result = DB::select($string, $pdoparameter);

            $rows = array();
            $table = array();
            $tempArray=array();
            $tempArray[]=array('label' => 'id', 'type' => 'string');
            $queryparameter="";
            $query = "Select distinct(day(AK_VR_Daily.transdate)) as day from AK_VR_Daily where day(AK_VR_Daily.transdate) in ".$day." ";
            if($request->input('day')!=null){
                $data=explode(',',$request->input('day'));
                foreach($data as $key => $value){
                    $queryparameter['day'.$key]=$value;
                }
            }
            $resultMonth=DB::select($query, $queryparameter);
            foreach($resultMonth as $m){
                $tempArray[]=array('label' => "Day: ".$m->{'day'}." Amount  ", 'type' => 'number');
                $tempArray[]=array('label' => "Day: ".$m->{'day'}." Amount  ", 'type' => 'number');
            }
            $table['cols']=$tempArray;
            $tempArray=array();
            foreach($result as $r) {
                $tempArray[$r->{'month'}][$r->{'day'}]=(int)$r->{'amount'};
            }
            foreach($tempArray as $key => $r){
                $temp=array();

                $temp[] = array('v' => (string) $key);
                foreach($r as $r1){
                    $temp[] = array('v' => $r1);
                    $temp[] = array('v' => ($r1/1000000));
                }
                $rows[] = array('c' => $temp);
            }
            $table['rows'] = $rows;
            $jsonTable = json_encode($table);
            return $jsonTable;

        }
        else if ($year!=null && $day!=null && $month==null){
            if($request->input('year')!=null){
                $data=explode(',',$request->input('year'));
                $year="(";
                foreach($data as $key => $value){
                    $pdoparameter['year'.$key]=$value;
                    if($key==0)$year.=' :year'.$key;
                    else $year.=',:year'.$key;
                }
                $year.=") ";
            }
            $string = "select year(AK_VR_Daily.transdate) as year,day(AK_VR_Daily.transdate) as day, sum(AK_VR_Daily.Amount) as amount from AK_VR_Daily ";
            if($operator!=null)  $string.=", operator ";
            if($department!=null||$easy!=null)  $string.=", client ";
            $string.=" where year(AK_VR_Daily.transdate) in ".$year." ";
            $string.=" and day(AK_VR_Daily.transdate) in ".$day." ";
            if($operator!=null)  $string.=" and operator.operator_name=".$operator." and operator.operator_id=AK_VR_Daily.operator_id ";
            if($department!=null)  $string.=" and client.Department=".$department." and client.client_id=AK_VR_Daily.client_id ";
            if($easy!=null){
                if($easy=="Others")$string.=" and client.easy_client=0 ";
                if($easy=="Easy")$string.=" and client.easy_client!=0 ";
            }
            $string.=" Group by year,day";
            $result = DB::select($string, $pdoparameter);
            $rows = array();
            $table = array();
            $tempArray=array();
            $tempArray[]=array('label' => 'id', 'type' => 'string');
            $queryparameter="";
            $query = "Select distinct(day(AK_VR_Daily.transdate)) as day from AK_VR_Daily where day(AK_VR_Daily.transdate) in ".$day." ";
            if($request->input('day')!=null){
                $data=explode(',',$request->input('day'));
                foreach($data as $key => $value){
                    $queryparameter['day'.$key]=$value;
                }
            }
            $resultMonth=DB::select($query, $queryparameter);
            foreach($resultMonth as $m){
                $tempArray[]=array('label' => "Day: ".$m->{'day'}." Amount  ", 'type' => 'number');
                $tempArray[]=array('label' => "Day: ".$m->{'day'}." Amount  ", 'type' => 'number');
            }
            $table['cols']=$tempArray;
            $tempArray=array();
            foreach($result as $r) {
                $tempArray[$r->{'year'}][$r->{'day'}]=(int)$r->{'amount'};
            }
            foreach($tempArray as $key => $r){
                $temp=array();
                $temp[] = array('v' => (string) $key);
                foreach($r as $r1){
                    $temp[] = array('v' => $r1);
                    $temp[] = array('v' => ($r1/1000000));
                }
                $rows[] = array('c' => $temp);

            }
            $table['rows'] = $rows;
            $jsonTable = json_encode($table);
            return $jsonTable;


        }
        else if ($year!=null && $day==null && $month!=null){
            if($request->input('year')!=null){
                $data=explode(',',$request->input('year'));
                $year="(";
                foreach($data as $key => $value){
                    $pdoparameter['year'.$key]=$value;
                    if($key==0)$year.=' :year'.$key;
                    else $year.=',:year'.$key;
                }
                $year.=") ";
            }
            $string = "select year(AK_VR_Daily.transdate) as year,month(AK_VR_Daily.transdate) as month, sum(AK_VR_Daily.Amount) as amount from AK_VR_Daily ";
            if($operator!=null)  $string.=", operator ";
            if($department!=null||$easy!=null)  $string.=", client ";
            $string.=" where year(AK_VR_Daily.transdate) in ".$year." ";
            $string.=" and Month(AK_VR_Daily.transdate) in ".$month." ";
            if($operator!=null)  $string.=" and operator.operator_name=".$operator." and operator.operator_id=AK_VR_Daily.operator_id ";
            if($department!=null)  $string.=" and client.Department=".$department." and client.client_id=AK_VR_Daily.client_id ";
            if($easy!=null){
                if($easy=="Others")$string.=" and client.easy_client=0 ";
                if($easy=="Easy")$string.=" and client.easy_client!=0 ";
            }
            $string.=" Group by year,month";
            $result = DB::select($string, $pdoparameter);

            $rows = array();
            $table = array();
            $tempArray=array();
            $tempArray[]=array('label' => 'id', 'type' => 'string');
            $queryparameter="";
            $query = "Select distinct(month(AK_VR_Daily.transdate)) as month from AK_VR_Daily where month(AK_VR_Daily.transdate) in ".$month." ";
            if($request->input('month')!=null){
                $data=explode(',',$request->input('month'));
                foreach($data as $key => $value){
                    $queryparameter['month'.$key]=$value;
                }
            }
            $resultMonth=DB::select($query, $queryparameter);
            foreach($resultMonth as $m){
                $tempArray[]=array('label' => "Month: ".$m->{'month'}." Amount  ", 'type' => 'number');
                $tempArray[]=array('label' => "Month: ".$m->{'month'}." Amount  ", 'type' => 'number');
            }
            $table['cols']=$tempArray;
            $tempArray=array();
            foreach($result as $r) {
                $tempArray[$r->{'year'}][$r->{'month'}]=(int)$r->{'amount'};
            }
            foreach($tempArray as $key => $r){
                $temp=array();
                $temp[] = array('v' => (string) $key);
                foreach($r as $r1){
                    $temp[] = array('v' => $r1);
                    $temp[] = array('v' => ($r1/1000000));
                }
                $rows[] = array('c' => $temp);

            }
            $table['rows'] = $rows;
            $jsonTable = json_encode($table);
            return $jsonTable;

        }
        else if ($year!=null && $day!=null && $month!=null){
            $rows = array();
            $table = array();
            $tempArray=array();
            $tempArray[]=array('label' => 'id', 'type' => 'string');
            $queryparameter="";
            $query = "Select distinct(day(AK_VR_Daily.transdate)) as day from AK_VR_Daily where day(AK_VR_Daily.transdate) in ".$day." ";
            if($request->input('day')!=null){
                $data=explode(',',$request->input('day'));
                foreach($data as $key => $value){
                    $queryparameter['day'.$key]=$value;
                }
            }
            $resultMonth=DB::select($query, $queryparameter);
            foreach($resultMonth as $m){
                $tempArray[]=array('label' => "Day: ".$m->{'day'}." Amount  ", 'type' => 'number');
                $tempArray[]=array('label' => "Day: ".$m->{'day'}." Amount  ", 'type' => 'number');
            }
            $table['cols']=$tempArray;
            $tempArray=array();
            foreach($year_data as $r2){
                $string = "select year(AK_VR_Daily.transdate) as year,month(AK_VR_Daily.transdate) as month,Day(AK_VR_Daily.transdate) as day, sum(AK_VR_Daily.Amount) as amount from AK_VR_Daily ";
                if($operator!=null)  $string.=", operator ";
                if($department!=null||$easy!=null)  $string.=", client ";
                $string.=" where Day(AK_VR_Daily.transdate) in ".$day." ";
                $string.=" and Month(AK_VR_Daily.transdate) in ".$month." ";
                $string.=" and year(AK_VR_Daily.transdate) = :year";
                $pdoparameter['year'] = $r2;
                if($operator!=null)  $string.=" and operator.operator_name=".$operator." and operator.operator_id=AK_VR_Daily.operator_id ";
                if($department!=null)  $string.=" and client.Department=".$department." and client.client_id=AK_VR_Daily.client_id ";
                if($easy!=null){
                    if($easy=="Others")$string.=" and client.easy_client=0 ";
                    if($easy=="Easy")$string.=" and client.easy_client!=0 ";
                }
                $string.=" Group by year,month,day";
                $result = DB::select($string, $pdoparameter);
                foreach($result as $r) {
                    $tempArray[$r->{'month'}][$r->{'day'}]=(int)$r->{'amount'};
                }
                foreach($tempArray as $key => $r){
                    $temp=array();

                    $temp[] = array('v' => (string) $key.", ".$r2);
                    foreach($r as $r1){
                        $temp[] = array('v' => $r1);
                        $temp[] = array('v' => ($r1/1000000));
                    }
                    $rows[] = array('c' => $temp);
                    $temp=array();
                }
            }
            $table['rows'] = $rows;
            //  var_dump($table);
            $jsonTable = json_encode($table);
            return $jsonTable;

        }



    }



}
