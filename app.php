<?php
$url = 'https://www.sknt.ru/job/frontend/data.json';
$path = realpath(dirname(__FILE__))."/data.json";
file_put_contents($path, file_get_contents($url));
$data = file_get_contents("data.json");
$res = json_decode($data,true);
function normform($number, $after) {//месяц,месяцы,месяца
	$cases = array (2, 0, 1, 1, 1, 2);
	return $number.' '.$after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
}
if($res > 0){
	echo '<div id="gate" class="container"><div class="count">';
	for($i=0;$i<count($res["tarifs"]);$i++) {
		$result[$i] = $res["tarifs"][$i];
		$title[] = $res["tarifs"][$i]["title"];
		$link[] = $res["tarifs"][$i]["link"];
		$speed[] = $res["tarifs"][$i]["speed"];
		for($j=0;$j<count($res["tarifs"][$i]["tarifs"]);$j++) {
			usort($res["tarifs"][$i]["tarifs"], function($a, $b){
				return ($a['pay_period'] - $b['pay_period']);
			});

			$price_add[$i][$j] = $res["tarifs"][$i]["tarifs"][$j]["price_add"];
			if($price_add[$i][$j] > 0) {
				$price[$i][$j] = $res["tarifs"][$i]["tarifs"][$j]["price"]+$price_add[$i][$j];
			} else {
				$price[$i][$j] = $res["tarifs"][$i]["tarifs"][$j]["price"];
			}
			$pay_period[$i][$j] = $res["tarifs"][$i]["tarifs"][$j]["pay_period"];			
			$price_m[$i][$j] = $price[$i][$j]/$pay_period[$i][$j];
			$new_payday[$i][$j] = $res["tarifs"][$i]["tarifs"][$j]["new_payday"];
			$string = strlen($new_payday[$i][$j]);
			if($string == 15 && strstr($new_payday[$i][$j],'+')) {//если +
				$new[$i][$j] = explode("+",$new_payday[$i][$j]);
				$new[$i][$j][1] = '+'.$new[$i][$j][1];
			} elseif ($string == 15 && strstr($new_payday[$i][$j],'-')) {//если -
				$new[$i][$j] = explode("-",$new_payday[$i][$j]);
				$new[$i][$j][1] = '-'.$new[$i][$j][1];
			} elseif ($string > 10 && is_int($new_payday[$i][$j])) {//попытка получения таймзоны средствами php
				$new[$i][$j][0] = substr($new_payday[$i][$j],10);
				$new[$i][$j][1] = date_default_timezone_get();
			} else {//поломалось
				$new[$i][$j][0] = time()+($pay_period[$i][$j]*2629743);
				$new[$i][$j][1] = date_default_timezone_get();
			}
			
			$date[$i][$j] = new DateTime(date("Y-m-d H:i",$new[$i][$j][0]));
			$date[$i][$j]->setTimeZone(new DateTimeZone($new[$i][$j][1]));
			$new_date[$i][$j] = $date[$i][$j]->format('d.m.Y H:i');
			$disc[$i][$j] = ($price_m[$i][0]-$price_m[$i][$j])*$pay_period[$i][$j];
		}
		$min[] = min($price_m[$i]);
		$max[] = max($price_m[$i]);	
		if(isset($res["tarifs"][$i]["free_options"])) {
			$free[$i] = $res["tarifs"][$i]["free_options"];
			$str[$i] = implode('<br>',$free[$i]);
			echo '<div id="rate" class="rate"><div class="title">Тариф "'.$title[$i].'"</div><div id="rate-title-'.$i.'" data-item="'.$i.'" class="section"><div class="speed">'.$speed[$i].' Мбит/с</div><div class="price">'.$min[$i].' – '.$max[$i].' ₽/мес</div><div class="disc">'.$str[$i].'</div></div><div class="link"><a name="узнать подробнее на сайте www.sknt.ru" target="_blank" href="'.$link[$i].'">узнать подробнее на сайте www.sknt.ru</a></div></div>';
		} else {
			echo '<div id="rate" class="rate"><div class="title">Тариф "'.$title[$i].'"</div><div id="rate-title-'.$i.'" data-item="'.$i.'" class="section"><div class="speed">'.$speed[$i].' Мбит/с</div><div class="price">'.$min[$i].' – '.$max[$i].' ₽/мес</div></div><div class="link"><a name="узнать подробнее на сайте www.sknt.ru" target="_blank" href="'.$link[$i].'">узнать подробнее на сайте www.sknt.ru</a></div></div>';
		}	
	}
	echo '</div></div>';
	$titles = array('месяц','месяца','месяцев');
	for($i=0;$i<count($res["tarifs"]);$i++) {
		echo '<div id="rate-body-'.$i.'"  class="rate-body-1 container earth"><div class="count"><div id="rate-body-'.$i.'-title-1" data-item="'.$i.'" class="title earth-title">Тариф "'.$title[$i].'"</div>';
		for($j=0;$j<count($res["tarifs"][$i]["tarifs"]);$j++) {
			$month[$i] = normform($pay_period[$i][$j],$titles);
			if($j > 0) {
				$disc[$i][$j] = '<br>скидка – '.$disc[$i][$j].' ₽.';
			} else {$disc[$i][$j]="";}
			echo '<div id="rate-body-'.$i.'-title" class="rate"><div class="title">'.$month[$i].'</div><div id="rate-body-'.$i.'-month-title-'.$j.'" data-item="'.$i.'" data-body="'.$j.'" class="section"><div class="price">'.round ($price_m[$i][$j],0).' ₽/мес</div><div class="disc">разовый платеж – '.$price[$i][$j].' ₽.'.$disc[$i][$j].'</div></div></div>';
		}
		echo'</div></div>';
	}
	
	for($i=0;$i<count($res["tarifs"]);$i++) {
		for($j=0;$j<count($res["tarifs"][$i]["tarifs"]);$j++) {
			$month[$i] = normform($pay_period[$i][$j],$titles);
			echo '<div id="rate-body-'.$i.'-month-body-'.$j.'" class="container earth-month"><div class="count"><div id="rate-body-'.$i.'-back-title-'.$j.'" data-item="'.$i.'" data-body="'.$j.'" class="title">Выбор тарифа</div><div class="month"><div class="title">Тариф "'.$title[$i].'"</div><div class="section"><div class="price">Период оплаты – '.$month[$i].'<br>'.$price_m[$i][$j].' ₽/мес</div><div class="disc">разовый платеж – '.$price[$i][$j].' ₽<br>со счета спишется – '.$price[$i][$j].' ₽<br></div><div class="cops">вступит в силу – сегодня<br>активно до – '.$new_date[$i][$j].'<br></div><div class="button">Выбрать</div></div></div></div></div>';
		}
		echo'</div></div>';
	}
}
?>