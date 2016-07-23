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

if(!is_object($libGlobal))
	exit();


$stmtCount = $libDb->prepare('SELECT COUNT(*) AS number FROM base_veranstaltung WHERE datum > NOW()');
$stmtCount->execute();
$stmtCount->bindColumn('number', $numberOfNextEvents);
$stmtCount->fetch();

$fb_url = $libGenericStorage->loadValueInCurrentModule('fb_url');
$showFbPagePlugin = $libGenericStorage->loadValueInCurrentModule('showFbPagePlugin');
$fbPagePluginEnabled = $showFbPagePlugin && $fb_url != '';

$semesterCoverAvailable = false;

if($libModuleHandler->moduleIsAvailable('mod_internet_semesterprogramm')){
	$semesterCoverString = $libTime->getSemesterCoverString($libGlobal->semester);
	$semesterCoverAvailable = $semesterCoverString != '';
}

if($semesterCoverAvailable || $numberOfNextEvents > 0 || $fbPagePluginEnabled){
	echo '<div class="row">';

	if($numberOfNextEvents > 0){
		if($semesterCoverAvailable && $fbPagePluginEnabled){
			$maxNumberOfEvents = 1;
		} elseif($semesterCoverAvailable){
			$maxNumberOfEvents = 3;
		} elseif($fbPagePluginEnabled){
			$maxNumberOfEvents = 2;
		} else {
			$maxNumberOfEvents = 4;
		}

		$stmt = $libDb->prepare('SELECT id, titel, datum FROM base_veranstaltung WHERE datum > NOW() ORDER BY datum LIMIT 0,' .$maxNumberOfEvents);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<div class="col-sm-6 col-md-3">';
			echo '<div class="thumbnail">';
			echo '<div class="caption">';

			printVeranstaltungTitle($row);
			printVeranstaltungDateTime($row);

			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}

	if($semesterCoverAvailable){
		echo '<div class="col-sm-6 col-md-3">';
		echo '<div class="thumbnail">';
		echo '<div class="semestercoverBox center-block">';
		echo '<a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$libGlobal->semester. '">';
		echo $semesterCoverString;
		echo '</a>';
		echo '</div>';

		echo '<div class="caption">';
		echo '<h3><i class="fa fa-calendar" aria-hidden="true"></i> <a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$libGlobal->semester. '">Semesterprogramm</a></h3>';
		echo '<p>Weitere Veranstaltungen im <a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$libGlobal->semester. '">Semesterprogramm ' .$libTime->getSemesterString($libGlobal->semester). '</a></p>';
		echo '</div>';

		echo '</div>';
		echo '</div>';
	}

	if($fbPagePluginEnabled){
		echo '<div class="col-sm-12 col-md-6">';
		echo '<div class="thumbnail">';
		echo '<div class="caption">';
		echo '<div style="max-width:500px" class="center-block">';
		echo '<iframe src="https://www.facebook.com/plugins/page.php?href=' .urlencode($fb_url). '&tabs&width=340&height=154&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=true&appId" width="100%" height="154" class="facebookPagePlugin" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}
?>