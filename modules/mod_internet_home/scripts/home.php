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


if(!$libGenericStorage->attributeExistsInCurrentModule('fb_url')){
	$libGenericStorage->saveValueInCurrentModule('fb_url', '');
}

if(!$libGenericStorage->attributeExistsInCurrentModule('wp_url')){
	$libGenericStorage->saveValueInCurrentModule('wp_url', '');
}


function printVeranstaltungTitle($row){
	echo '<h4><a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '">' .$row['titel']. '</a></h4>';
}

function printVeranstaltungTime($row){
	global $libTime;

	echo '<p>';

	$date = substr($row['datum'], 0, 10);
	$datearray = explode('-', $date);
	$dateString = $datearray[2]. '.' .$datearray[1]. '.';
	$timeString = substr($row['datum'], 11, 5);

	echo $libTime->wochentag($row['datum']). ', ' .$dateString;

	if($timeString != '00:00'){
		echo '<br />';
		echo $timeString;
	}

	echo '</p>';
}


echo '<h1>Willkommen</h1>';

include("elements/announcements.php");
include("elements/pastevents.php");
include("elements/nextevents.php");