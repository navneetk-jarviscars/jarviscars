<?php
include('defaults.inc.php');

//debug
echo "<div style='display:none;'>";
var_dump($_SESSION['search']);
echo "</div>";
//debug
/**
 * Start of Page Customisation
 */
//$_SESSION['search'] = reset_advanced_search($_SESSION['search']);

/**
 * Set required page attributes here
 * REQUIRED
 */
$schema['title'] = "Search New, Demo and Used Cars | Jarvis | Adelaide, South Australia";
$schema['meta']['description'] = "Search the entire range of new, demo and used cars at Jarvis";
$schema['meta']['keywords'] = "search,cars,new,demo,used,carsales,preowned,demonstrator";
/**
 * Set custom Schema values here - Refer schema-markup.inc.php
 * OPTIONAL
 */


$globalANDCriteria = NULL;
$criteriaORlist = NULL;
$sqlORDERBY = "`retailPrice` ASC, `onlineSpecial` DESC, `lastUpdate` DESC, `photoCount` DESC";


/* --------------------------------------
-- 
--    DO SEO FRIENDLY URL STUFF HERE
-- 
---------------------------------------*/
if(
(count($_SESSION['search']['makes']) <= 1) &&
(count($_SESSION['search']['models']) <= 1) &&
(count($_SESSION['search']['bodies']) <= 1) &&
(count($_SESSION['search']['types']) <= 1) &&
(count($_SESSION['search']['locations']) <= 1)


) {
	if(isset($_REQUEST['sseo'])) {
		$pageTitle = "";
		$pageDescription = "";
		$pageKeywords = "";
		
//		$_SESSION['search'] = reset_search_session($_SESSION['search']['sort']);
		$_SESSION['search']['types'] = array();
		$_SESSION['search']['locations'] = array();
		$_SESSION['search']['makes'] = array();
		$_SESSION['search']['models'] = array();
		$_SESSION['search']['bodies'] = array();
		$sLoName = NULL;
		$sMaName = NULL;
		$sMoName = NULL;
		$sBoName = NULL;
	
		if(isset($_REQUEST['sSp'])) { $sSp = $_REQUEST['sSp']; } else { $sSp = NULL; }
		
		if(isset($_REQUEST['sTyName'])) { $sTyName = str_replace("_"," ",$_REQUEST['sTyName']); } else { $sTyName = NULL; }
		if(isset($_REQUEST['sLoName'])) { $sLoName = str_replace("_"," ",$_REQUEST['sLoName']); } else { $sLoName = NULL; }
		if(isset($_REQUEST['sMaName'])) { $sMaName = str_replace("_"," ",$_REQUEST['sMaName']); } else { $sMaName = NULL; }
		if(isset($_REQUEST['sMoName'])) { $sMoName = str_replace("_"," ",$_REQUEST['sMoName']); } else { $sMoName = NULL; }
		if(isset($_REQUEST['sBoName'])) { $sBoName = str_replace("_"," ",$_REQUEST['sBoName']); } else { $sBoName = NULL; }
	
		if($sTyName == "all-cars") {
			$sTyName = NULL;
		}
		if($sLoName == "All Locations") {
			$sLoName = NULL;
		}
		if($sMaName == "All Makes") {
			$sMaName = NULL;
		}
		if($sMoName == "All Models") {
			$sMoName = NULL;
		}
		if($sBoName == "All Bodies") {
			$sBoName = NULL;
		}
	
	
		$pageTitle.= "Search ";
		$pageDescription.= "Search ";
		$pageKeywords.= "search,";
	
	
	
	
		// ADVANCED SEARCH CRITERIA	
		if(!empty($sLoName)) {
			$_SESSION['search']['locations'][0] = get_value($pdo,"jrv_locations","locationID",array("locationName"=>$sLoName));
			$_SESSION['search']['advanced'] = 1;
			$pageTitle.= "Jarvis ".$sLoName." ";
			$pageDescription.= "Jarvis ".$sLoName." ";
			$pageKeywords.= "Jarvis ".$sLoName.",";
		}
		if(!empty($sTyName)) {
			$_SESSION['search']['types'][0] = get_value($pdo,"autogate_types","typeID",array("typeName"=>substr($sTyName,0,-5)));
			$pageTitle.= $sTyName." ";
			$pageDescription.= $sTyName." ";
			$pageKeywords.= $sTyName.",";
		}
		
		
		
		if(!empty($sMaName)) {
//dump($_SESSION,0);
			$_SESSION['search']['makes'][0] = get_value($pdo,"r_makes","makeID",array("description"=>$sMaName));
//dump($_SESSION,0);
//dump($sMaName,1);
			$pageTitle.= $sMaName." ";
			$pageDescription.= $sMaName." ";
			$pageKeywords.= $sMaName.",";
		}
		if(!empty($sMoName)) {
			//$_SESSION['search']['models'][0] = get_value($pdo,"v_models","modelID",array("modelNameDisplay"=>$sMoName,"makeID"=>$_SESSION['search']['makes'][0]));
			if(!empty($sMaName)){
				$_SESSION['search']['models'][0] = get_value($pdo,"r_models","modelID",array("description"=>$sMoName,"makeID"=>$_SESSION['search']['makes'][0]));
			}else{
				$filtersMoID = 0;
				if(isset($_REQUEST['sMo'])) {
					if(!in_array(0,$_REQUEST['sMo'])) {
						$filtersMoID = $_REQUEST['sMo'][0];
						$_SESSION['search']['filtersMoID'] = $filtersMoID;
					}
				}
				
				if(!(isset($_SESSION['search']['filtersMoID']))){
					$_SESSION['search']['models'][0] = get_value($pdo,"r_models","modelID",array("description"=>$sMoName));
				}else{
					if($_SESSION['search']['filtersMoID']!=0){
						$_SESSION['search']['models'][0] = get_value($pdo,"r_models","modelID",array("description"=>$sMoName,"modelID"=>$_SESSION['search']['filtersMoID']));
					}else{
						$_SESSION['search']['models'][0] = get_value($pdo,"r_models","modelID",array("description"=>$sMoName));
					}
				}
				//debug
				echo "<div style='display:none;'>";
				var_dump($_SESSION['search']['filtersMoID']);
				var_dump($_SESSION['search']['models'][0]);
				echo "</div>";
				//debug
			}
			

			$pageTitle.= $sMoName." ";
			$pageDescription.= $sMoName." ";
			$pageKeywords.= $sMoName.",";
		}
		if(!empty($sBoName)) {
			$_SESSION['search']['bodies'][0] = get_value($pdo,"r_bodystyle","bodystyleID",array("name"=>$sBoName));
			$pageTitle.= $sBoName." ";
			$pageDescription.= $sBoName." ";
			$pageKeywords.= $sBoName.",";
		}
		if(!empty($sSp)) {
			$_SESSION['search']['specials'] = $sSp;
			$pageTitle.= "Specials ";
			$pageDescription.= "Specials ";
			$pageKeywords.= "Specials,";
		}
		
		$pageTitle.= "cars for sale in Adelaide | South Australia (SA)";
		$pageDescription.= "cars for sale in Adelaide | South Australia (SA)";
		$pageKeywords.= "cars,for,sale,in,Adelaide,South Australia,sa";
		
	}
	
	
	
}

//fix for urls from banner ads (eg.suzuki ads)
if(
	isset($_REQUEST['sTyName']) &&
	isset($_REQUEST['sLoName']) &&
	isset($_REQUEST['sMaName']) &&
	isset($_REQUEST['sMoName']) &&
	!isset($_REQUEST['sSortKey']) &&
	!isset($_REQUEST['sSortDir']) &&
	!isset($_REQUEST['sMa']) &&
	!isset($_REQUEST['sMo']) &&
	!isset($_REQUEST['sSe']) &&
	!isset($_REQUEST['sBa']) &&
	!isset($_REQUEST['sBo']) &&
	!isset($_REQUEST['sSt']) &&
	!isset($_REQUEST['sFu']) &&
	!isset($_REQUEST['sTr']) &&
	!isset($_REQUEST['sTg']) &&
	!isset($_REQUEST['sLo']) &&
	!isset($_REQUEST['sTy']) &&
	!isset($_REQUEST['sPr']) &&
	!isset($_REQUEST['pMi']) &&
	!isset($_REQUEST['pMa']) &&
	!isset($_REQUEST['yMi']) &&
	!isset($_REQUEST['yMa']) &&
	!isset($_REQUEST['sNu']) &&
	!isset($_REQUEST['sRe']) &&
	!isset($_REQUEST['sVi']) &&
	!isset($_REQUEST['sSp']) &&
	!isset($_REQUEST['sBodyID']) &&
	!isset($_REQUEST['page'])
){
	if(isset($_REQUEST['sseo'])) {
		$pageTitle = "";
		$pageDescription = "";
		$pageKeywords = "";
		
//		$_SESSION['search'] = reset_search_session($_SESSION['search']['sort']);
		/*$_SESSION['search']['types'] = array();
		$_SESSION['search']['locations'] = array();
		$_SESSION['search']['makes'] = array();
		$_SESSION['search']['models'] = array();
		$_SESSION['search']['bodies'] = array();*/
		$_SESSION['search'] = reset_search_session(NULL); 
		$sLoName = NULL;
		$sMaName = NULL;
		$sMoName = NULL;
		$sBoName = NULL;
	
		if(isset($_REQUEST['sSp'])) { $sSp = $_REQUEST['sSp']; } else { $sSp = NULL; }
		
		if(isset($_REQUEST['sTyName'])) { $sTyName = str_replace("_"," ",$_REQUEST['sTyName']); } else { $sTyName = NULL; }
		if(isset($_REQUEST['sLoName'])) { $sLoName = str_replace("_"," ",$_REQUEST['sLoName']); } else { $sLoName = NULL; }
		if(isset($_REQUEST['sMaName'])) { $sMaName = str_replace("_"," ",$_REQUEST['sMaName']); } else { $sMaName = NULL; }
		if(isset($_REQUEST['sMoName'])) { $sMoName = str_replace("_"," ",$_REQUEST['sMoName']); } else { $sMoName = NULL; }
		if(isset($_REQUEST['sBoName'])) { $sBoName = str_replace("_"," ",$_REQUEST['sBoName']); } else { $sBoName = NULL; }
	
		if($sTyName == "all-cars") {
			$sTyName = NULL;
		}
		if($sLoName == "All Locations") {
			$sLoName = NULL;
		}
		if($sMaName == "All Makes") {
			$sMaName = NULL;
		}
		if($sMoName == "All Models") {
			$sMoName = NULL;
		}
		if($sBoName == "All Bodies") {
			$sBoName = NULL;
		}
	
	
		$pageTitle.= "Search ";
		$pageDescription.= "Search ";
		$pageKeywords.= "search,";
	
	
	
	
		// ADVANCED SEARCH CRITERIA	
		if(!empty($sLoName)) {
			$_SESSION['search']['locations'][0] = get_value($pdo,"jrv_locations","locationID",array("locationName"=>$sLoName));
			$_SESSION['search']['advanced'] = 1;
			$pageTitle.= "Jarvis ".$sLoName." ";
			$pageDescription.= "Jarvis ".$sLoName." ";
			$pageKeywords.= "Jarvis ".$sLoName.",";
		}
		if(!empty($sTyName)) {
			$_SESSION['search']['types'][0] = get_value($pdo,"autogate_types","typeID",array("typeName"=>substr($sTyName,0,-5)));
			$pageTitle.= $sTyName." ";
			$pageDescription.= $sTyName." ";
			$pageKeywords.= $sTyName.",";
		}
		
		
		
		if(!empty($sMaName)) {
//dump($_SESSION,0);
			$_SESSION['search']['makes'][0] = get_value($pdo,"r_makes","makeID",array("description"=>$sMaName));
//dump($_SESSION,0);
//dump($sMaName,1);
			$pageTitle.= $sMaName." ";
			$pageDescription.= $sMaName." ";
			$pageKeywords.= $sMaName.",";
		}
		if(!empty($sMoName)) {
			//$_SESSION['search']['models'][0] = get_value($pdo,"v_models","modelID",array("modelNameDisplay"=>$sMoName,"makeID"=>$_SESSION['search']['makes'][0]));
			$_SESSION['search']['models'][0] = get_value($pdo,"r_models","modelID",array("description"=>$sMoName));

			$pageTitle.= $sMoName." ";
			$pageDescription.= $sMoName." ";
			$pageKeywords.= $sMoName.",";
		}
		if(!empty($sBoName)) {
			$_SESSION['search']['bodies'][0] = get_value($pdo,"r_bodystyle","bodystyleID",array("name"=>$sBoName));
			$pageTitle.= $sBoName." ";
			$pageDescription.= $sBoName." ";
			$pageKeywords.= $sBoName.",";
		}
		if(!empty($sSp)) {
			$_SESSION['search']['specials'] = $sSp;
			$pageTitle.= "Specials ";
			$pageDescription.= "Specials ";
			$pageKeywords.= "Specials,";
		}
		
		$pageTitle.= "cars for sale in Adelaide | South Australia (SA)";
		$pageDescription.= "cars for sale in Adelaide | South Australia (SA)";
		$pageKeywords.= "cars,for,sale,in,Adelaide,South Australia,sa";
		
	}
}
//fix for urls from banner ads (eg.suzuki ads)


if(isset($_REQUEST['sBodyID'])) { $sBodyID = $_REQUEST['sBodyID']; } else { $sBodyID = NULL; }

if(!is_null($sBodyID) && $sBodyID>= 0) {
	
	if(!isset($_REQUEST['sSortKey'])) {
		$_SESSION['search'] = reset_search_session(NULL); 
		$_SESSION['search']['bodies']= array();
		$_SESSION['search']['sBodyID']= array();	
	}
    
	if($sBodyID==0){
		$_SESSION['search']['bodies']= array();	
		$_SESSION['search']['sBodyID']= array();		
	}
	else {
		$_SESSION['search']['bodies']= array($sBodyID);			
		$_SESSION['search']['sBodyID'] = array($sBodyID);
	} 
	
 	
}


$search_type =  $_SESSION['search']['types'];
//  14-02-2017 automacally adding a demo type if searching for new or used type. 
//var_dump($site['makeID']);
if(!empty($search_type)) {
	//old code
	/*if(empty($site['makeID'])) {
	  if($site['siteID']=='1'){
		if (in_array(1, $search_type) || in_array(3, $search_type)) {
			if( !in_array(2, $search_type))
			{
				array_push($search_type,"2");
				$_SESSION['search']['types']=$search_type;	
			}
		}
	  }else{
	  	if (in_array(3, $search_type)) {
			if( !in_array(2, $search_type))
			{
				array_push($search_type,"2");
				$_SESSION['search']['types']=$search_type;	
			}
		}
	  }
	}else{
	  if (in_array(1, $search_type) || in_array(3, $search_type)) {
		  if( !in_array(2, $search_type))
		  {
			  array_push($search_type,"2");
			  $_SESSION['search']['types']=$search_type;	
		  }
	  }	
	}*/
	//new code 15 September 2017
	
	
	
	
	
	
	if($site['siteID']=='8' || $site['siteID']=='1'){
		
		
		if(in_array('71',$_SESSION['search']['makes'])) {
		if(!in_array('71',$_SESSION['search']['makes'])) {
		if (in_array(1, $search_type) || in_array(3, $search_type)) {
			if( !in_array(2, $search_type))
			{
		//array_push($search_type,"2");
				$_SESSION['search']['types']=$search_type;	
			}
		}
		}
		}
	elseif(!in_array('71',$_SESSION['search']['makes'])) {
	
		if(!in_array('71',$_SESSION['search']['makes'])) {
		if (in_array(1, $search_type) || in_array(3, $search_type)) {
			if( !in_array(2, $search_type))
			{
		array_push($search_type,"2");
				$_SESSION['search']['types']=$search_type;	
			}
		}
		}
	
	
	
	
	
	
	
	
	}
	
	
	
	}
	else{
		if (in_array(3, $search_type)) {
			if( !in_array(2, $search_type))
			{
				array_push($search_type,"2");
				$_SESSION['search']['types']=$search_type;	
			}
		}
	}
	
	
	
	
	
	
	
	
}


// GET EACH VARIABLE BEING SEARCHED FOR
// BUILD ARRAY FOR SEARCH CRITERIA INDEPENDANT OF $_SESSION
$search = array();
$search = $_SESSION['search'];

// DEPENDANT... MAKE | MODEL | SERIES | BADGE

// GLOBAL SEARCH CRITERIA
// Body
// Price
// Year
//



if(!empty($search['types'])) {
	$str = " AND (`typeID` = ";
	$str.= implode(" OR `typeID` = ",$search['types']);
	$str.= ")";
	
	if($site['siteID']=='1'){
		if(in_array('71',$_SESSION['search']['makes'])) {
			$str = " AND `typeID` NOT IN ('2','3') ";
		}
	
	else{
		if(in_array('',$_SESSION['search']['makes'])) {
			$str = " AND (`typeID`  IN ('2','3') ";
		}
		
		
	}
	}
	
	
	
	
	$globalANDCriteria.= $str;
}

if(!empty($search['bodies'])) {
	$str = " AND (`bodyID` = ";
	$str.= implode(" OR `bodyID` = ",$search['bodies']);
	$str.= ")";
	
	$globalANDCriteria.= $str;
}

if(!empty($search['seats'])) {
	$str = " AND (`seats` = ";
	$str.= implode(" OR `seats` = ",$search['seats']);
	$str.= ")";
	
	$globalANDCriteria.= $str;
}

if(!empty($search['fuels'])) {
	$str = " AND (`fuelID` = ";
	$str.= implode(" OR `fuelID` = ",$search['fuels']);
	$str.= ")";
	
	$globalANDCriteria.= $str;
}

if(!empty($search['transmissions'])) {
	$str = " AND (`transmissionID` = ";
	$str.= implode(" OR `transmissionID` = ",$search['transmissions']);
	$str.= ")";
	
	$globalANDCriteria.= $str;
}
if(!empty($search['transmissionGroups'])) {
	$str = " AND (`transmissionGroupID` = ";
	$str.= implode(" OR `transmissionGroupID` = ",$search['transmissionGroups']);
	$str.= ")";
	
	$globalANDCriteria.= $str;
}

if(!empty($search['locations'])) {
	/*if (in_array("14", $search['locations'])){
		array_push($search['locations'], "10");
	}else if (in_array("10", $search['locations'])){
		array_push($search['locations'], "14");
	}*/
	$str = " AND (`locationID` = ";
	$str.= implode(" OR `locationID` = ",$search['locations']);
	$str.= ")";
	//echo $str;
	$globalANDCriteria.= $str;
}

if(!empty($search['programs'])) {
	$str = " AND (`programID` IN (".implode(",",$search['programs']).")";
	$str.= ")";
	
	$globalANDCriteria.= $str;
}


if($budgetSearchFlag==1) 
{
	$globalANDCriteria.= " AND `retailPrice` >= 0";	
	$globalANDCriteria.= " AND `retailPrice` <= ".$budgetAmount;			
}else 
{
	if(!empty($search['priceMax'])) {
		// BUILD PORTION OF WHERE QUERY
		$globalANDCriteria.= " AND `retailPrice` <= ".$search['priceMax'][0];
	}	

	if(!empty($search['priceMin'])) {
		// BUILD PORTION OF WHERE QUERY
		$globalANDCriteria.= " AND `retailPrice` >= ".$search['priceMin'][0];
	}

}

if(!empty($search['yearMin'])) {
	// BUILD PORTION OF WHERE QUERY
	$globalANDCriteria.= " AND `year` >= ".$search['yearMin'][0];
}
if(!empty($search['yearMax'])) {
	// BUILD PORTION OF WHERE QUERY
	$globalANDCriteria.= " AND `year` <= ".$search['yearMax'][0];
}


if(!empty($search['specials'])) {
	// MODIFY SORT CRITERIA
	$globalANDCriteria.= " AND `onlineSpecial` != ''";
//	$sqlORDERBY = "`onlineSpecial` DESC, `retailPrice` ASC, `lastUpdate` DESC, `photoCount` DESC";
//	$_SESSION['search']['sort']['key'] = 15;
//	$_SESSION['search']['sort']['dir'] = "desc";
}

//if(!empty($search['assured'])) {
	// MODIFY SORT CRITERIA
	//$globalANDCriteria.= " AND assuredVehicle='". $search['assured']. "' ";
//}

$badgesSearchSQL = ""; //used in make filter
foreach($search['badges'] as $key => $value) {
	// GET MAKE, MODEL, SERIES FOR EACH BADGE
	$s = array();
	$s['badgeID'] = $value;
	$s['seriesID'] = get_value($pdo,"r_badge","seriesID",array("badgeID"=>$s['badgeID']));
	$s['modelID'] = get_value($pdo,"r_series","modelID",array("seriesID"=>$s['seriesID']));
	$s['makeID'] = get_value($pdo,"r_models","makeID",array("modelID"=>$s['modelID']));

	// BUILD PORTION OF WHERE QUERY
	$criteriaORlist.= " OR (`makeID` = ".$s['makeID']." AND `modelID` = ".$s['modelID']." AND `seriesID` = ".$s['seriesID']." AND `badgeID` = ".$s['badgeID'].$globalANDCriteria.")";
	$badgesSearchSQL .= " AND `badgeID` = ".$s['badgeID']." ";
	
	//search for trd hilux
	if($s['modelID']=='2129'){
		$criteriaORlist.= " OR (`makeID` = 197 AND `modelID` = 2159 AND `seriesID` = ".$s['seriesID']." AND `badgeID` = ".$s['badgeID'].$globalANDCriteria.")";
	}
	// REMOVE SPECIFIC MAKE, MODEL, SERIES FROM $search array
	$badgeKey = $key;
	$seriesKey = array_search($s['seriesID'],$search['series']);
	$modelKey = array_search($s['modelID'],$search['models']);
	$makeKey = array_search($s['makeID'],$search['makes']);
	unset($search['badges'][$badgeKey]);
	unset($search['series'][$seriesKey]);
	unset($search['models'][$modelKey]);
	unset($search['makes'][$makeKey]);
}
$seriesSearchSQL = ""; //used in make filter
foreach($search['series'] as $key => $value) {
	// GET MAKE, MODEL FOR EACH SERIES
	$s = array();
	$s['seriesID'] = $value;
	$s['modelID'] = get_value($pdo,"r_series","modelID",array("seriesID"=>$s['seriesID']));
	$s['makeID'] = get_value($pdo,"r_models","makeID",array("modelID"=>$s['modelID']));
	// BUILD PORTION OF WHERE QUERY
	$criteriaORlist.= " OR (`makeID` = ".$s['makeID']." AND `modelID` = ".$s['modelID']." AND `seriesID` = ".$s['seriesID'].$globalANDCriteria.")";
	$seriesSearchSQL .= " AND `seriesID` = ".$s['seriesID']." ";
	
	//search for trd hilux
	if($s['modelID']=='2129'){
		$criteriaORlist.= " OR (`makeID` = 197 AND `modelID` = 2159 AND `seriesID` = ".$s['seriesID'].$globalANDCriteria.")";
	}
	// REMOVE SPECIFIC MAKE, MODEL, SERIES FROM $search array
	$seriesKey = $key;
	$modelKey = array_search($s['modelID'],$search['models']);
	$makeKey = array_search($s['makeID'],$search['makes']);
	unset($search['series'][$seriesKey]);
	unset($search['models'][$modelKey]);
	unset($search['makes'][$makeKey]);
}
	
$modelSearchSQL = ""; //used in make filter
foreach($search['models'] as $key => $value) {
	// GET MAKE FOR EACH MODEL
	$s = array();
	$s['modelID'] = $value;
	$s['makeID'] = get_value($pdo,"r_models","makeID",array("modelID"=>$s['modelID']));
	// BUILD PORTION OF WHERE QUERY
	$criteriaORlist.= " OR (`makeID` = ".$s['makeID']." AND `modelID` = ".$s['modelID'].$globalANDCriteria.")";
	$modelSearchSQL .= " AND `modelID` = ".$s['modelID']." ";
	
	//search for trd hilux
	if($s['modelID']=='2129'){
		$criteriaORlist.= " OR (`makeID` = 197 AND `modelID` = 2159 ".$globalANDCriteria.")";
		$modelSearchSQL .= " AND `modelID` = 2159 ";
	}
	// REMOVE SPECIFIC MAKE, MODEL FROM $search array
	$modelKey = $key;
	$makeKey = array_search($s['makeID'],$search['makes']);
	unset($search['models'][$modelKey]);
	unset($search['makes'][$makeKey]);
	
	
	
	
	
	
}

//fix for demo and used cars search on manufacturer site
/*if($site['siteID']=='2'){
	if(empty($search['makes'])){
		// BUILD PORTION OF WHERE QUERY
		$criteriaORlist.= " OR (`makeID` != 1 AND `typeID` IN ('1')) ".$modelSearchSQL.$globalANDCriteria;
	}
}*/
//if($site['siteID']=='2'){
if(($site['siteID'] > '1' && $site['siteID'] < '8') || ($site['siteID'] > '8')){
	if((empty($search['types'])) && (empty($search['makes']))) {
		$criteriaORlist.= " OR (`makeID` != ".$site['makeID']." AND `typeID` IN ('1')) ".$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria;
		$criteriaORlist.= " OR (`makeID` = ".$site['makeID']." AND `typeID` IN ('3','2','1')) ".$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria;
	}
}
if($site['siteID']=='8'){
	if((empty($search['types'])) && (empty($search['makes']))) {
		$criteriaORlist.= " OR (`makeID` NOT IN ('190','192','104','182') AND `typeID` IN ('1')) ".$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria;
		$criteriaORlist.= " OR (`makeID` IN ('190','192','104','182') AND `typeID` IN ('3','2','1')) ".$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria;
	}
}
/*if($site['siteID']=='1'){
		if(in_array('71',$_SESSION['search']['makes'])) {
			$criteriaORlist.= " AND `typeID` NOT IN ('2','3') ";
		}
	}*/
	





//fix for demo and used cars search on manufacturer site

foreach($search['makes'] as $key => $value) {
	//fix for demo and used cars search on manufacturer site
	$typeUsed = "";
	//if($site['siteID']=='2'){
	if(($site['siteID'] > '1' && $site['siteID'] < '8') || ($site['siteID'] > '8')){
		if($value != $site['makeID']){
			$typeUsed = " AND `typeID` IN ('1') ";
		}
	}
	/*if($site['siteID']=='8'){
		if($value != '190' && $value != '192' && $value != '104' && $value != '182'){
			$typeUsed = " AND `typeID` IN ('1') ";
		}
	}*/
		/*if($site['siteID']=='1'){
		if(in_array('71',$_SESSION['search']['makes'])) {
			$typeUsed = " AND `typeID` NOT IN ('2','3') ";
		}
		
		
		}*/
	
	
	
	
	
	
	//fix for demo and used cars search on manufacturer site
	
	// BUILD PORTION OF WHERE QUERY
	$criteriaORlist.= " OR (`makeID` = ".$value.$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria.$typeUsed.")";
	//for fpv
	/*if($value=='46'){
		$criteriaORlist.= " OR (`makeID` = 5 ".$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria.$typeUsed.")";
	}
	if($value=='5'){
		$criteriaORlist.= " OR (`makeID` = 46 ".$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria.$typeUsed.")";
	}*/
	//for hsv
	/*if($value=='47'){
		$criteriaORlist.= " OR (`makeID` = 8 ".$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria.$typeUsed.")";
	}
	if($value=='8'){
		$criteriaORlist.= " OR (`makeID` = 47 ".$modelSearchSQL.$seriesSearchSQL.$badgesSearchSQL.$globalANDCriteria.$typeUsed.")";
	}*/
	// REMOVE SPECIFIC MAKE, MODEL FROM $search array
	$makeKey = $key;
	unset($search['makes'][$makeKey]);
}

if($criteriaORlist != NULL) {
	$criteriaORlist = " AND (".substr($criteriaORlist,4).")";
} else {
	$criteriaORlist = $globalANDCriteria;
}
// SPECIFIC SEARCH
// STOCK NUMBER OR REGISTARTION
if(!empty($search['stockNumber'])) {
	$commaPos = strpos($search['stockNumber'],",");
	if($commaPos === FALSE) {
		$criteriaORlist .= " AND (`stockNumber` LIKE '".$search['stockNumber']."%'". " OR "."`registrationNumber` LIKE '".$search['stockNumber']."%' )"      ;
	} else {
		$a = explode(",",$search['stockNumber']);
		$criteriaORlist .= " AND (";
		foreach($a as $k => $v) {
			$criteriaORlist .= " `stockNumber` LIKE '".$v."%' OR ". " `registrationNumber` LIKE '".$v."%' OR";
		}
		$criteriaORlist = substr($criteriaORlist,0,-3);
		$criteriaORlist .= ") ";
	}
} elseif(!empty($search['registration'])) {
	$commaPos = strpos($search['registration'],",");
	if($commaPos === FALSE) {
		$criteriaORlist .= " AND `registrationNumber` LIKE '".$search['registration']."%'";
	} else {
		$a = explode(",",$search['registration']);
		$criteriaORlist .= " AND (";
		foreach($a as $k => $v) {
			$criteriaORlist .= " `registrationNumber` LIKE '".$v."%' OR";
		}
		$criteriaORlist = substr($criteriaORlist,0,-3);
		$criteriaORlist .= ") ";
	}
} elseif(!empty($search['vin'])) {
	$commaPos = strpos($search['vin'],",");
	if($commaPos === FALSE) {
		$criteriaORlist .= " AND `vin` LIKE '".$search['vin']."%'";
	} else {
		$a = explode(",",$search['vin']);
		$criteriaORlist .= " AND (";
		foreach($a as $k => $v) {
			$criteriaORlist .= " `vin` LIKE '".$v."%' OR";
		}
		$criteriaORlist = substr($criteriaORlist,0,-3);
		$criteriaORlist .= ") ";
	}
}

// SORT PROCESSING
if($_SESSION['search']['sort']['key'] != "default") {
	$sql = "SELECT `sortFieldName` FROM `autogate_sort_fields` WHERE `sortID` = :sortID AND `sortFieldStatus` = 1 ORDER BY `sortFieldOrder` ASC";
	$params = array(":sortID" => $_SESSION['search']['sort']['key']);
	$sth = $pdo->prepare($sql);
	$sth->execute($params);
	$sqlOB1 = "";
	$sqlOB2 = "";
	$sqlORDERBY = "";
	while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
//		$sqlORDERBY.= " `".$row['sortFieldName']."` ".$_SESSION['search']['sort']['dir'].",";
		$sqlOB1.= " `".$row['sortFieldName']."` = 0,";
		$sqlOB2.= " `".$row['sortFieldName']."` ".$_SESSION['search']['sort']['dir'].",";
	}
	$sqlORDERBY = substr($sqlOB1.$sqlOB2,0,-1);
	
}

$i = 0;
$rpp = 12;
$offset = ($rpp*($pageNumber-1));

$vehicles = array();
//$sql = "SELECT COUNT(`inventoryID`) AS `count` FROM `v_inventory`  WHERE `inventoryStatus` = 1 $criteriaORlist ORDER BY $sqlORDERBY";
if($site['siteID'] == '1'){
		if((!in_array('1',$_SESSION['search']['types']))&&(!in_array('2',$_SESSION['search']['types']))&&(!in_array('2',$_SESSION['search']['types']))){
			
			$sql = "SELECT COUNT(`inventoryID`) AS `count` 
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
		WHERE `autogate_inventory`.`inventoryID` NOT IN (SELECT `inventoryID` FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `makeID` = 71 and `typeID` IN ('2', '3')) 
AND 
		 inventoryStatus = 1 
		$criteriaORlist
		ORDER BY $sqlORDERBY";

			
			
			
			
		}}


 if($site['siteID'] == '1'){
	if(in_array('1',$_SESSION['search']['types'])){
	
	
$sql = "SELECT COUNT(`inventoryID`) AS `count` 
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
		
		WHERE `autogate_inventory`.`inventoryID` NOT IN (SELECT `inventoryID` FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `makeID` = 71 and `typeID` IN ('2', '3')) 
AND 
		 inventoryStatus = 1 
		$criteriaORlist
		ORDER BY $sqlORDERBY";
		
		}

	
	else if($site['siteID'] == '1'){
	if((in_array('2',$_SESSION['search']['types']))||(in_array('3',$_SESSION['search']['types'])))
	
	
	{
	$sql = "SELECT COUNT(`inventoryID`) AS `count` 
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
		WHERE inventoryStatus = 1 and makeID != '71'
		$criteriaORlist
		ORDER BY $sqlORDERBY";
	}
	
}}


else {
	
$sql =	"SELECT COUNT(`inventoryID`) AS `count` 
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
		WHERE inventoryStatus = 1 
		$criteriaORlist
		ORDER BY $sqlORDERBY";
}


	
	
	
	
//echo $sql;
$error="";

$debug[] = $sql;

try {
	$count = $pdo->query($sql)->fetchColumn();
} catch (Exception $e) {
    $error=$e->getMessage();
}



if($site['siteID'] == '1'){
		if((!in_array('1',$_SESSION['search']['types']))&&(!in_array('2',$_SESSION['search']['types']))&&(!in_array('3',$_SESSION['search']['types']))){
			
$sql = "SELECT *  
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
	WHERE `autogate_inventory`.`inventoryID` NOT IN (SELECT `inventoryID` FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `makeID` = 71 and `typeID` IN ('2', '3')) 
AND 
inventoryStatus = 1
	
		$criteriaORlist  
		ORDER BY $sqlORDERBY
		LIMIT ".$offset.",".$rpp;

		}
}

if($site['siteID'] == '1'){
		if((!in_array('1',$_SESSION['search']['types']))&&(!in_array('2',$_SESSION['search']['types']))&&(in_array('3',$_SESSION['search']['types']))){
			
			$sql = "SELECT *  
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
	WHERE inventoryStatus = 1 and `makeID` != 71
	
		$criteriaORlist  
		ORDER BY $sqlORDERBY
		LIMIT ".$offset.",".$rpp;

}
	}

if($site['siteID'] == '1'){
		if((!in_array('1',$_SESSION['search']['types']))&&(!in_array('2',$_SESSION['search']['types']))&&(!in_array('2',$_SESSION['search']['types']))){
			
			$sql = "SELECT *  
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
	WHERE `autogate_inventory`.`inventoryID` NOT IN (SELECT `inventoryID` FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `makeID` = 71 and `typeID` IN ('2', '3')) 
AND 
inventoryStatus = 1
	
		$criteriaORlist  
		ORDER BY $sqlORDERBY
		LIMIT ".$offset.",".$rpp;
}
	}



if($site['siteID'] == '1'){
	//ADDED ON 28/05/2019
	
if((in_array('1',$_SESSION['search']['types']))&&(in_array('2',$_SESSION['search']['types']))||(in_array('1',$_SESSION['search']['types']))&&(in_array('3',$_SESSION['search']['types']))||(in_array('1',$_SESSION['search']['types']))){
	$sql = "SELECT *  
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
	WHERE `autogate_inventory`.`inventoryID` NOT IN (SELECT `inventoryID` FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `makeID` = 71 and `typeID` IN ('2', '3')) 
AND 
inventoryStatus = 1
	
		$criteriaORlist  
		ORDER BY $sqlORDERBY
		LIMIT ".$offset.",".$rpp;
		}

else if($site['siteID'] == '1'){
	
	if((in_array('2',$_SESSION['search']['types']))||(in_array('3',$_SESSION['search']['types']))){
	
	$sql = "SELECT *  
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
	WHERE inventoryStatus = 1 and `makeID` != 71
		$criteriaORlist  
		ORDER BY $sqlORDERBY
		LIMIT ".$offset.",".$rpp;

	}


}
	
else if($site['siteID'] == '1'){
	$sql = "SELECT *  
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
	    WHERE `autogate_inventory`.`inventoryID` NOT IN (SELECT `inventoryID` FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `makeID` = 71 and `typeID` IN ('2', '3')) 
        AND 
        inventoryStatus = 1
        $criteriaORlist  
		ORDER BY $sqlORDERBY
		LIMIT ".$offset.",".$rpp;

}	
 }else {
	$sql = "SELECT *  
		FROM `autogate_inventory` 
		JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
		WHERE inventoryStatus = 1
		$criteriaORlist 
		ORDER BY $sqlORDERBY
		LIMIT ".$offset.",".$rpp;
	}
//code added on 7/11/2019 after dos attack
$sth=$pdo->prepare($sql);

$sth->bindParam(1, $row['makeID'], PDO::PARAM_INT);
$sth->bindParam(2, $row['modelID'], PDO::PARAM_INT);
$sth->bindParam(3, $row['seriesID'], PDO::PARAM_INT);
$sth->bindParam(4, $row['badgeID'], PDO::PARAM_INT);
$sth->bindParam(5, $row['bodyID'], PDO::PARAM_INT);
$sth->bindParam(6, $row['typeID'], PDO::PARAM_INT);
$sth->bindParam(7, $row['transmissionID'], PDO::PARAM_INT);
$sth->bindParam(8, $row['colourID'], PDO::PARAM_INT);
$sth->bindParam(9, $row['inventoryID'], PDO::PARAM_INT);
$sth->execute($params);


?>
<script>
	var sth = <?php echo($row['makeID']); ?>;
	console.log('test'.sth);
	
	</script>

<?php

$sql2 = "SELECT `imageName` FROM `autogate_inventory_photos` WHERE `inventoryID` = :inventoryID ORDER BY `imageOrder` ASC";
$sth2 = $pdo->prepare($sql2);

$debug[] = $sql;


$sth = $pdo->query($sql);



$models = array();
while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	$vehicles[$i] = $row;

	$vehicles[$i]['makeName'] = get_value($pdo,"r_makes","description",array("makeID"=>$row['makeID']));
/* CHANGED SELECT TO `modelName` INSTEAD OF `modelNameDisplay` */
	$vehicles[$i]['modelName'] = get_value($pdo,"r_models","description",array("modelID"=>$row['modelID']));
	$vehicles[$i]['modelCode'] = get_value($pdo,"r_models", ucwords(strtolower("modelCode")),array("modelID"=>$row['modelID']));
	$vehicles[$i]['seriesName'] = get_value($pdo,"r_series","name",array("seriesID"=>$row['seriesID']));
	
	$primaryBadgeName = get_value($pdo,"r_badge","name",array("badgeID"=>$row['badgeID']));
	$secondaryBadgeName = get_value($pdo,"r_badge","badge2nd",array("badgeID"=>$row['badgeID']));
	$vehicles[$i]['badgeName'] = $primaryBadgeName.' '.$secondaryBadgeName;
	$vehicles[$i]['bodyName'] = get_value($pdo,"r_bodystyle","name",array("bodystyleID"=>$row['bodyID']));
	$vehicles[$i]['typeName'] = get_value($pdo,"autogate_types","typeName",array("typeID"=>$row['typeID']));
	
	$vehicles[$i]['programName'] = "";
	$vehicles[$i]['transmissionName'] = get_value($pdo,"r_transmission","nameDisplay",array("transmissionID"=>$row['transmissionID']));
	
	$vehicles[$i]['transmissionGroupName'] = NULL;
	$vehicles[$i]['colourName'] = get_value($pdo,"autogate_colours","colourName",array("colourID"=>$row['colourID']));
	
	
	if($row['photoCount'] > 0) {
		$params2 = array(":inventoryID"=>$row['inventoryID']);
$debug[] = $sql2;
$debug[] = $params2;

		$sth2->execute($params2);
		while($row2 = $sth2->fetch(PDO::FETCH_ASSOC)) {
			$vehicles[$i]['photos'][] = $row2['imageName'];
		}
	} else {
		$vehicles[$i]['photos'][] = "_no-image.gif";
	}
	
	
	$i++;
}
$sth2 = NULL;
$sth = NULL;

$pagination = get_pagination_array($pageNumber,$count,$rpp,5);


$carType = "";
if(count($_SESSION['search']['types']) == 1) {
	$carType = get_value($pdo,"autogate_types","typeName",array("typeID"=>$_SESSION['search']['types'][0]));
}
/**
 * End of Page Customisation
 */
include('start.inc.php');
?>

<link rel="stylesheet"  href="<?php echo(CDN_URL); ?>css/noUiSlider/nouislider.min.css" >

<?php
include('head.inc.php');
?>

<style type="text/css">
	@media (min-width: 1281px) {
  
		#refine-search-mobile{
			display:none;
		}
  
}
	
	#refine-search-mobile{
	text-transform: none;
		font-size: 1.6rem;
		background-color:#004b8d;
		    text-decoration: none
	
	}
	:target::before {
  content: "";
  display: block;
  width: 100%;
  height: 86px;
		margin: -86px 0 0;
}
	#refineSearchFields:target {
       border: 5px solid rgb(0, 0, 139);
    box-shadow: rgb(160, 160, 160) 0px 0px 2px;
   width: fit-content;
}
	#re-input{
			
			text-transform: none;
		font-size: 1.6rem;
	
		}
#refine-Search
	{
		
		text-transform: none;
		font-size: 1.6rem;
		background-color:#004b8d;
		    text-decoration: none;
}
		
	
	 #remove-affordability-search{
	  
	      text-transform: none;
          font-size: 1.6rem;
		
	}
	
	.refine-search{
text-decoration: none;	
}
	
	
	.remove-affordability-search{
		 text-transform: none;
		
	}
	
	
		/*iphone x*/
	@media only screen 
    and (device-width : 375px) 
    and (device-height : 812px) 
    and (-webkit-device-pixel-ratio : 3) {
		
		
		#re-input{
			
			width: 100%;
		}	
		
		
		
	
	#refine-Search
	{
		
		text-transform: none;
		font-size: 1.6rem;
		width:195px;
		background-color:#004b8d;
		margin-left:0px;
		margin-top:7px;
		width: 100%;
		}
	
	 #remove-affordability-search{
	  
	      text-transform: none;
          font-size: 1.6rem;
		  width: 245px;
          margin-left: 0px; 
		 width: 100%;
	
	}
		
		#refineSearchFields:target {
       border: 5px solid rgb(0, 0, 139);
    box-shadow: rgb(160, 160, 160) 0px 0px 2px;
   width: 100%;
}
	
	}
	/*iphone 8**/	
@media only screen 
    and (device-width : 375px) 
    and (device-height : 667px) 
    and (-webkit-device-pixel-ratio : 2) {
	
	#re-input{
			
			width: 100%;}	
		
#refine-Search
	{
		
		text-transform: none;
		font-size: 1.6rem;
		width:195px;
		background-color:#004b8d;
		margin-left:0px;
			margin-top:7px;
		width: 100%;
		}
	
	 #remove-affordability-search{
	  
	      text-transform: none;
          font-size: 1.6rem;
		  width: 245px;
          margin-left: 0px; 
		 width: 100%;
	
	}
		#refineSearchFields:target {
       border: 5px solid rgb(0, 0, 139);
    box-shadow: rgb(160, 160, 160) 0px 0px 2px;
   width: 100%;
}

	
	}
	
	
	@media only screen 
    and (device-width : 414px) 
    and (device-height : 896px) 
    and (-webkit-device-pixel-ratio : 3) { 
	
	
	#re-input{
			
			width: 100%;}	
		
#refine-Search
	{
		
		text-transform: none;
		font-size: 1.6rem;
		width:195px;
		background-color:#004b8d;
		margin-left:0px;
			margin-top:7px;
		width: 100%;
		}
	
	 #remove-affordability-search{
	  
	      text-transform: none;
          font-size: 1.6rem;
		  width: 245px;
          margin-left: 0px; 
		 width: 100%;
	
	}
		
		#refineSearchFields:target {
       border: 5px solid rgb(0, 0, 139);
    box-shadow: rgb(160, 160, 160) 0px 0px 2px;
   width: 100%;
}

	
	}
	
}
	
	
	@media only screen 
    and (device-width : 375px) 
    and (device-height : 812px) 
    and (-webkit-device-pixel-ratio : 3) {
	
	
	#re-input{
			
			width: 100%;}	
		
#refine-Search
	{
		
		text-transform: none;
		font-size: 1.6rem;
		width:195px;
		background-color:#004b8d;
		margin-left:0px;
			margin-top:7px;
		width: 100%;
		}
	
	 #remove-affordability-search{
	  
	      text-transform: none;
          font-size: 1.6rem;
		  width: 245px;
          margin-left: 0px; 
		 width: 100%;
	
	}
	
	}
	
	
	
	
	
	}
	
	
	
	
	/*begin ipad portrait fixes*/
@media only screen 
  and (min-device-width: 768px) 
  and (max-device-width: 1024px) 
  and (orientation: portrait) 
  and (-webkit-min-device-pixel-ratio: 1) {
  .main>.box {
	  padding-left: 10px;
	  padding-right: 10px;
  }
  .box-col-container {
	  margin: 0 auto;
  }
  .col1 .box-result-item .image {
	  height: auto;
	  display: block;
	  width: 100%;
	  padding: 10px !important;
  }
  .col1 .box-result-item .blurb {
	  display: block;
	  width: 100% !important;
	  padding: 0 10px 0 10px;
  }
  .col1 .box-result-item .details {
	  display: block;
	  width: 100%;
	  padding: 0 10px 0 10px;
  }
  .col1 .box-result-item .btn-details {
	  margin-bottom: 10px;
	  margin-top: 10px;
	  width: 94%;
	  margin-left: 10px;
	  position: relative;
	  min-width: 0px;
	  right: 0px;
	  bottom: 0px;
  }
  .refineSearch-hr {
	  width: 93% !important;
  }
  .col1 .box-result-item .features {
	  width: 50% !important;
  }
  .col1 .box-result-item .blurb p {
	  min-height: 0px;
	  margin-top: 10px;
  }
  .col1 .box-result-item .blurb p {
      height: 3.2em !important;
  }
}
/*end ipad portrait fixes*/

/* for this page */
@media (max-width: 768px)
	{
	.column.main, .column.aside, .column.full-width {
		width: 100%;
	}
}

.noUi-tooltip {
    font: 700 12px/12px Arial;
}

.noUi-horizontal .noUi-tooltip {
	bottom: -120%;
}

@media (max-width: 768px){
	#results-view-details-btn{
		right:0px !important;
		width:94% !important;
		margin-bottom:0px !important;
		position:relative;
		margin-left:10px;
	}
	#results-enquire-btn{
		right:0px !important;
		width:94% !important;
		position:relative;
		margin-left:10px;
		margin-top:10px;
	}
	.blurb{
		width:100% !important;
	}
	.details{
		padding-top:10px !important;
	}
	.blurb p{
		height: 4.2em !important;
	}
	#features-2col{
		margin-left:0px !important;
	}
}

@media (min-width: 769px){
#results-view-details-btn{
	right:206px !important; 
	width:185px !important;
	cursor:pointer !important;
	bottom:10px !important; /*17px !important;*/
}
#results-enquire-btn{
	width:185px !important;
	cursor:pointer !important;
	bottom:10px !important; /*17px !important;*/
}
}
@media (min-width: 480px){
.blurb{
	width:31% !important;
}
}
.blurb p{
	line-height:1.4em !important;
	height: 4.2em; /*7.0em !important;*/
    overflow: hidden !important;
	text-overflow: ellipsis !important;
}
/*VDP new style*/
.box-shadow.gradient-bg{
	background:#FFFFFF !important;
}
.checkbox-container span.checkbox::before{
	box-shadow:none;
}
.chosen-container-multi .chosen-choices{
	box-shadow:none;
}
.basicSearchFields .btn-fullwidth{
	width:90%;
}
#search-cars-form{
	margin-top:0px !important;
}
@media (max-width: 768px) {
	.aside.column.skinny{
		padding-left: 20px !important;
		padding-right: 20px !important;
	}
	.car-detail-page .aside {
		margin-top: -20px !important;
	}
}
/*new results page style*/
.car-list-header {
	color: #565656 !important;
	background-color: #FFF !important;
	font-weight:bold !important;
}
.col1 .box-shadow{
	border: 2px solid #e1e1e1 !important;
}
.featured-grad{
	border-bottom: none !important;
    margin-left: 0px !important;
    margin-right: 0px !important;
}
.col1 .featured-grad .car-title{
	padding-left: 10px !important;
}
.col1 .featured-grad .car-price{
	padding-right: 10px !important;
}
.col1 .box-result-item.special{
	border: none !important;
}
#features-2col{
	margin-left:30px;
}
.box.box-shadow.box-result-item{
	margin-bottom:20px !important;
}
.col1 .car-price{
	font-size: 15px !important;
}
.col1 .box-result-item.weekly-special-nogreyborder{
	border: none !important;
}
.box-content.closed{
	padding-top: 15px !important;
	padding-bottom:15px !important;
}
.icon-up-open:before {
    content: '\2212';
	position: absolute;
    font-family: sans-serif;
    width: 1.5em;
    text-align: center;
    font-size: 1.6rem;
    padding-top: 7px;
    right: 5px;
    font-weight: bold;
}
.icon-down-open:before {
    content: '\002B';
    position: absolute;
    font-family: sans-serif;
    width: 1.5em;
    text-align: center;
    font-size: 1.6rem;
    padding-top: 7px;
    right: 5px;
    font-weight: bold;
}
.aside .jarvis-difference img{
	max-width: 220px;
}

/*mobile landscape*/
@media only screen and (min-device-width: 568px) 
                   and (max-device-width: 823px) 
                   and (orientation: landscape) {
	
	#results-view-details-btn{
		position: relative;
    	margin-left: 10px;
    	width: 97% !important;
	}
	#results-enquire-btn{
		position: relative;
    	margin-top: 10px;
    	margin-left: 10px;
		width: 97% !important;
	}
}

@media only screen and (min-device-width: 1024px) 
                   and (max-device-width: 1024px) 
                   and (orientation: portrait)
				   and (-webkit-min-device-pixel-ratio: 2) {
	#features-2col{
		margin-left:0px;
	}
	#results-view-details-btn {
		right: 0px !important;
		width: 97% !important;
	}
	#results-enquire-btn{
		width: 97% !important;
	}
}

/*iPhone X landscape*/
@media only screen 
  and (min-device-width: 812px) 
  and (max-device-width: 812px) 
  and (-webkit-min-device-pixel-ratio: 3)
  and (orientation: landscape) { 
  .column.main, .column.aside, .column.full-width{
	width:100% !important; 
  }
  .box-result-item{
  	height:auto !important;
  }
  .col1 .box-result-item .image{
  	padding-left: 0px !important;
    padding-right: 0px !important;
	height: auto !important;
    display: block !important;
    width: 100% !important;
  }
  .col1 .box-result-item .details{
  	display: block;
    width: 100% !important;
	padding-top: 10px !important;
  }
  .col1 .box-result-item .blurb{
  	float: left;
    display: block;
    width: 100% !important;
    padding: 10px 10px 0 10px;
  }
  #results-view-details-btn{
  	right: 0px !important;
    width: 97% !important;
    margin-bottom: 0px !important;
  }
  #results-enquire-btn{
  	right: 0px !important;
	width: 97% !important;
  }
  .sticky{
  	display:none !important;
  }
	  
	#refine-Search
	{
		
		text-transform: none;
		font-size: 1.6rem;
		width:195px;
		background-color:#004b8d;
		
		
		}
	  
	  #remove-affordability-search{
	  
	      text-transform: none;
    font-size: 1.6rem;
		  width: 245px;}
    /* margin-left: -179px; */
	  
	  
	  
	  
}

/*pixel 2 xl landscape*/
@media only screen 
  and (min-device-width: 823px) 
  and (max-device-width: 823px) 
  and (-webkit-min-device-pixel-ratio: 3)
  and (orientation: landscape) { 
  .column.main, .column.aside, .column.full-width{
	width:100% !important; 
  }
  .box-result-item{
  	height:auto !important;
  }
  .col1 .box-result-item .image{
  	padding-left: 0px !important;
    padding-right: 0px !important;
	height: auto !important;
    display: block !important;
    width: 100% !important;
  }
  .col1 .box-result-item .details{
  	display: block;
    width: 100% !important;
	padding-top: 10px !important;
  }
  .col1 .box-result-item .blurb{
  	float: left;
    display: block;
    width: 100% !important;
    padding: 10px 10px 0 10px;
  }
  #results-view-details-btn{
  	right: 0px !important;
    width: 97% !important;
    margin-bottom: 0px !important;
  }
  #results-enquire-btn{
  	right: 0px !important;
	width: 97% !important;
  }
  .sticky{
  	display:none !important;
  }
}

/*ipad mini landscape*/
@media only screen 
and (min-device-width : 768px) 
and (max-device-width : 1024px) 
and (orientation : landscape)
and (-webkit-min-device-pixel-ratio: 1)  {
  .column.main, .column.aside, .column.full-width{
	width:100% !important; 
  }
  .box-result-item{
  	height:auto !important;
  }
  .col1 .box-result-item .image{
  	padding-left: 0px !important;
    padding-right: 0px !important;
	height: auto !important;
    display: block !important;
    width: 100% !important;
  }
  .col1 .box-result-item .details{
  	display: block;
    width: 100% !important;
	padding-top: 10px !important;
  }
  .col1 .box-result-item .blurb{
  	float: left;
    display: block;
    width: 100% !important;
    padding: 10px 10px 0 10px;
  }
  #results-view-details-btn{
  	right: 0px !important;
    width: 97% !important;
    margin-bottom: 0px !important;
	position:relative;
	margin-left:10px;
  }
  #results-enquire-btn{
  	right: 0px !important;
	width: 97% !important;
	position:relative;
	margin-left:10px;
	margin-top:10px;
  }
  .sticky{
  	display:none !important;
  }
}
</style>
<?php
	if($site['siteID'] != "3" && $site['siteID'] != "6") {
?>
<style type="text/css">
.panel .header {
    background-color: #5db741 !important;
}
</style>
<?php } ?>

<div class="page-body">
  
  <div class="row">
    <div class="row-inner">
      <?php include("crumbs.inc.php"); ?>
      <div class="content">
        <div class="column main skinny brand-text">
        
         <!-- budget cal -->
         <div class="panel">
            	<!--	Budget Calculator --> 
                <div class="header" >
                	<span class="ico icon-down-open"> </span>
              		<h2 >Search By Repayments </h2>
                </div>
			       <?php if($budgetSearchFlag !=1) { ?>
			 <div class="col col1 column1 refine">
						  <a class="refine-search" href="#" onclick="setActiveSearch('9');">
						  
                    <div class="btn btn-delete txt_center" id="refine-search-mobile" >	<h2 style="margin-bottom: 0px; color: #fff;line-height: 1.2em; " >Refine Search </h2></div>
							 
							 
							 
                        </a>
                   </div> 
			 
			 
			 
			
			 	<?php } ?>
                
                <div class="body"> 
					<?php include("pages/finance/affordability-calculator/calculator.inc-budget.php"); ?>
                </div>
			 
		
            
            </div> <!--end panel -->
			 <div>
				 <?php 
				 
			/*	 if(($site['siteID'])=='1'||($site['siteID'])=='2'&&($today <= '2019-12-01 00:00:00')){ ?>


				 <a href="https://www.jarvisford.com.au/search/all-cars/?sPr%5B%5D=8"> <img class="no-display-mobile" src="<?php echo(CDN_URL); ?>img/banners/black-friday/Mini676x110px.jpg" alt="" title="" style="width:100%;height: auto;padding-bottom: 10px;" />
				 
				  <img class="no-display-desktop" src="<?php echo(CDN_URL); ?>img/banners/black-friday/Mini603x100px.jpg" alt="" title="" style="width:100%;height: auto;padding-bottom: 10px;padding-top: 10px;" />
				 </a>
				
				 <?php }  */ //include("banner.php"); ?>
            </div>
         <?php if($budgetSearchFlag==1) { ?>
                <div class="box box-shadow search-notification">
                    <div style="margin-bottom: 15px;">Your search result for cars up to <b><?php echo("$".number_format($budgetAmount,2,'.',',')); ?></b> is below. Refine your search further by selecting from "More Options". 
                 </div>
                    
                   <div class="col col3 column1 redo-budget">
                            <a class="reinput-affordability-calculator" href="#">
                     <div class="btn btn-search redo-calculator btn-budget" id = "re-input" >Re-Input</div>
                            </a>
                   </div> 
					
					
                   
                   <div class="col col3 column2 reset-budget">
                       <a class="remove-budget-search" href="#"> 
						   
               <div class="btn btn-delete txt_center " id="remove-affordability-search" >Remove Repayments</div>
                        </a>
                   </div>   
					
					
					
					<div class="col col3 column3 refine">
						  <a class="refine-search" href="#" onclick="setActiveSearch('8');">
						  
                    <div class="btn btn-delete txt_center" id="refine-search" >Refine Search</div>
							 
							 
							 
                        </a>
                   </div> 
                </div>            
 			<?php } ?> 
			
			 <!-- end buget --> 
         	<div class="box box-content closed">
            <div class="search-results-filter">
				
			<?php  
					
			$MCM_Make = $vehicles[0]['makeName'];
			$MCM_Model = $vehicles[0]['modelName'];
			$MCM_Body = $vehicles[0]['bodyName'];	
				
			if ( count($_SESSION['search']['makes']) 	== 1 &&
			   	 count($_SESSION['search']['models']) 	== 1 
			   )
			   { if($count>1){echo ("<h3><b>".$count."</b> ".$MCM_Make .' '.$MCM_Model."s found</h3>");}
					else { echo ("<h3><b>".$count."</b> ".$MCM_Make .' '.$MCM_Model." found</h3>");}				
			   }
			elseif ( count($_SESSION['search']['makes']) 	== 1 )
			   { if($count>1){echo ("<h3><b>".$count."</b> ".$MCM_Make."s found</h3>");}
				else{echo ("<h3><b>".$count."</b> ".$MCM_Make." found</h3>");}				
			   }
			elseif ( count($_SESSION['search']['models']) 	== 1 )
			   {if($count>1){echo ("<h3><b>".$count."</b> ".$MCM_Model."s found</h3>");}
				else{echo ("<h3><b>".$count."</b> ".$MCM_Model." found</h3>");}				
			   }
			elseif ( count($_SESSION['search']['bodies']) 	== 1 )
			   {if($count>1){echo ("<h3><b>".$count."</b> ".$MCM_Body."s found</h3>");}
				else {echo ("<h3><b>".$count."</b> ".$MCM_Body." found</h3>");	}			
			   }
			else 
			   {if($count>1){echo ("<h3><b>".$count."</b> cars found</h3>");}
				else{echo ("<h3><b>".$count."</b> car found</h3>");}				
			   }
			
			?> 
			
			
        
                <!-- <h3><b><?php echo ($count); ?></b> cars found</h3> --> 
				
                <div class="options">
                  <p>Sort by:</p>
<form method="get" name="sortForm" class="sort-form" >
<select name="sSortKey" id="sortBy" data-option-key="sortBy" class="chzn-select sortField" data-placeholder="Sort By...">
<?php
$sorts = array();
$sorts = get_sort_array($pdo);
foreach($sorts as $a) {
	echo ("<option value=\"".$a['sortID']."\"");
	if($_SESSION['search']['sort']['key'] == $a['sortID']) {
		echo (" selected=\"selected\"");
	}
	echo (">".$a['sortName']."</option>");
}
?>
</select>
<select name="sSortDir" id="sortDirection" data-option-key="sortDirection" class="chzn-select sortField">
    <option value="asc" <?php if($_SESSION['search']['sort']['dir'] == "asc") { echo (" selected=\"selected\""); } ?>>Asc</option>
    <option value="desc" <?php if($_SESSION['search']['sort']['dir'] == "desc") { echo (" selected=\"selected\""); } ?>>Desc</option>
</select>
<input type="hidden" name="sXx" value="1" />
<input type="hidden" id="sortbudgetSearchFlag" name="budgetSearch" value="<?php echo $budgetSearchFlag ?>" />
<input type="hidden" id="sortbudgetAmount" name="budgetAmount" value="<?php echo $budgetAmount ?>" />
<?php if(isset($_SESSION['search']['sBodyID'])) { ?>
<!--fix for sBodyID-->
<input type="hidden" id="sortsBodyID" name="sBodyID" value="<?php if(isset($_SESSION['search']['sBodyID'])) { echo $_SESSION['search']['sBodyID'][0]; }else{echo "0";} ?>" />
<!--fix for sBodyID-->
<?php } ?>

</form>
<!--<a href="javascript:void(0);" class="icon icon-menu layoutToggle" data-view="list" title="List View"></a><a href="javascript:void(0);" class="icon icon-layout layoutToggle" data-view="tiles" title="Grid View"></a>-->
                </div>
              </div>
            </div>
         <div class="box box-col-container">
		<?php
		foreach($vehicles as $car) {	 
		$carTitle= $car['year']." ".$car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName'];	 
		}
		//enquiry processor
		// enquire this car form
		$formFields = array();
		
		
$formFields[] = "enquireThisCar_name";
$formFields[] = "enquireThisCar_email";
$formFields[] = "enquireThisCar_phone";
$formFields[] = "enquireThisCar_comments";

$formFields[] = "enquireThisCar_contact";
$enquireThisCarFormFields = array();
$enquireThisCarFormFields[] = "enquireThisCar_name";
$enquireThisCarFormFields[] = "enquireThisCar_email";
$enquireThisCarFormFields[] = "enquireThisCar_phone";
$enquireThisCarFormFields[] = "enquireThisCar_comments";

$enquireThisCarFormFields[] = "enquireThisCar_contact";
	
		
	$etype="vehicle";
		
		$formFileFields = array();
		$formData = array();
foreach($formFields as $k => $v) 
	{
	if(isset($_REQUEST[$v])) 
		{ 
		$formData[$v] = $_REQUEST[$v]; 
		} 
	else 
		{ 
		$formData[$v] = NULL; 
		}
	}
		
		
		$enquireThisCarFormData = array();
foreach($enquireThisCarFormFields as $k => $v) 
	{
	if(isset($_REQUEST[$v])) 
		{ 
		$enquireThisCarFormData[$v] = $_REQUEST[$v]; 
		} 
	else 
		{ 
		$enquireThisCarFormData[$v] = NULL; 
		}
	}
		
		
		
		if(isset($_POST['submit'])) 
	{
	
	/**
	 * Form fields name used to define the sender's email address and name
	 */
	$emailAddressField = "car_email";
	$nameField = "car_name";
	/**
	 * "To", "Cc", "Bcc" recipient lists
	 * 
	 * @param array $emailTo Array containing key value pairs of email address and recipient name
	 * $emailTo = array("johndoe@emailaddress.com" => "John Doe", "janedoe@emailaddress.com" => "Jane Doe");
	 * $emailCc = array("billsmith@emailaddress.com");
	 * $emailBcc = array("santa@emailaddress.com");
	 */
	$emailTo = array("navneetk@jarviscars.com.au");
		
	$emailCc = array();
	$emailBcc = array("webmaster@jarviscars.com.au");
	
	$siteName=""; 
	if($site['siteID']> 0)
		{
	  	$siteName="Jarvis Website";
		}
	 
	$emailSubject = $siteName. " | ". $car['typeName']." ". $car['makeName']." ".$car['modelName']." ".$car['bodyName']." Enquiry | ".$car['stockNumber'];
	
	$emailPriority = 3;
	
	$emailCustomHeaders = array();
	
	/***** now catering for Testing drive bookings as well  */
	//check for enquiry type default to vehicle. 
	
	if(isset($_REQUEST['enquire_type']))
		{
		$etype=	$_REQUEST['enquire_type'];
		}
	
	if($etype=="enquireThisCar")
		{
		$emailAddressField = "enquireThisCar_email";
		$nameField = "enquireThisCar_name";
		$emailSubject = $siteName. " | ". $car['typeName']." ". $car['makeName']." ".$car['modelName']." ".$car['bodyName']." Enquiry | ".$car['stockNumber'];
		//check availability flag
		if($enquire_checkAvailabilityFlag=='1')
			{
			$emailSubject = $siteName. " | ". $car['typeName']." ". $car['makeName']." ".$car['modelName']." ".$car['bodyName']." Check Availability Enquiry | ".$car['stockNumber'];
			}
		// send the test drive form in 
		$formData = $enquireThisCarFormData;
 		}
				$formData = $enquireThisCarFormData;
		
		include("enquiry-processor.inc.php");
			}
		
	//enquiry confirmation
		
		if(!empty($emailResult)) 
						{
						//include("enquiry-confirmation.inc.php");
						$formName = "Individual Vehicle Enquiry"." ".$car['typeName'];//Individual Vehicle Enquiry
						$eventLabel = "Individual Vehicle Enquiry"." ".$car['typeName'];
						$formName2 = "";
						$eventLabel2 = "";
						
							
						if($etype=="enquireThisCar")
								{
								$formName = "Enquire This Car"." ".$car['typeName'];
								$eventLabel = "Enquire This Car"." ".$car['typeName'];
								//only for toyota
								if ($site['siteID']=='3')
									{
									$formName2 = "Enquire This Car"." ".$car['typeName'];
									$eventLabel2 = "Enquire This Car"." ".$car['typeName'];
									if (($car['typeName']) == "Demo") 
										{
										$formName = "Individual Vehicle Enquiry New";
										}
									else
										{
										$formName = "Individual Vehicle Enquiry"." ".$car['typeName']; //Individual Vehicle Enquiry
										}
									$eventLabel = "Individual Vehicle Enquiry"." ".$car['typeName'];
									}
								//check availability flag
								if($enquire_checkAvailabilityFlag=='1')
									{
									$formName = "Enquire This Car - Check Availability ".$car['typeName'];//Check Availability Form
									$eventLabel = "Enquire This Car - Check Availability ".$car['typeName'];
									}
								}
								
						else
							{
							//do nothing
							}

						// tracking vehicle
						// TRIGGER ANALYTICS CONFIRMATION ACTION
						echo ("<script type=\"text/javascript\">\n");
						echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Submitted', 'gaEventLabel': '" .$eventLabel."' });");
						echo ("dataLayer.push ({ 'event': 'toyotaFormSubmitted', 'formName': '".$formName."', 'formStatus': 'submitted' });");
						echo ("</script>");

						//only for toyota
						if ($site['siteID']=='3')
							{
							if($formName2!="")
								{
								// tracking vehicle
								// TRIGGER ANALYTICS CONFIRMATION ACTION
								echo ("<script type=\"text/javascript\">\n");
								echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Submitted', 'gaEventLabel': '" .$eventLabel2."' });");
								echo ("dataLayer.push ({ 'event': 'toyotaFormSubmitted', 'formName': '".$formName2."', 'formStatus': 'submitted' });");
								echo ("</script>");
								}
							}
		 // CLEAR FORM DATA
						$formData = array();
						foreach($formFields as $k => $v) 
							{
							$formData[$v] = NULL;
							}	
						} 
						else 
							{
							// TRIGGER ANALYTICS VIEW ACTION
							/*
							//	echo ("<script type=\"text/javascript\">\n");
							//	echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': 'Individual Vehicle Enquiry ".$car['typeName']."' });");
							//	echo ("dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': 'Individual Vehicle Enquiry ".$car['typeName']."', 'formStatus': 'viewed' });");
							//	echo ("</script>");
							*/
							
							//do nothing commented out
							//27-11-2019
							/*
							//check availability flag
							if(isset($_SESSION["checkAvailabilityFlag"])) 
								{
								if($_SESSION["checkAvailabilityFlag"]=='1')
									{
									// TRIGGER ANALYTICS FORM VIEW ACTION
									$formName = "Enquire This Car - Check Availability ".$car['typeName'];//Check Availability Form
									$eventLabel = "Enquire This Car - Check Availability ".$car['typeName'];
									echo ("<script type=\"text/javascript\">\n");
									echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': '".$eventLabel."' });");
									echo ("dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': '".$formName."', 'formStatus': 'viewed' });");
									echo ("</script>");
									if ($site['siteID']=='3')
										{
										if($formName2!="")
											{
											// tracking vehicle
											// TRIGGER ANALYTICS CONFIRMATION ACTION
											if (($car['typeID']) == '2') 
												{
												$formName2 = "Individual Vehicle Enquiry New";
												}
											else
												{
												$formName2 = "Individual Vehicle Enquiry"." ".$car['typeName']; //Individual Vehicle Enquiry
												}
											$eventLabel2 = "Individual Vehicle Enquiry  ".$car['typeName'];
											echo ("<script type=\"text/javascript\">\n");
											echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Submitted', 'gaEventLabel': '" .$eventLabel2."' });");
											echo ("dataLayer.push ({ 'event': 'toyotaFormSubmitted', 'formName': '".$formName2."', 'formStatus': 'submitted' });");
											echo ("</script>");
											}
										}
									}
								else if($_SESSION["checkAvailabilityFlag"]=='0')
									{
									// TRIGGER ANALYTICS FORM VIEW ACTION
									$formName = "Enquire This Car"." ".$car['typeName'];
									$eventLabel = "Enquire This Car"." ".$car['typeName'];
									echo ("<script type=\"text/javascript\">\n");
									echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': '".$eventLabel."' });");
									echo ("dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': '".$formName."', 'formStatus': 'viewed' });");
									echo ("</script>");
									if ($site['siteID']=='3')
										{
										if($formName2 != "")
											{
											// tracking vehicle
											// TRIGGER ANALYTICS CONFIRMATION ACTION
											if (($car['typeID']) == '2') 
												{
												$formName2 = "Individual Vehicle Enquiry New";
												}
											else
												{
												$formName2 = "Individual Vehicle Enquiry"." ".$car['typeName']; //Individual Vehicle Enquiry
												}
											$eventLabel2 = "Individual Vehicle Enquiry  ".$car['typeName'];
											echo ("<script type=\"text/javascript\">\n");
											echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Submitted', 'gaEventLabel': '" .$eventLabel2."' });");
											echo ("dataLayer.push ({ 'event': 'toyotaFormSubmitted', 'formName': '".$formName2."', 'formStatus': 'submitted' });");
											echo ("</script>");
											}
										}
									}
								}
							else
								{
								// TRIGGER ANALYTICS FORM VIEW ACTION
								$formName = "Enquire This Car"." ".$car['typeName'];
								$eventLabel = "Enquire This Car"." ".$car['typeName'];
								echo ("<script type=\"text/javascript\">\n");
								echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': '".$eventLabel."' });");
								echo ("dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': '".$formName."', 'formStatus': 'viewed' });");
								echo ("</script>");
								if ($site['siteID']=='3')
									{
									if($formName2!="")
										{
										// tracking vehicle
										// TRIGGER ANALYTICS CONFIRMATION ACTION
										if (($car['typeID']) == '2') 
											{
											$formName2 = "Individual Vehicle Enquiry New";
											}
										else
											{
											$formName2 = "Individual Vehicle Enquiry"." ".$car['typeName']; //Individual Vehicle Enquiry
											}
										$eventLabel2 = "Individual Vehicle Enquiry  ".$car['typeName'];
										echo ("<script type=\"text/javascript\">\n");
										echo ("dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Submitted', 'gaEventLabel': '" .$eventLabel2."' });");
										echo ("dataLayer.push ({ 'event': 'toyotaFormSubmitted', 'formName': '".$formName2."', 'formStatus': 'submitted' });");
										echo ("</script>");
										}
									}

								}
								*/
							//comment out end
							}
		 
			 
		
			 	 
			 

//get weekly specials 
$weekly_specials_list = get_weekly_specials_list();
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 

foreach($vehicles as $car) {
	
	
	
	$is_weekly_specials =0;
	$weekly_grad="";
	$weekly_border="";
	$feature_grad= "";
	$feature_border="";
	if($car['onlineSpecial']) {
		$feature_grad="featured-grad";
		$feature_border = "featured-border";
	}
	if(in_array($car['inventoryID'], $weekly_specials_list)){
		
		$weekly_grad="weekly-grad";
		$weekly_border="weekly-border";	
		$feature_grad= "";
		$feature_border="";
		$is_weekly_specials=1;
	}
	
	$noCarImageBorder = '';
	//display border if there is no car image
	if($car['photos'][0]=="_no-image.gif") {
		$noCarImageBorder = " style='border:1px solid #e1e1e1' ";
	}
	
	echo ("<div class=\"car col col1\">");
	/*echo ("<a href=\"/search/all-cars/All_Locations/".str_replace(" ","_",$car['makeName']."/".$car['modelName']."/".str_replace("/","",$car['bodyName']))."/".$car['inventoryID']."/\" class=\"box box-shadow box-result-item");*/
	echo ("<span class=\"box box-shadow box-result-item");
	if($car['onlineSpecial']) {
		echo (" special");
	}
	if(in_array($car['inventoryID'], $weekly_specials_list)){
		echo (" weekly-special-nogreyborder");
	}
	echo ("\">");
	
	echo("<div class=\"car-list-header ". $weekly_grad. $feature_grad.  "\"> ");
	
	//echo ("<span class=\"car-title \">".$car['mfrYear']." ".$car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName']."</span>");
	
	$seriesNameDisplay  = "<span class=\"car-series\">".$car['seriesName']."</span>" ;
	//$seriesNameDisplay  = "";
	$badgeNameDisplay  = "<span class=\"car-badge\">".$car['badgeName']."</span>" ;
	 //$bageNameDisplay ="";
	 
	echo ("<span class=\"car-title \">".$car['year']." ".$car['makeName']." ".$car['modelName']." ".$seriesNameDisplay." ".$badgeNameDisplay. "</span>");
	
	$notation = "";
	/*if(!$car['isDriveAway']) {
		$notation='<span class="notation">*</span>';
	}*/
	
	$drive_away= "";
	/*if($car['isDriveAway']) {
		 $drive_away = "<div class=\"price-notation\">Drive Away</div>";
	}*/
	
	//autogate_inventory
	$vehiclePrice = "";
	if($car['igcPrice'] > '0.00'){
		//drive away
		$drive_away = "<div class=\"price-notation\">Drive Away</div>";
		$vehiclePrice = $car['igcPrice'];
	}else if($car['egcPrice'] > '0.00'){
		$notation='<span class="notation">*</span>';
		$vehiclePrice = $car['egcPrice'];
	}else{
		
	}
	
	/*if ($car['retailPrice']<"1000"){
echo ("<span class=\"car-price \">Test Drive</span>");}
	
	else if ($car['isTestDrive']==1) { echo ("<span class=\"car-price \">Test Drive</span>");}
	
else{ echo ("<span class=\"car-price \">$".number_format($car['retailPrice'],0).$notation.$drive_away."</span>");
}*/
	
	//autogate_inventory
	if ($car['isTestDrive']==1) { 
		echo ("<span class=\"car-price \">Test Drive</span>");
	}else{ 
		echo ("<span class=\"car-price \">$".number_format($vehiclePrice,0).$notation.$drive_away."</span>");
	}
	
	
	//hr
	if($is_weekly_specials==0 && $feature_grad==""){
		echo "<hr class=\"refineSearch-hr\" style=\"width: auto;border-bottom: 1px solid #e1e1e1;margin: 10px 10px 0px 10px;\">";
	}
					
	echo ("</div>"); // end car list header
	
	echo ("<div class=\"car-box-details ". $weekly_border.  $feature_border."\">");  //begin body 
	 
	
	echo ("<div class=\"image\" style=\"cursor:pointer;\">");
//	echo ("<img title=\"\" alt=\"\" src=\"".CDN_URL."_cache/".$car['photos'][0]."\" />");
	//hyperlink
	echo ("<a style=\"display: block; position: absolute;height: 100%;width: 100%;top: 0;left: 0%;z-index: 200;background-color: rgba(0,0,0,0);\" href=\"/search/all-cars/All_Locations/".str_replace(" ","_",$car['makeName']."/". ucwords(strtolower($car['modelCode']))."/".str_replace("/","",$car['bodyName']))."/".$car['inventoryID']."/\"></a>");
	echo ("<img title=\"".$car['year']." ".$car['makeName']." ".$car['modelCode']." ".$car['seriesName']." ".$car['badgeName']."\" alt=\"".$car['year']." ".$car['makeName']." ".$car['modelName']." Image\" src=\"//www.jarviscars.com.au/_cache/".$car['photos'][0]."?w=320\"".$noCarImageBorder." />");
	if($is_weekly_specials) {
		echo ("<div class=\"weekly-tag\">Weekly Special</div>");

	}
	else 
	{
		if($car['onlineSpecial']) {
			echo ("<div class=\"featured\">Featured</div>");
		}
				
	}
	if(($car['programID']=='8') && ($today >= '2019-12-01')){
			
			echo ("<div class=\"featured\" style=\"background-color: transparent !important; padding:0 !important\"><img src=\"".(CDN_URL)."img/banners/black-friday/sale-overlay.png\"/></div>");
		}
	
	
	echo ("</div>");
	
	
	
	
	//begin details div
	
	
	echo ("<div class=\"details\">");
	echo ("<p class=\"title\">".$car['year']." ".$car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName']."</p>");
	echo ("<ul class=\"features\">");
	
	// doors, cylinder, transmission, , odometer, color 
	
	
	
	if(!empty($car['typeName'])) {
		echo ("<li title=\"Car Type\"><span class=\"icon icon-key\"></span>".$car['typeName']." Car</li>");
	}
	if(!empty($car['bodyName'])) {
		echo ("<li title=\"Body\"><span class=\"icon flaticon-car\"></span>");
		echo ($car['bodyName']."</li>");
	}
	
	
	
	
	
	
	
	
	
	$engineDetails = "";
			
	 if($car['fuelID']=='10'){
		 echo ("<li title=\"Engine\"><span class=\"icon flaticon-engine\"></span>");
		$engineDetails = 'Hybrid';
		echo ($engineDetails."</li>");
	 }
	else{
		
	if(!empty($car['capacityDescription']) && $car['capacityDescription']>0 ) {
		$engineDetails .= $car['capacityDescription'].'L';
	}
	if(!empty($car['cylinders']) && $car['cylinders']>0 ) {
		if($engineDetails!=''){
			$engineDetails .= " ";
		}
		$engineDetails .= $car['cylinders'].' Cylinders';
	}
	if($engineDetails!=''){
		echo ("<li title=\"Engine\"><span class=\"icon flaticon-engine\"></span>");
		echo ($engineDetails."</li>");
	}
	}
	
	
	
	//engine
	
	//engine

	echo ("</ul>");
	echo ("<ul id=\"features-2col\" class=\"features\">");
		
	
/*	
	echo ($car['bodyName']."</li>");
	*/

	if(!empty($car['transmissionName'])) {
		echo ("<li title=\"Transmission\"><span class=\"icon icon-flow-branch\"></span>".$car['transmissionName']."</li>");
	}
	/*
	if(!empty($car['vin'])) {
		echo ("<li title=\"VIN\"><span class=\"icon icon-code\"></span>".$car['vin']."</li>");
	}
	*/
	if(!empty($car['colourName'])) {
		echo ("<li title=\"Colour\"><span class=\"icon flaticon-colour\"></span>".$car['colourName']."</li>");
	}else{
		echo ("<li title=\"Colour\"><span class=\"icon flaticon-colour\"></span>".$car['genericColour']."</li>");
	}
	
	//kilometers
	if(!empty($car['kilometres'])) {
		echo ("<li title=\"Odometer\"><span class=\"icon icon-gauge\"></span>".number_format($car['kilometres'],0)."km</li>");
	}
	//kilometers
	echo ("</ul>");
	
	
	echo ("<div class=\"pricing\">");
	echo ("<div class=\"amount on\">");
	//
	
	/*if ($car['retailPrice']>"1000")
	{ echo ("<p class=\"price\">$".number_format($car['retailPrice'],0).$notation."</p>");
	if($car['isDriveAway']) {
		echo ("<p class=\"availability\">Drive Away</p>");
	}
	}
	else{ echo("<p class=\"price\">TEST DRIVE</p>");}*/
	
	
	
	//
	
	//autogate_inventory
	if ($vehiclePrice > "1000"){ 
		echo ("<p class=\"price\">$".number_format($vehiclePrice,0).$notation."</p>");
		if($car['igcPrice'] > '0.00') {
			echo ("<p class=\"availability\">Drive Away</p>");
		}
	}
	if ($car['isTestDrive']==1) { 
		echo("<p class=\"price\">TEST DRIVE</p>");
	}
	
	echo ("</div>");
	echo ("<div class=\"amount off\">");
	$finance = calculate_finance($vehiclePrice);
	if(!empty($finance)) {
		echo ("<p class=\"price\">$". number_format($finance['periodPayment'],0) . "</p>");
		echo ("<p class=\"availability\">Per Week</p>");
	}
	echo ("</div>");

	echo ("</div>");
	echo ("</div>");
	//end of details div
	
	//begin blurb div
	echo ("<div class=\"blurb\">");
	//echo ("<p>".output_truncated_text($car['comments'], 275, "...", FALSE, TRUE)."</p>");
	$readMoreLink = "/search/all-cars/All_Locations/".str_replace(" ","_",$car['makeName']."/".ucwords(strtolower ($car['modelCode']))."/".str_replace("/","",$car['bodyName']))."/".$car['inventoryID']."/";
	$formattedComments = '';
	if($car['comments']!=''){
		$formattedComments = str_replace("<br>", " ", $car['comments']);
		$formattedComments = preg_replace('!\s+!', ' ', $formattedComments); 
		$formattedComments = substr($formattedComments, 0,132)."... ";
	}
	//echo ("<p>".output_truncated_text($formattedComments, 119, "...", TRUE, TRUE)."</p>");
	echo ("<p>".$formattedComments);
	//echo ("<input type='hidden' name='commentvID' id='commentvID' value='".$readMoreLink."'>");
	echo ("<a style=\"text-decoration: none;\" href=\"".$readMoreLink."\">Read more</a>"); 
	echo ("</p>");
	echo ("</div>");
	//end of blurb div
	
	//echo ("<div class=\"btn btn-details view-details\"> View Details </div>");
	echo ("<div id=\"results-view-details-btn\" class=\"btn btn-details view-details\">");
	//hyperlink
	echo ("<a style=\"display: block; position: absolute; height: 100%; width: 100%; top: 0; left: 0%; z-index: 200; background-color: rgba(0,0,0,0);\" href=\"/search/all-cars/All_Locations/".str_replace(" ","_",$car['makeName']."/".$car['modelName']."/".str_replace("/","",$car['bodyName']))."/".$car['inventoryID']."/\"></a>");
	echo ("View Details </div>");
	
	$carTitle= $car['year']." ".$car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName'];
	
	?>
			 
	<?php if($car['inventoryID']==9236||$car['inventoryID']==9690){
		//$carTitle= $car['year']." ".$car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName']; ?>
	
		
			 
	<script src="https://cdn.jsdelivr.net/npm/@simonwep/selection-js/dist/selection.min.js"></script>		 
<!-- enquire from popup		!-->
			 
			 
			 
	<div id="results-enquire-btn" class="btn btn-details enquire"  onClick="myFunction('<?php echo($car['inventoryID']); ?>');">
		
		
		
		

			<a class="enquire_popup_open" href="" id="<?php echo $car['inventoryID']; ?>" style="font-size:16px;color:#fff;  ">
			Enquire Now
		</a>

			 
				
			 
			
			 
			 		</div>
	
			 
			 <?php  } else{
		
		
		//new enquire button
	echo ("<div id=\"results-enquire-btn\" class=\"btn btn-details enquire\">");
	//hyperlink
	echo ("<a style=\"display: block; position: absolute; height: 100%; width: 100%; top: 0; left: 0%; z-index: 200; background-color: rgba(0,0,0,0);\" href=\"/search/all-cars/All_Locations/".str_replace(" ","_",$car['makeName']."/".$car['modelName']."/".str_replace("/","",$car['bodyName']))."/".$car['inventoryID']."/?enquireBtnClicked=1#enquireTab\"></a>");
	echo ("Enquire</div>");
	
		

		
	} ?>
			 
			 
			 
		
							 
			 
			 
			 <?php
	
	
	
	
	// end body-details
	echo ("</div><!--end body detials -->");
	echo ("</span>");
	echo ("</div>");
	
	
}//end of foreach loop
			 
	?>
		
		<div id="enquire_popup" class="well" name="<?php echo $car['inventoryID']; ?>" style="max-width:44em;z-index: 20000001;">
			<a href="#" class="enquire_popup_close" style="float:right;padding:0 0.4em;text-decoration:none;color:#000000;font-weight:bold;" id="<?php echo $car['inventoryID']; ?>" name ="carEnquiry">
								X
							</a>
										<h2 style="text-align: left;">
											Enquire Now
										</h2>
										
										<h2 style="text-align: left;">
											<hr style="border-bottom: 1px solid black;margin-bottom:0px;"></br>
											<?php 
												echo ($carTitle);
											?>
											<hr style="border-bottom: 1px solid black;" >
										</h2>
										
										<div class="container" id ="<?php echo $car['inventoryID']; ?>">
											<!-- onsubmit="return validate_test_drive_form()">!-->
										<form name="Enquire This Car <?php echo $car['typeName']; ?>" id="enquireThisCarForm" method="post" action="
												<?php 
													echo ($_SERVER['REQUEST_URI']); 
												?>">
												Preferred contact method:
													
														
													               
												<select name="enquireThisCar_contact" id="enquireThisCar_contact" class="justselect">
														
														<option style="margin-top: 7pt;"    value="Email">Email</option>

														<option style="margin-top: 7pt;"   value="Call" >Call</option>

														<option style="margin-top: 7pt;"     value="SMS">SMS</option>
														
											</select> </br>
													
											<!--additional Finance Form Fields-->
												<div class="form-row">
														<div class="form-group col-md-6">
															<input type="text" name="enquireThisCar_name" id="enquireThisCar_name" required="required" value="<?php echo ($enquireThisCarFormData['enquireThisCar_name']); ?>" placeholder="Name*" />
														</div>
														<div class="form-group col-md-6">
															<input type="tel" name="enquireThisCar_phone" id="enquireThisCar_phone" required="required" value="<?php echo ($enquireThisCarFormData['enquireThisCar_phone']); ?>"  placeholder="Phone*" />
														</div>
														<div class="form-group">
															<!--<label for="exampleInputEmail1">Email address</label>-->
															<input type="email" class="form-control" name="enquireThisCar_email" id="enquireThisCar_email" required="required" aria-describedby="emailHelp" value="<?php echo ($enquireThisCarFormData['enquireThisCar_email']); ?>"  placeholder="Email">
														</div>
														<div class="form-group">
															<textarea name="enquireThisCar_comments" style="height:100px;" value="<?php echo ($enquireThisCarFormData['enquireThisCar_comments']); ?>"  placeholder="Your Message"></textarea>
														</div>
												</div>

										
												<div class="form-group emailAlt">
														<label for="emailAlt">Leave Blank</label>
														<input type="text" value="" id="emailAlt" name="emailAlt" />
												</div>
											
												<input type="hidden" name="enquire_type" value="FinanceEnquiry">
												<input type="hidden" name="finance_vID" value="<?php echo ($car['inventoryID']); ?>" />
												<input type="hidden" name="finance_url" value="<?php echo ($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" />
												<div style="display: flex;justify-content: center;">
												<input type="submit" name="submit" value="Send" class="btn btn-send vdp" style="-webkit-appearance: none;border-radius: 0;-webkit-border-radius: 0;"/>
											</div>
											</form>

									
									
									   </div>

									</div>
		
		
		
		
		
		
			 
			 
			 
			 
			 
			 
			 

			</div>
            <div class="box">
           		<?php
                if($count > 0) {
					echo output_pagination($pageNumber,$pagination);
				} else {
					echo ("<div class=\"box box-shadow gradient-bg\">");
					echo ("<form class=\"search-form\" name=\"advancedSearch\" id=\"advancedSearch\" method=\"get\" action=\"/search/search-processor.php\">");
					echo ("<h2>Please refine your search</h2>");
					echo ("<fieldset class=\"box\">");
					echo ("<div id=\"advancedSearchFields\">");
					include("pages/search/advanced-search.ajax.php");
					echo ("</div>");
					echo ("</fieldset>");
					echo ("<div class=\"col col1\">");
					echo ("<input type=\"submit\" name=\"submit\" value=\"Search\" class=\"btn btn-send\" />");
					echo ("</div>");
					echo ("</form>");
					echo ("</div>");
					
					
				
				}
				?>
            </div>
            <div class="box">
           		<?php
                if($count > 0) {
					echo '<p  class="vehicle-disclaimer"> Note: Vehicle features shown are obtained from a third party data provider and are based on manufacturer standard specifications. Actual features for this vehicle may differ, and will be confirmed by Jarvis upon enquiry. </p>';
					
				}
				?>
            </div>
        </div> <!-- end main -->

        <div class="aside column skinny">
			<?php
			include("refine-search.inc.php");
			include("side-tiles.inc.php");
			?>
        </div>
      </div>

    </div>
  </div>
<?php
include("blocks/jarvis-difference-full-width.php");
?>
</div><!-- /.home-body -->
<?php include('footer.inc.php'); ?>
<!-- PAGE SPECIFIC JAVASCRIPT HERE -->

<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/noUiSlider/nouislider.min.js"></script> 
<!--<script type="text/javascript" src="<?php //echo(CDN_URL); ?>js/wNumb.js"></script> -->
<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/slider-function.js?v=1"></script>

<script type="text/javascript">
	

			
			
function myFunction(x) {
  //var x = document.getElementById("results-enquire-btn");
 console.log(x);
}

		//--implement 19/09/2019 START--/
$(document).ready(function() {

							  // Initialize the plugin
							  
							  $('#enquire_popup').popup()});

			//--implement 19/09/2019 END--/
		
	

$(document).ready(function(e) {
	
	var priceStep =  5000;
	var yearStep  = 1;
		
	$(".layoutToggle").on("click",this,function(e) {
		var view = $(this).data("view");
		
		if(view == "tiles") {
			$(".car").each(function(index, element) {
                $(element).removeClass("col1");
                $(element).addClass("col3");
				$(element).first(".box-result-item").removeClass("box-shadow");
            });
		} else {
			$(".car").each(function(index, element) {
                $(element).removeClass("col3");
                $(element).addClass("col1");
				$(element).first(".box-result-item").removeClass("box-shadow");
            
			});
			
		}
	});
	
	$(".sortField").on("change",this,function(e) {
		$(this).closest('form').trigger('submit');
	});

	$("#reset-search").on("click", function(event) {
	//$(document).on("click", "#reset-search", function(event) {	 
		$("#refineSearch")[0].reset();
		$( "input[name='sNu']" ).val('');
		$( "input[name='sRe']" ).val('');	
		$(".chzn-select").val('').trigger("chosen:updated");
		//reset checkbox for the type of cars
		$("input:checkbox.refine-search[name='sTy[]']").each(function(){
			$(this).prop('checked', false);				
		});
		$("#typeID-0").prop('checked', true);
		 
		//Scroll to the top of the page when the reset search button is clicked
		$('html,body').scrollTop(0);
		
		//reset search session
		$.ajax({
			type: "GET",
			url: "/search/reset-search.ajax.php",
			data: '',
		})
		
		//mnaully reset price range from 0 to 2000
		// Set both slider handles
		price_slider_rs.noUiSlider.set([<?php echo $priceMin ; ?>, <?php echo $priceMax; ?>]);
		year_slider_rs.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		
		
		if ($("#price-slider-as" ).length ) {
			price_slider_as.noUiSlider.set([<?php echo $priceMin ; ?>, <?php echo $priceMax; ?>]);
			year_slider_as.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		}
		
		if ($("#year-slider-as" ).length ) {
			year_slider_as.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		}
		
		dataLayer.push({
			'eventCategory' : 'Site Actions',
			'eventAction' : 'SRP - Reset Search'
		});
	
	});
	
	//new code
	$("#reset-search-with-more-options").on("click", function(event) {
	//$(document).on("click", "#reset-search-with-more-options", function(event) {		
		$("#refineSearch")[0].reset();
		$( "input[name='sNu']" ).val('');
		$( "input[name='sRe']" ).val('');	
		$(".chzn-select").val('').trigger("chosen:updated");
		//reset checkbox for the type of cars
		$("input:checkbox.refine-search[name='sTy[]']").each(function(){
			$(this).prop('checked', false);				
		});
		$("#typeID-0").prop('checked', true);
		 
		//Scroll to the top of the page when the reset search button is clicked
		$('html,body').scrollTop(0);
		
		//reset search session
		$.ajax({
			type: "GET",
			url: "/search/reset-search.ajax.php",
			data: '',
		})
		
		//mnaully reset price range from 0 to 2000
		// Set both slider handles
		price_slider_rs.noUiSlider.set([<?php echo $priceMin ; ?>, <?php echo $priceMax; ?>]);
		year_slider_rs.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		
		
		if ($("#price-slider-as" ).length ) {
			price_slider_as.noUiSlider.set([<?php echo $priceMin ; ?>, <?php echo $priceMax; ?>]);
			year_slider_as.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		}
		
		if ($("#year-slider-as" ).length ) {
			year_slider_as.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		}
		
		dataLayer.push({
			'eventCategory' : 'Site Actions',
			'eventAction' : 'SRP - Reset Search With More Options'
		});
	
	});
			
	$('.chosen-container .chosen-results').on('touchend', function(event) {
		event.preventDefault();
		return ;
	 });
	
	$("#refineSearch").on("change",".chzn-select",function(e) {
		if (!$('option:selected',this).length) {
			$(this).val($($('option:first',this)).val());
		}
		var formData = $("#refineSearch").serialize();
		$.ajax({
			type: "GET",
			url: "/search/refine-search.ajax.php",
			data: formData,
		})
		.done(function(html) {
			$("#refineSearchFields").html(html);
			$("#refineSearch .chzn-select").each(function(index, value){
				$(this).chosen({allow_single_deselect: true});
			})
			var advFlag = $("#refineSearch #advFlag").val();
			if(advFlag == 1) {
				$("#refineSearch .advSearchFieldsWrapper").css('display',"block");
				$("#refineSearch .advSearchFields legend").addClass("active");
			}

			if($( "#price-slider-rs" ).length ) {
				price_slider_rs = create_price_range_slider(<?php echo $priceMin;?>, 150000, $( "#pMiRS2" ).val(),$( "#pMaRS2" ).val(), document.getElementById("price-slider-rs"), priceStep);
				
				//positioning of the left and right handles
				/*$(".noUi-handle.noUi-handle-lower").css({
					"left": "-20px"
				});
				$(".noUi-handle.noUi-handle-upper").css({
					"left": "-4px"
				});*/
				
				// update on change 
				price_slider_rs.noUiSlider.on('update', function( values, handle ) {
					var selectedMinValue = values[0].replace('$','').replace(',','');
					var selectedMaxValue = values[1].replace('$','').replace(',','');
					
					//range increments by 10000 after a value of 100000
					if((selectedMinValue <= 150000 && selectedMinValue > 100000)){
						console.log(selectedMinValue);
						selectedMinValue = 100000 + (selectedMinValue - 100000) * 2;
					}
					if((selectedMaxValue <= 150000 && selectedMaxValue > 100000)){
						console.log(selectedMaxValue);
						selectedMaxValue = 100000 + (selectedMaxValue - 100000) * 2;
					}
					
					$( "#pMiRS" ).val(Math.round(selectedMinValue));
					$( "#pMaRS" ).val(Math.round(selectedMaxValue));
					var minFormat  = Math.floor( selectedMinValue/1000);
					if(minFormat> 0){ minFormat= minFormat + "k"; } 
					$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(selectedMaxValue/1000) + "k"  );		
				});
			}
			// re-create
			
			if($( "#year-slider-rs" ).length ) {
				year_slider_rs = create_slider(<?php echo $yearMin;?>, <?php echo $yearMax;?>, $( "#yMiRS" ).val(),$( "#yMaRS" ).val(), document.getElementById("year-slider-rs"), yearStep); 
				year_slider_rs.noUiSlider.on('update', function( values, handle ) {
					$( "#yMiRS" ).val(Math.round(values[0]));
					$( "#yMaRS" ).val(Math.round(values[1]));
					$("#yearRangeAmountRS" ).text(Math.round(values[ 0 ]) + " - " + Math.round(values[ 1 ]));
					
				});	
			}
			
		});
	});
	
	var advFlag = $("#refineSearch #advFlag").val();
	if(advFlag == 1) {
		$("#refineSearch .advSearchFieldsWrapper").css('display',"block");
		$("#refineSearch .advSearchFields legend").addClass("active");
		//display the search and reset search buttons when the more options is being expanded
		$("#search-cars-with-more-options").css('display',"block");
		$("#reset-search-with-more-options").css('display',"block");
	}
	
	//$('#refineSearch .advSearchFields legend').on('click',this,function() {
	$(document).on("click", "#refineSearch .advSearchFields legend", function(event) {
		$('.advSearchFieldsWrapper').toggle();
		$(this).toggleClass("active");
		if( $('.advSearchFieldsWrapper').is(':hidden')){
			//hide the search and reset search buttons when the more options is being closed
			$("#search-cars-with-more-options").css('display',"none");
			$("#reset-search-with-more-options").css('display',"none");
		}else{
			//display the search and reset search buttons when the more options is being expanded
			$("#search-cars-with-more-options").css('display',"block");
			$("#reset-search-with-more-options").css('display',"block");
		}
	});
	
	/* budget calculator */
	
	$("#budget-calculator-box h2").hide();
	$("#budget-calculator-box").parent().removeClass("box-content");
	
	$(".panel  .header").on("click",this,function(e) {
		$(".panel .body").slideToggle();
		$(".panel .header .ico").toggleClass("icon-up-open");
		$(".panel .header .ico").toggleClass("icon-down-open");
	});
	 
	//redo budget calculator 
	$(".redo-budget").on("click",this,function(e) {
		 
		if( $(".panel .body").is(':hidden')){
			$(".panel .body").slideToggle();
			$(".panel .header .ico").toggleClass("icon-up-open");
			$(".panel .header .ico").toggleClass("icon-down-open");
			
		}
	 });
	
	
	if($( "#price-slider-rs" ).length ) {
		// create price sliders for Refine Search
		var price_slider_rs = create_price_range_slider(<?php echo $priceMin;?>, 150000, $( "#pMiRS2" ).val(),$( "#pMaRS2" ).val(), document.getElementById("price-slider-rs"), priceStep);
		
		//positioning of the left and right handles
		/*$(".noUi-handle.noUi-handle-lower").css({
			"left": "-20px"
		});
		$(".noUi-handle.noUi-handle-upper").css({
			"left": "-4px"
		});*/
			
		price_slider_rs.noUiSlider.on('update', function( values, handle ) {			
			var selectedMinValue = values[0].replace('$','').replace(',','');
			var selectedMaxValue = values[1].replace('$','').replace(',','');
			
			//range increments by 10000 after a value of 100000
			if((selectedMinValue <= 150000 && selectedMinValue > 100000)){
				console.log(selectedMinValue);
				selectedMinValue = 100000 + (selectedMinValue - 100000) * 2;
			}
			if((selectedMaxValue <= 150000 && selectedMaxValue > 100000)){
				console.log(selectedMaxValue);
				selectedMaxValue = 100000 + (selectedMaxValue - 100000) * 2;
			}
			
			$( "#pMiRS" ).val(Math.round(selectedMinValue));
			$( "#pMaRS" ).val(Math.round(selectedMaxValue));
			var minFormat  = Math.floor( selectedMinValue/1000);
			if(minFormat> 0){ minFormat= minFormat + "k"; } 
			$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(selectedMaxValue/1000) + "k"  );		
		});		
	}
	
	if($( "#year-slider-rs" ).length ) {
		var year_slider_rs = create_slider(<?php echo $yearMin;?>, <?php echo $yearMax;?>, $( "#yMiRS" ).val(),$( "#yMaRS" ).val(), document.getElementById("year-slider-rs"), yearStep);  
		year_slider_rs.noUiSlider.on('update', function( values, handle ) {
			$( "#yMiRS" ).val(Math.round(values[0]));
			$( "#yMaRS" ).val(Math.round(values[1]));
			$("#yearRangeAmountRS" ).text(Math.round(values[ 0 ]) + " - " + Math.round(values[ 1 ]));
		});
			
	}
	
	// create sliders for advance search 
	if($( "#price-slider-as" ).length ) {
		var price_slider_as = create_price_range_slider(<?php echo $priceMin;?>, 150000, $( "#pMiAS2" ).val(),$( "#pMaAS2" ).val(), document.getElementById("price-slider-as"), priceStep);
		
		//positioning of the left and right handles
		/*$(".noUi-handle.noUi-handle-lower").css({
			"left": "-20px"
		});
		$(".noUi-handle.noUi-handle-upper").css({
			"left": "-4px"
		});*/
		
		price_slider_as.noUiSlider.on('update', function( values, handle ) {
			var tempvalue =  values;
			var selectedMinValue = values[0].replace('$','').replace(',','');
			var selectedMaxValue = values[1].replace('$','').replace(',','');
			
			//range increments by 10000 after a value of 100000
			if((selectedMinValue <= 150000 && selectedMinValue > 100000)){
				console.log(selectedMinValue);
				selectedMinValue = 100000 + (selectedMinValue - 100000) * 2;
			}
			if((selectedMaxValue <= 150000 && selectedMaxValue > 100000)){
				console.log(selectedMaxValue);
				selectedMaxValue = 100000 + (selectedMaxValue - 100000) * 2;
			}
			
			$( "#pMiAS" ).val(Math.round(selectedMinValue));
			$( "#pMaAS" ).val(Math.round(selectedMaxValue));
			var minFormat  = Math.floor( selectedMinValue/1000);
			if(minFormat> 0){ minFormat= minFormat + "k"; } 
			$("#priceRangeAmountAS" ).text( "$" +  minFormat + " - $" + Math.floor(selectedMaxValue/1000) + "k"  );		
		});	
	}
	
	if ( $( "#year-slider-as" ).length ) {
		var year_slider_as = create_slider(<?php echo $yearMin;?>, <?php echo $yearMax;?>, $( "#yMiAS" ).val(),$( "#yMaAS" ).val(), document.getElementById("year-slider-as"), yearStep);  
		year_slider_as.noUiSlider.on('update', function( values, handle ) {
			$( "#yMiAS" ).val(Math.round(values[0]));
			$( "#yMaAS" ).val(Math.round(values[1]));
			$("#yearRangeAmountAS" ).text(Math.round(values[ 0 ]) + " - " + Math.round(values[ 1 ]));
		});		
	}
	
	$(document).on("change", "input:checkbox.refine-search[name='sTy[]']", function(event) {	
		//fix for demo and used cars search in manufacturer websites 
		var siteID = $("#refineSearch #siteID").val();
		var searchNewDemoCars = false;
		if ($('#typeID-3').is(":checked") || $('#typeID-2').is(":checked"))
		{
  			var searchNewDemoCars = true;
		}
		console.log("siteID val="+siteID);
		console.log("searchNewDemoCars val="+searchNewDemoCars);
		if((siteID < 8 && siteID > 1) || (siteID < 1) || (siteID == 8) || (siteID > 8)){
			//for manufacturer sites and jarvis barossa site
			if($( this ).attr( "id" )=="typeID-1"){	
				if($(this).is(':checked'))
				{
					$("input:checkbox.refine-search[name='sTy[]']").each(function(){
							$(this).prop('checked', false);				
					});
					$(this).prop('checked', true);	
					console.log("used");
					if(searchNewDemoCars == true){
						//$("#refineSearch")[0].reset();
						$( "input[name='sNu']" ).val('');
		 				$( "input[name='sRe']" ).val('');	
		 				$(".chzn-select").val('').trigger("chosen:updated");
		 				$("input:checkbox.refine-search[name='sTy[]']").each(function(){
							$(this).prop('checked', false);				
						});
						$(this).prop('checked', true);
						var formData = $("#refineSearch").serialize();
						$.ajax({
							type: "GET",
							url: "/search/refine-search-clear-vehicle-makes.ajax.php",
							data: formData,
						})
						
						.done(function(html) {
							$("#refineSearchFields").html(html);
							$("#refineSearch .chzn-select").each(function(index, value){
								$(this).chosen({allow_single_deselect: true});
							})	
							var advFlag = $("#refineSearch #advFlag").val();
							if(advFlag == 1) {
								$("#refineSearch .advSearchFieldsWrapper").css('display',"block");
								$("#refineSearch .advSearchFields legend").addClass("active");
							}
							
							if($( "#price-slider-rs" ).length ) {
								price_slider_rs = create_price_range_slider(<?php echo $priceMin;?>, 150000, $( "#pMiRS2" ).val(),$( "#pMaRS2" ).val(), document.getElementById("price-slider-rs"), priceStep);
								
								//positioning of the left and right handles
								/*$(".noUi-handle.noUi-handle-lower").css({
									"left": "-20px"
								});
								$(".noUi-handle.noUi-handle-upper").css({
									"left": "-4px"
								});*/
								
								// update on change 
								price_slider_rs.noUiSlider.on('update', function( values, handle ) {
									var selectedMinValue = values[0].replace('$','').replace(',','');
									var selectedMaxValue = values[1].replace('$','').replace(',','');
									
									//range increments by 10000 after a value of 100000
									if((selectedMinValue <= 150000 && selectedMinValue > 100000)){
										console.log(selectedMinValue);
										selectedMinValue = 100000 + (selectedMinValue - 100000) * 2;
									}
									if((selectedMaxValue <= 150000 && selectedMaxValue > 100000)){
										console.log(selectedMaxValue);
										selectedMaxValue = 100000 + (selectedMaxValue - 100000) * 2;
									}									
									
									$( "#pMiRS" ).val(Math.round(selectedMinValue));
									$( "#pMaRS" ).val(Math.round(selectedMaxValue));
									var minFormat  = Math.floor( selectedMinValue/1000);
									if(minFormat> 0){ minFormat= minFormat + "k"; } 
									$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(selectedMaxValue/1000) + "k"  );		
								});
							}
							// re-create
							
							if($( "#year-slider-rs" ).length ) {
								year_slider_rs = create_slider(<?php echo $yearMin;?>, <?php echo $yearMax;?>, $( "#yMiRS" ).val(),$( "#yMaRS" ).val(), document.getElementById("year-slider-rs"), yearStep); 
								year_slider_rs.noUiSlider.on('update', function( values, handle ) {
									$( "#yMiRS" ).val(Math.round(values[0]));
									$( "#yMaRS" ).val(Math.round(values[1]));
									$("#yearRangeAmountRS" ).text(Math.round(values[ 0 ]) + " - " + Math.round(values[ 1 ]));
									
								});	
							}
							
							$("input:checkbox.refine-search[name='sTy[]']").each(function(){
								$(this).prop('checked', false);				
							});
							$("#typeID-1").prop('checked', true);
							
						});//end of done
					}//end of search new demo cars
				}//end of is checked
			}//end of type id 1
			if($( this ).attr( "id" )=="typeID-2"){
				if($(this).is(':checked')){
					$("input:checkbox.refine-search[name='sTy[]']").each(function(){
							$(this).prop('checked', false);				
					});
					$(this).prop('checked', true);
					console.log("demo");
					//$("#refineSearch")[0].reset();
					$( "input[name='sNu']" ).val('');
					$( "input[name='sRe']" ).val('');	
					$(".chzn-select").val('').trigger("chosen:updated");
					$("input:checkbox.refine-search[name='sTy[]']").each(function(){
						$(this).prop('checked', false);				
					});
					$(this).prop('checked', true);
					var formData = $("#refineSearch").serialize();
					$.ajax({
						type: "GET",
						url: "/search/refine-search-add-vehicle-makes.ajax.php",
						data: formData,
					})
					
					.done(function(html) {
						$("#refineSearchFields").html(html);
						$("#refineSearch .chzn-select").each(function(index, value){
							$(this).chosen({allow_single_deselect: true});
						})	
						var advFlag = $("#refineSearch #advFlag").val();
						if(advFlag == 1) {
							$("#refineSearch .advSearchFieldsWrapper").css('display',"block");
							$("#refineSearch .advSearchFields legend").addClass("active");
						}
						
						if($( "#price-slider-rs" ).length ) {
							price_slider_rs = create_price_range_slider(<?php echo $priceMin;?>, 150000, $( "#pMiRS2" ).val(),$( "#pMaRS2" ).val(), document.getElementById("price-slider-rs"), priceStep);
							
							//positioning of the left and right handles
							/*$(".noUi-handle.noUi-handle-lower").css({
								"left": "-20px"
							});
							$(".noUi-handle.noUi-handle-upper").css({
								"left": "-4px"
							});*/
							
							// update on change 
							price_slider_rs.noUiSlider.on('update', function( values, handle ) {
								var selectedMinValue = values[0].replace('$','').replace(',','');
								var selectedMaxValue = values[1].replace('$','').replace(',','');
								
								//range increments by 10000 after a value of 100000
								if((selectedMinValue <= 150000 && selectedMinValue > 100000)){
									console.log(selectedMinValue);
									selectedMinValue = 100000 + (selectedMinValue - 100000) * 2;
								}
								if((selectedMaxValue <= 150000 && selectedMaxValue > 100000)){
									console.log(selectedMaxValue);
									selectedMaxValue = 100000 + (selectedMaxValue - 100000) * 2;
								}
								
								$( "#pMiRS" ).val(Math.round(selectedMinValue));
								$( "#pMaRS" ).val(Math.round(selectedMaxValue));
								var minFormat  = Math.floor( selectedMinValue/1000);
								if(minFormat> 0){ minFormat= minFormat + "k"; } 
								$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(selectedMaxValue/1000) + "k"  );		
							});
						}
						// re-create
						
						if($( "#year-slider-rs" ).length ) {
							year_slider_rs = create_slider(<?php echo $yearMin;?>, <?php echo $yearMax;?>, $( "#yMiRS" ).val(),$( "#yMaRS" ).val(), document.getElementById("year-slider-rs"), yearStep); 
							year_slider_rs.noUiSlider.on('update', function( values, handle ) {
								$( "#yMiRS" ).val(Math.round(values[0]));
								$( "#yMaRS" ).val(Math.round(values[1]));
								$("#yearRangeAmountRS" ).text(Math.round(values[ 0 ]) + " - " + Math.round(values[ 1 ]));
								
							});	
						}
						
						$("input:checkbox.refine-search[name='sTy[]']").each(function(){
							$(this).prop('checked', false);				
						});
						$("#typeID-2").prop('checked', true);
						
					});//end of done
				}//end of is checked
					
			}//end of type id 2
			if($( this ).attr( "id" )=="typeID-3"){
				if($(this).is(':checked')){
					$("input:checkbox.refine-search[name='sTy[]']").each(function(){
							$(this).prop('checked', false);				
					});
					$(this).prop('checked', true);
					console.log("new");
					//$("#refineSearch")[0].reset();
					$( "input[name='sNu']" ).val('');
					$( "input[name='sRe']" ).val('');	
					$(".chzn-select").val('').trigger("chosen:updated");
					$("input:checkbox.refine-search[name='sTy[]']").each(function(){
						$(this).prop('checked', false);				
					});
					$(this).prop('checked', true);
					var formData = $("#refineSearch").serialize();
					$.ajax({
						type: "GET",
						url: "/search/refine-search-add-vehicle-makes.ajax.php",
						data: formData,
					})
					
					.done(function(html) {
						$("#refineSearchFields").html(html);
						$("#refineSearch .chzn-select").each(function(index, value){
							$(this).chosen({allow_single_deselect: true});
						})	
						var advFlag = $("#refineSearch #advFlag").val();
						if(advFlag == 1) {
							$("#refineSearch .advSearchFieldsWrapper").css('display',"block");
							$("#refineSearch .advSearchFields legend").addClass("active");
						}
						
						if($( "#price-slider-rs" ).length ) {
							price_slider_rs = create_price_range_slider(<?php echo $priceMin;?>, 150000, $( "#pMiRS2" ).val(),$( "#pMaRS2" ).val(), document.getElementById("price-slider-rs"), priceStep);
							
							//positioning of the left and right handles
							/*$(".noUi-handle.noUi-handle-lower").css({
								"left": "-20px"
							});
							$(".noUi-handle.noUi-handle-upper").css({
								"left": "-4px"
							});*/
							
							// update on change 
							price_slider_rs.noUiSlider.on('update', function( values, handle ) {
								var selectedMinValue = values[0].replace('$','').replace(',','');
								var selectedMaxValue = values[1].replace('$','').replace(',','');
								
								//range increments by 10000 after a value of 100000
								if((selectedMinValue <= 150000 && selectedMinValue > 100000)){
									console.log(selectedMinValue);
									selectedMinValue = 100000 + (selectedMinValue - 100000) * 2;
								}
								if((selectedMaxValue <= 150000 && selectedMaxValue > 100000)){
									console.log(selectedMaxValue);
									selectedMaxValue = 100000 + (selectedMaxValue - 100000) * 2;
								}
								
								$( "#pMiRS" ).val(Math.round(selectedMinValue));
								$( "#pMaRS" ).val(Math.round(selectedMaxValue));
								var minFormat  = Math.floor( selectedMinValue/1000);
								if(minFormat> 0){ minFormat= minFormat + "k"; } 
								$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(selectedMaxValue/1000) + "k"  );		
							});
						}
						// re-create
						
						if($( "#year-slider-rs" ).length ) {
							year_slider_rs = create_slider(<?php echo $yearMin;?>, <?php echo $yearMax;?>, $( "#yMiRS" ).val(),$( "#yMaRS" ).val(), document.getElementById("year-slider-rs"), yearStep); 
							year_slider_rs.noUiSlider.on('update', function( values, handle ) {
								$( "#yMiRS" ).val(Math.round(values[0]));
								$( "#yMaRS" ).val(Math.round(values[1]));
								$("#yearRangeAmountRS" ).text(Math.round(values[ 0 ]) + " - " + Math.round(values[ 1 ]));
								
							});	
						}
						
						$("input:checkbox.refine-search[name='sTy[]']").each(function(){
							$(this).prop('checked', false);				
						});
						$("#typeID-3").prop('checked', true);
						
					});//end of done
				}//end of is checked
					
			}//end of type id 3
			if($( this ).attr( "id" )=="typeID-0")
			{
				if($(this).is(':checked'))
				{
					$("input:checkbox.refine-search[name='sTy[]']").each(function(){
							$(this).prop('checked', false);				
					});
					$(this).prop('checked', true);	
					console.log("all");
				}	
			} else 
			{
				$("#typeID-0").prop('checked', false);		
			}
		}else{
			//for normal jarvis site
			if($( this ).attr( "id" )=="typeID-0")
			{
				if($(this).is(':checked'))
				{
					$("input:checkbox.refine-search[name='sTy[]']").each(function(){
							$(this).prop('checked', false);				
					});
					$(this).prop('checked', true);	
				}	
			} else 
			{
				$("#typeID-0").prop('checked', false);		
			}	
		}
	});
	
	$("input:checkbox.advance-search[name='sTy[]']").change(function() {
		
		if($( this ).attr( "id" )=="typeID-adv0")
		{
			if($(this).is(':checked'))
			{
				$("input:checkbox.advance-search[name='sTy[]']").each(function(){
						$(this).prop('checked', false);				
				});
				$(this).prop('checked', true);	
			}	
		} else 
		{
			$("#typeID-adv0").prop('checked', false);		
		}
		
	});
	
	//begin to track the number of clicks for reinput affordability calculator button
	$('.reinput-affordability-calculator').on('click',this,function(e) {
		if( $(".panel .body").is(':hidden')){
			console.log('reinmput');
			dataLayer.push({
				'eventCategory' : 'Site Actions',
				'eventAction' : 'Affordability Calculator - SRP Reinput'
			});
		}
	});
	//end to track the number of clicks for reinput affordability calculator button
	
	//begin to track the number of clicks for remove affordability search button
	$('.remove-budget-search').on('click',this,function(e) {
		dataLayer.push({
			'eventCategory' : 'Site Actions',
			'eventAction' : 'Affordability Calculator - SRP Remove'
		});
		var host = window.location.host;
		window.location.href = 'http://' + host + '/search/all-cars/';
	});
	//end to track the number of clicks for remove affordability search button
	
	$('#sNu').change(function () {
		dataLayer.push ({ 
			'event': 'gaTriggerEvent', 
			'gaEventCategory': 'Site Actions', 
			'gaEventAction': 'Rego or Stock Search Field', 
			'gaEventLabel': 'Advanced Search Form' 
		}); 
	});	
	
	//var mess_content = $('.blurb');
	//mess_content.each(function(){
		//console.log($(this).find('input#commentvID').val());
		//var vID_value=$(this).find('input#commentvID').val();
		//$( $(this).find('p') ).after( "<a style='text-decoration: none;' href='"+vID_value+"'>Read more...</a>" );
		//$( ".blurb p" ).after( "<a target='_blank' href='details-enquire-test.php?vID=28351#vehicleDetails'>read more</a>" );
	//});
	
	//truncate badge if the car title is longer than 40 characters (mobile only)
	var newWindowWidth = $(window).width();
	if (newWindowWidth <= 380) {
		console.log('mobile');
		var carTitle = $(".car-title");
		carTitle.each(function(){
			var carTitleText = $(this).text();
			var carTitleLength = $(this).text().length;
			var carBadge = $(this).find('.car-badge').text();
			//console.log(carTitleText);
			//console.log(carTitleLength);
			//console.log(carBadge);
			if(carTitleLength>=40){
				$(this).find('.car-badge').text('...');
			}
		});
	}
	$(window).on("resize", function (e) {
        var newWindowWidth = $(window).width();
		if (newWindowWidth <= 380) {
			console.log('mobile');
			var carTitle = $(".car-title");
			carTitle.each(function(){
				var carTitleText = $(this).text();
				var carTitleLength = $(this).text().length;
				var carBadge = $(this).find('.car-badge').text();
				//console.log(carTitleText);
				//console.log(carTitleLength);
				//console.log(carBadge);
				if(carTitleLength>=40){
					$(this).find('.car-badge').text('...');
				}
			});
		}
    });
	
}); <!--end document reaady -->


</script>
<script type="text/javascript">
function searchCars(){
	$("#refineSearch").submit();
	dataLayer.push({
		'eventCategory' : ' Site Actions',
		'eventAction' : 'SRP - Search'
	});
}
function searchCarsWithMoreOptions(){
	$("#refineSearch").submit();
	dataLayer.push({
		'eventCategory' : ' Site Actions',
		'eventAction' : 'SRP - Search With More Options'
	});
}
	
$('a[href^="#"]').on('click', function(event) {
    var target = $(this.getAttribute('href'));
    if( target.length ) {
        event.preventDefault();
        $('html, body').stop().animate({
            scrollTop: target.offset().top
        }, 1000);
    }
});	
	
	
	
	
	
</script>
<?php 
include("pages/finance/affordability-calculator/calculator.inc-budget-js.php");
include('end.inc.php'); ?>
