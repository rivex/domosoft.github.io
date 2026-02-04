<?

include 'session.inc';

if (!isset($_SESSION['login'])) {
	header("Location: index.html");
}
else {

include "mode.php";
include "header.inc";

$login = $_SESSION['login'];
$searchprod = $_GET['searchprod'];
$an = $_GET['an'];

//проверка кол-ва запросов
	$query = ibase_query("select netuserid, coalesce(netuseraccess,0), netuserdate, current_timestamp, coalesce(DATEDIFF(HOUR FROM netuserdate TO current_timestamp),0) from netusers where netuserid=$login");
	while ($row = ibase_fetch_row($query)) {$r1=$row[1]; $r2=$row[4];}
	if (($r2<$blockhours)&&($r1>$blockcount)) {
	?>
		<script language="javascript">
		<!--
			alert('Внимание! Превышен лимин запросов.');
			document.location='exit.php';
		//-->
		</script>
	<?
	} else {
//добавняем еще один запрос	
	$query = ibase_query("update netusers set netuseraccess=iif($blockhours>coalesce(DATEDIFF(HOUR FROM netuserdate TO current_timestamp),0),coalesce(netuseraccess,0)+1,1), netuserdate=current_timestamp where netuserid=$login");
?>
<form action="choice.php" method="get">

<table border="0">
<tr>
	<td colspan="2" class="standard"><input type="submit" value="Поиск" class="input"></td>
	<td class="standard"><input type="text" class="input" size="25" name="searchprod"></td>
	<td class="standard"> * введите часть НАИМЕНОВАНИЯ либо ОЕ либо ОПИСАНИЯ (точки, тире, пробелы игнорируются)</td>
</tr>
</table>

</form>

<table border="0" cellpadding="0" cellspacing="1" bgcolor="black" width="100%">
<tr>
	<td class="header" colspan="14" align="center">Товары</td>
</tr><tr>
	<td class="group" align="center">Наименование </td>
	<td class="group" align="center">ОЕ </td>
	<td class="group" align="center">Группа </td>
	<td class="group" align="center">Цена розница</td>
	<td class="group" align="center">Цена со скидкой</td>
	<td class="group" align="center">В приходе </td>
	<td class="group" align="center">Остаток </td>
	<td class="group" align="center">Резерв </td>
	<td class="group" align="center">Описание </td>
	<td class="group" align="center">Производитель </td>
	<td class="group" align="center">Скидка, % </td>
	<td class="group" align="center">Дополнит. </td>
	<td class="group" align="center" colspan="2">&nbsp;</td>
<?
$query = ibase_query($db, "SELECT valvalue FROM exchange where valid='$valute'") or die(ibase_errmsg()); 
while ($row = ibase_fetch_row($query)) $invexch=$row[0];

if ((!empty($an)) and ($an>0)) $query = ibase_query("select first 60 PRODUCTS.prodid, PRODUCTS.prodname, PRODUCTS.notes, PRODUCTS.oe, PRODUCTS.analoggroup, cast(PRODUCTS.prodprice*Exchange.valvalue/$invexch as numeric(15,2)), cast((PRODUCTS.prodprice-PRODUCTS.prodprice*discounts.discount*0.01)*Exchange.valvalue/$invexch as numeric(15,2)), products.groupid, GROUPS.GROUPNAME||' '||SUBGROUPS.SUBGROUPNAME, VENDORS.vendorname, EXCHANGE.valname, EXCHANGE.valvalue, discounts.discount, cast(coalesce(ostat.insum-ostat.inparty,0) as integer), cast(coalesce(ostat.outsum+ostat.outparty,0) as integer), cast(coalesce(ostat.inparty+ostat.outparty,0) as integer), products.analoggroup, dops.dopname from PRODUCTS left join VENDORS on PRODUCTS.VENDORID=VENDORS.vendorid left join DIRECTS on DIRECTS.directid=VENDORS.vendordirect left join DIVISIONS on DIVISIONS.divid=DIRECTS.directdiv and DIVISIONS.divid=$divid left join SUBGROUPS on PRODUCTS.GROUPID=SUBGROUPS.SUBGROUPID left join GROUPS on SUBGROUPS.GROUPID=GROUPS.GROUPID left join EXCHANGE on PRODUCTS.valid=EXCHANGE.valid left join discounts on products.discid=discounts.discid and discounts.ncorr=$login left join (select prodid, sum(insum) as insum, sum(outsum) as outsum, sum(inparty) as inparty, sum(outparty) as outparty from ostatki where ostatki.storeid in ($store, $storedop) group by prodid) as ostat on products.prodid=ostat.prodid left join dops on dops.dopid=products.proddops and dops.dopid<>2 where products.analoggroup=$an") or die(ibase_errmsg()); 
else 
$query = ibase_query("select first 60 PRODUCTS.prodid, PRODUCTS.prodname, PRODUCTS.notes, PRODUCTS.oe, PRODUCTS.analoggroup, cast(PRODUCTS.prodprice*Exchange.valvalue/$invexch as numeric(15,2)), cast((PRODUCTS.prodprice-PRODUCTS.prodprice*discounts.discount*0.01)*Exchange.valvalue/$invexch as numeric(15,2)), products.groupid, GROUPS.GROUPNAME||' '||SUBGROUPS.SUBGROUPNAME, VENDORS.vendorname, EXCHANGE.valname, EXCHANGE.valvalue, discounts.discount, cast(coalesce(ostat.insum-ostat.inparty,0) as integer), cast(coalesce(ostat.outsum+ostat.outparty,0) as integer), cast(coalesce(ostat.inparty+ostat.outparty,0) as integer), products.analoggroup, dops.dopname from PRODUCTS left join VENDORS on PRODUCTS.VENDORID=VENDORS.vendorid left join DIRECTS on DIRECTS.directid=VENDORS.vendordirect left join DIVISIONS on DIVISIONS.divid=DIRECTS.directdiv and DIVISIONS.divid=$divid left join SUBGROUPS on PRODUCTS.GROUPID=SUBGROUPS.SUBGROUPID left join GROUPS on SUBGROUPS.GROUPID=GROUPS.GROUPID left join EXCHANGE on PRODUCTS.valid=EXCHANGE.valid left join discounts on products.discid=discounts.discid and discounts.ncorr=$login left join (select prodid, sum(insum) as insum, sum(outsum) as outsum, sum(inparty) as inparty, sum(outparty) as outparty from ostatki where ostatki.storeid in ($store, $storedop) group by prodid) as ostat on products.prodid=ostat.prodid left join dops on dops.dopid=products.proddops and dops.dopid<>2 where replace(replace(replace(coalesce(products.prodname,'')||'/'||coalesce(products.oe,'')||'/'||coalesce(products.notes,''),'-',''),' ',''),'.','') containing replace(replace(replace('$searchprod','-',''),' ',''),'.','')") or die(ibase_errmsg()); 

	while ($row = ibase_fetch_row($query)) {
		echo "</tr><tr>";
		echo "<td class=\"standard\" width=\"150\"align=\"left\">$row[1]</td>";
		echo "<td class=\"standard\" width=\"150\"align=\"left\">$row[3]</td>";
		echo "<td class=\"standard\" width=\"200\"align=\"left\">$row[8]</td>";
		echo "<td class=\"standard\" width=\"50\"align=\"right\">$row[5]</td>";
		echo "<td class=\"standard\" width=\"50\"align=\"right\">$row[6]</td>";
		echo "<td class=\"standard\" width=\"50\"align=\"center\">$row[13]</td>";
		echo "<td class=\"standard\" width=\"50\"align=\"center\">$row[15]</td>";
		echo "<td class=\"standard\" width=\"50\"align=\"center\">$row[14]</td>";
		echo "<td class=\"standard\" width=\"200\"align=\"left\">$row[2]</td>";
		echo "<td class=\"standard\" width=\"100\"align=\"left\">$row[9]</td>";
		echo "<td class=\"standard\" width=\"50\"align=\"center\">$row[12]</td>";
		echo "<td class=\"standard\" width=\"100\"align=\"center\">$row[17]</td>";
		if ($row[16] > 0) echo "<td class=\"standard\" width=\"110\" align=\"center\"><a href=\"choice.php?an=$row[16]\" class=\"sub\">Аналоги</a></td>";
		else echo "<td class=\"standard\" width=\"110\"align=\"center\">Нет аналога</a></td>";
	}

}
}
?>
</table>

</form>
