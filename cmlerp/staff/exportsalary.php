<?php
$filename="salary.xls";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

echo "编号"."\t";
echo "姓名"."\t";
echo "职级"."\t";
echo "基本工资"."\t";
echo "加班费"."\t";
echo "应扣休假"."\t";
echo "手工提成"."\t";
echo "销售提成"."\t";
echo "岗位工资"."\t";
echo "全勤奖"."\t";
echo "生活补贴"."\t";
echo "宝贝"."\t";
echo "奖金"."\t";
echo "应发工资"."\t";
echo "月份"."\n";

$conn = mysql_connect("localhost","root","123qaz");
mysql_select_db("cmlerp");
$table=$_GET['action']?$_GET['action']:'salary';
$agencyid=$_GET['agencyid'];
$batch=$_GET['batch'];
if($table=='salaryhistory'){

$sql='select * from s_salaryhistory where month='.$batch.' and  agencyid ='.$agencyid.' ORDER BY  salary_id DESC ';
}else{
$sql='select * from s_salary where agencyid ='.$agencyid.' ORDER BY  salary_id DESC ';
}
mysql_query("SET NAMES GBK");
$result=mysql_query($sql); 
while($inrrs =mysql_fetch_array($result)){
echo $inrrs['employee_id']."\t";
$employee=mysql_query("select * from s_employee e INNER JOIN  s_employeelevel el on e.employeelevelid=el.employeelevel_id   where  e.employee_id=".$inrrs['employee_id']." LIMIT 0 , 1 ");
while($row =mysql_fetch_array($employee)){
$employeename=$row['employee_name'];
$employeelevelname=$row['employeelevel_name'];
}

echo $employeename."\t";
echo $employeelevelname."\t";
echo $inrrs["basic"]."\t";
echo $inrrs['otpay']."\t";
echo $inrrs['leavepay']."\t";
echo $inrrs['ser_royalties']."\t";
echo $inrrs['sel_royalties']."\t";
echo $inrrs['postwage']."\t";
echo $inrrs['fullattendance']."\t";
echo $inrrs['livingallowance']."\t";
echo $inrrs['fine']."\t";
echo $inrrs['bonus']."\t";
echo $inrrs['wagespayable']."\t";
echo $inrrs['batch']."\n";
}

?>