<div id=siteUrl>
	<?php echo site_url()?>
</div>

<?php
$lastCloseFileName = "lastVisit.txt";
if (!file_exists($lastCloseFileName)) {
	touch($lastCloseFileName);
}

$lastCloseDate=filemtime($lastCloseFileName);
//echo "La date de derniere fermeture etait ".date("l d M Y H:i:s.", $lastCloseDate)." ".$lastCloseDate."</br>";
$nextCloseDate=date(strtotime('next thursday', $lastCloseDate));

//echo "Prochaine fermeture prevue : ". date('l d M Y H:i:s', $nextCloseDate) ." ". $nextCloseDate ."</br>";

$now=date(time());
//echo "Now:       ". date('l d M Y H:i:s') ." ".date(time())." ".$now."</br>";

//echo "<br>des que qq'un se connecte apres le ".date('l d M Y H:i:s', $nextCloseDate)."<br>";
if ($now > $nextCloseDate) {
	$nextCloseDate=date(strtotime('next thursday', $now));
	$closeDate=date(strtotime('next saturday', $nextCloseDate));
//	echo "Cloturer du ".date('l d M Y H:i:s', $lastCloseDate)." ".$lastCloseDate." au ".date('l d M Y H:i:s', $closeDate)." ".$closeDate."</br>";
	$sql = $this->Resa_model->validateResaByDate($lastCloseDate, $closeDate);
//	echo $sql."<br>";
//	echo "Changer la date de cloture a ".date('l d M Y H:i:s', $now)."</br>";
//	echo "Nouvelle fermeture prevue : ". date('l d M Y H:i:s', $nextCloseDate) ." ". $nextCloseDate ."</br>";
	touch($lastCloseFileName);
	file_put_contents($lastCloseFileName, $closeDate);
}

//echo "check balance closing</br>";

$lastBalanceFileName = "lastBalance.txt";
if (!file_exists($lastBalanceFileName)) {
    touch($lastBalanceFileName);
}

//set last balance date
//touch($lastBalanceFileName, strtotime('06-01-2020'));

$lastBalanceDate=filemtime($lastBalanceFileName);
//echo "La date de derniere balance etait ".date("l d M Y H:i:s.", $lastBalanceDate)." ".$lastBalanceDate."</br>";
$nextBalanceDate=date(strtotime('+1 month', $lastBalanceDate));
//echo "Prochaine balance prevue : ". date('l d M Y H:i:s', $nextBalanceDate) ." ". $nextBalanceDate ."</br>";
if ($now > $nextBalanceDate) {
    //Update net balance date file with current date
    file_put_contents($lastBalanceFileName, date("l d M Y H:i:s.", $nextBalanceDate));
    touch($lastBalanceFileName, $nextBalanceDate);
//    $cost = $this->Cost_model->setBalance($lastBalanceDate);
    
//    print_r($cost);
}
/*
$date[] = array("year" => "2021","month"=> "4");
$date[] = array("year" => "2021","month"=> "5");
$date[] = array("year" => "2021","month"=> "6");

$users = $this->User_model->get_users(TRUE);

foreach ($users as $user) {
    foreach ($date as $curDate) {
        $where = array('user_id'=>$user['id'], 'YEAR(month_paided)' => $curDate['year'], 'MONTH(month_paided)' => $curDate['month'], 'status' => 3 );
        $payments = $this->Payment_model->get_payment_where($where);
        $totPayment = 0;
        foreach ($payments as $payment) {
            $totPayment += $payment["amount"];
        }
        print("<br>");
        //Get month paid resa
        $bill = $this->Resa_model->getResaSummary($curDate['year'], $curDate['month'], $user['id']);
        //Get month paid debt
        $DBCost = current($this->Cost_model->get_cost_where(array('user_id' => $user['id'], 'YEAR(month_paided)' => $curDate['year'], 'MONTH(month_paided)' => $curDate['month'] )));
        $prevDate = date("Y-m-d", mktime(0, 0, 0, $curDate['month']-1, 1, $curDate['year']));
        $prevDateList = explode("-",$prevDate);
        $DBCostPrev = current($this->Cost_model->get_cost_where(array('user_id' => $user['id'], 'YEAR(month_paided)' => $prevDateList[0], 'MONTH(month_paided)' => $prevDateList[1] )));
        if(!$DBCostPrev) {
            $DBCostPrev["debt"]=0;
        }
        $cost['month_paided'] = date("Y-m-d", mktime(0, 0, 0, $curDate['month'], 1, $curDate['year']));
        $cost['paid'] = $totPayment;
        $cost['user_id'] = $user['id'];
        $cost['debt'] = round(($DBCostPrev["debt"] + $bill['sum']['total'] - $totPayment),2);
        if($DBCost) {
            $this->Cost_model->update($DBCost["id"], $cost);
            print_r($DBCost);
            print("<br>");
            echo "update: ".$user['id']." ".$curDate['year']." ".$curDate['month']." => ".$DBCostPrev["debt"]." + ".$bill['sum']['total']." - ".$totPayment."<br>";
            print_r($cost);
            print("<br>");
        } else {
            $this->Cost_model->create($cost);
            echo "create: ".$user['id']." ".$curDate['year']." ".$curDate['month']." => ".$bill['sum']['total']." - ".$totPayment."<br>";
        }
    }
}

echo "<br>";
*/
?>

<div class="holder_content">
<section class="container_left">
<form class="form" method="post" action="<?php echo site_url()?>/login">
	<div>
		<label for="username" >Mail:</label>
		<input class="InputText" type="text" size="20" name="username" value="<?php echo set_value('username'); ?>" /></br>
		<label for="pass">Mot de passe:</label>
		<input class="InputText" type="password" size="20" name="pass"/>
		<span>
		<?php
			if(validation_errors()) {
				echo validation_errors();
			}
		?>
		</span>
		<input class="InputSubmit" type="submit" value="Login"/>
	</div>
</form>
</section>
<section class="container_rigth">
<div>
	<a class="button" href="mailto:loupiot@free.fr">Envoyer un mail</a><br/>
	<a class="button" href="<?php echo site_url()?>/contact/viewLogin"  class="last">Nous contacter</a>
</div>
</section>
<div class="holder_content_separator"></div>
</div>

<div class="holder_content">
<h3>Actualit&eacute;s</h3>
<div id=newsContent></div>

</div>




