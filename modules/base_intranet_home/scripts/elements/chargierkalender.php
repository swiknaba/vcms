<?php
/*
This file is part of VCMS.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


/*
* actions
*/

if(isset($_REQUEST["chargierkalenderchangeanmeldenstate"]) && $_REQUEST["chargierkalenderchangeanmeldenstate"] != "" && isset($_REQUEST['chargierveranstaltungid']) && $_REQUEST['chargierveranstaltungid'] != ""){
	$stmt = $libDb->prepare("SELECT * FROM mod_chargierkalender_veranstaltung WHERE id=:id");
	$stmt->bindValue(':id', $_REQUEST['chargierveranstaltungid'], PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	// event in future?
	if(@date("Y-m-d H:i:s") <= $row["datum"]){
		if($_REQUEST["chargierkalenderchangeanmeldenstate"] == "anmelden"){
			$stmt = $libDb->prepare("INSERT IGNORE INTO mod_chargierkalender_teilnahme (chargierveranstaltung, mitglied) VALUES (:chargierveranstaltung, :mitglied)");
			$stmt->bindValue(':chargierveranstaltung', $_REQUEST['chargierveranstaltungid'], PDO::PARAM_INT);
			$stmt->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$stmt = $libDb->prepare("DELETE FROM mod_chargierkalender_teilnahme WHERE chargierveranstaltung=:chargierveranstaltung AND mitglied=:mitglied");
			$stmt->bindValue(':chargierveranstaltung', $_REQUEST['chargierveranstaltungid'], PDO::PARAM_INT);
			$stmt->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
			$stmt->execute();
		}
	}
}


/*
* output
*/

$stmtCount = $libDb->prepare('SELECT COUNT(*) AS number FROM mod_chargierkalender_veranstaltung WHERE datum >= NOW()');
$stmtCount->execute();
$stmtCount->bindColumn('number', $count);
$stmtCount->fetch();

// if there are entries
if($count > 0){
	echo '<h2>Chargierkalender</h2>';
	echo '<hr />';

	$stmt = $libDb->prepare('SELECT * FROM mod_chargierkalender_veranstaltung WHERE datum >= NOW() ORDER BY datum LIMIT 0,2');
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<form action="index.php?pid=intranet_home" method="post">';
		echo '<input type="hidden" name="chargierveranstaltungid" value="' .$row['id']. '" />';

		$stmt2 = $libDb->prepare("SELECT COUNT(*) AS number FROM mod_chargierkalender_teilnahme WHERE mitglied=:mitglied AND chargierveranstaltung=:chargierveranstaltung");
		$stmt2->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
		$stmt2->bindValue(':chargierveranstaltung', $row['id'], PDO::PARAM_INT);
		$stmt2->execute();
		$stmt2->bindColumn('number', $angemeldet);
		$stmt2->fetch();

		if($angemeldet){
			echo '<input type="hidden" name="chargierkalenderchangeanmeldenstate" value="abmelden" /><input type="submit" value="Abmelden" style="margin:0;padding:0;color:green;" />';
		} else {
			echo '<input type="hidden" name="chargierkalenderchangeanmeldenstate" value="anmelden" /><input type="submit" value="Anmelden" style="margin:0;padding:0;color:red;" />';
		}

		echo '</form>';

		echo '<b>' .$libTime->formatDateTimeString($row['datum'], 2). '</b> - ';
		echo '<a href="index.php?pid=intranet_chargierkalender_kalender&amp;semester=' .$libTime->getSemesterEinesDatums($row['datum']). '#t' .$row['id']. '">';
		echo $libVerein->getVereinNameString($row['verein']);

		if($row['beschreibung'] != ''){
			echo $row['beschreibung'];
		}

		echo '</a>';
		echo '<hr />';
	}
}
?>