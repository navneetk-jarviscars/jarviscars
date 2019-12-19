<?php
include('defaults.inc.php'); 
$kill=0;
/**
 * Start of Page Customisation
 */
if(isset($_REQUEST['vID'])) {
	$inventoryID = $_REQUEST['vID'];
} 
else {
	$path = explode("/",$_SERVER['REQUEST_URI']);
	if(count($path) > 1) {
		$path = array_filter($path);
		$discard = array_pop($path);
		$path = "/".implode("/",$path)."/";
	} 
	else {
		$path = "/search/";
		 }
	header("Location:".$path);
	exit();
};

$thumbs=array();
$imageList=null;
if(isset($_GET['regenThumbs'])) {
	// GET STOCK NUMBER FOR INVENTORYID
	$stockNumber = get_value($pdo,"autogate_inventory","stockNumber",array("inventoryID"=>$inventoryID));
	// GET LIST OF IMAGES FOR THE STOCK NUMBER
	$path = "/home/jarvisca/public_html/www.jarviscars.com.au/html/_cache/";
	$imageList = glob($path.$stockNumber."*");
	foreach($imageList as $k => $image) {
		$filename = substr($image,strlen($path));
		$thumbs[]=$filename;
		if(substr_count($filename, '_') > 1) 
			{
//			$thumbs[] = $path.$filename;
			
			unlink($path.$filename);
			}
	}
//	dump($thumbs,0);
// DELETE ALL BUT ORIGINALS
}


/*
function remove_cache_file($as_file)
{
	$path="/cust-web/a/j/A2088823/sites/www.jarviscars.com.au/html/_cache/";
	$file_full_path= $path.$as_file;
	if (file_exists($file_full_path)) {
		unlink($file_full_path);
	}
	
}
// try to remove file from catche

remove_cache_file("SG29764_1_320_.jpg");

echo('<div style="display:none" class="testing">');
var_dump($imageList);
echo('</div>');

*/

if(isset($_SESSION['user']['root']['userID'])) 
	{ $userID = $_SESSION['user']['root']['userID']; 
	} 
else { $userID = NULL; }
if(isset($_SERVER['HTTP_REFERER'])) 
	{ $referrer = $_SERVER['HTTP_REFERER']; 
	} 
else { $referrer = NULL; }
$stat = log_search_traffic($pdo,$_SERVER['REMOTE_ADDR'],$_SERVER['REQUEST_URI'],$referrer,$userID);
$stat = log_inventory_stats($pdo,$inventoryID,4,$userSourceID,$_SERVER['REMOTE_ADDR'],$userID);
$params = array(":inventoryID" => $inventoryID);
$sth = $pdo->prepare("SELECT COUNT(`inventoryID`) AS `count` FROM `autogate_inventory` JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `inventoryStatus` = 1 AND `inventoryID` = :inventoryID");
/************
$sth->execute($params);
*************/
$sth->execute($params);

$count = $sth->fetch(PDO::FETCH_COLUMN);
$sth = NULL;

if($count == 0 && (!isset($_REQUEST['jAdmin']))) 
	{
	
	// if this is sold car 
	$sth = $pdo->prepare("SELECT COUNT(`inventoryID`) AS `count` FROM `autogate_inventory` JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `inventoryStatus` = 0 AND `inventoryID` = :inventoryID");
    $sth->execute($params);

	$countSold = $sth->fetch(PDO::FETCH_COLUMN);
	
	$sth = NULL;
	if($countSold > 0) 
		{
		$path = "https://www.jarviscars.com.au/search/index.php";
		}
	else 
		{
		$path = explode("/",$_SERVER['REQUEST_URI']);
		if(count($path) > 1) 
			{
			$path = array_filter($path);
			$discard = array_pop($path);
			$path = "/".implode("/",$path)."/";
			}
		}
	
	//error_reporting(E_ALL);
	//ini_set('display_errors', TRUE);
	//ob_start();
	
	header('location:/search/');
	//ob_end_flush();
	exit(0);	
	}

$weekly_specials= 0;

$car = array();

$sql = "SELECT * FROM `autogate_inventory` JOIN r_vehicles ON r_vehicles.redbookCode = autogate_inventory.redbookCode WHERE `inventoryID` = :inventoryID";




//code added on 7/11/2019 after dos attack
$sth = $pdo->prepare($sql);

$sth->bindParam(1, $row['makeID'], PDO::PARAM_INT);
$sth->bindParam(2, $row['modelID'], PDO::PARAM_INT);
$sth->bindParam(3, $row['seriesID'], PDO::PARAM_INT);
$sth->bindParam(4, $row['badgeID'], PDO::PARAM_INT);
$sth->bindParam(5, $row['bodyID'], PDO::PARAM_INT);
$sth->bindParam(6, $row['typeID'], PDO::PARAM_INT);
$sth->bindParam(7, $row['transmissionID'], PDO::PARAM_INT);
$sth->bindParam(8, $row['colourID'], PDO::PARAM_INT);
$sth->bindParam(9, $row['driveID'], PDO::PARAM_INT);
$sth->bindParam(10, $row['fuelID'], PDO::PARAM_INT);
$sth->bindParam(11, $row['inductionID'], PDO::PARAM_INT);

$sth->execute($params);

while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
	{
	$car = $row;
	$car['typeName'] = get_value($pdo,"autogate_types","typeName",array("typeID"=>$row['typeID']));
	$car['makeName'] = get_value($pdo,"r_makes","description",array("makeID"=>$row['makeID']));
	/* CHANGED SELECT TO `modelName` INSTEAD OF `modelNameDisplay` */
	$car['modelName'] = get_value($pdo,"r_models","description",array("modelID"=>$row['modelID']));
	$car['seriesName'] = get_value($pdo,"r_series","name",array("seriesID"=>$row['seriesID']));
	//$car['badgeName'] = get_value($pdo,"r_badge","name",array("badgeID"=>$row['badgeID']));
	$primaryBadgeName = get_value($pdo,"r_badge","name",array("badgeID"=>$row['badgeID']));
	$secondaryBadgeName = get_value($pdo,"r_badge","badge2nd",array("badgeID"=>$row['badgeID']));
	$car['badgeName'] = $primaryBadgeName.' '.$secondaryBadgeName;
	$car['bodyName'] = get_value($pdo,"r_bodystyle","name",array("bodystyleID"=>$row['bodyID']));
	$car['transmissionName'] = get_value($pdo,"r_transmission","nameDisplay",array("transmissionID"=>$row['transmissionID']));
	$car['colourName'] = get_value($pdo,"autogate_colours","colourName",array("colourID"=>$row['colourID']));
	if(empty($car['colourName'])) 
		{
		$car['colourName'] = $row['genericColour'];
		}
	
	$car['drivelineName'] = get_value($pdo,"r_drive","name",array("driveID"=>$row['driveID']));
	$car['fuelName'] = get_value($pdo,"r_fuel","name",array("fuelID"=>$row['fuelID']));
	$car['inductionName'] = get_value($pdo,"r_inductions","name",array("inductionID"=>$row['inductionID']));
	//$car['retailPrice']=get_value($pdo,"autogate_inventory","retailPrice",array("retailPrice"=>$row['retailPrice']));
	
	$car['features'] = array();
	/*$sql2 = "SELECT `featureName` FROM `v_features` INNER JOIN `v_inventory_features` ON `v_features`.`featureID` = `v_inventory_features`.`featureID` WHERE `inventoryID` = :inventoryID ORDER BY `featureName` ASC";
	$sth2 = $pdo->prepare($sql2);
	$sth2->execute($params);
	while($row2 = $sth2->fetch(PDO::FETCH_ASSOC)) {
		$car['features'][] = $row2['featureName'];
	}*/
	$sql2 = "SELECT `r_standards`.`description` 
			FROM `r_standards` 
			INNER JOIN `r_vehicle_standards` ON `r_standards`.`standardID` = `r_vehicle_standards`.`standardID` 
			INNER JOIN `r_vehicles` ON `r_vehicle_standards`.`rbID` = `r_vehicles`.`rbID`
			INNER JOIN `autogate_inventory` ON r_vehicles.redbookCode = autogate_inventory.redbookCode 
			WHERE `inventoryID` = :inventoryID 
			ORDER BY `r_standards`.`description` ASC";
	$sth2 = $pdo->prepare($sql2);
	$sth2->execute($params);
	while($row2 = $sth2->fetch(PDO::FETCH_ASSOC)) 
		{
		$car['features'][] = $row2['description'];
		}
	$sth2 = NULL;
	
	$car['images'] = array();
	if($row['photoCount'] > 0) 
		{
		$sql2 = "SELECT `imageName` FROM `autogate_inventory_photos` WHERE `inventoryID` = :inventoryID ORDER BY `imageOrder` ASC";
		$sth2 = $pdo->prepare($sql2);
		$sth2->execute($params);
		while($row2 = $sth2->fetch(PDO::FETCH_ASSOC)) 
			{
			$car['images'][] = $row2['imageName'];
			}
		$sth2 = NULL;
		$pageHeroImage = "//jarviscars.com.au/_cache/".$car['images'][0];
		$pageHeroImageClass = "";
		//$pageHeroImage = CDN_URL."content-img/banner-usedCars.jpg";
		} 
	else 
		{
		$car['images'][] = "_no-image.gif";
		$pageHeroImage = CDN_URL."content-img/banner-usedCars.jpg";
		}

	// check is this a weekly specials or not
	$weekly_specials = is_weekly_specials($inventoryID);	
}

$sth = NULL;


// get the first image 
if(isset($car['images'][0]))
	{
	//$thumb_n= str_replace  (".jpg", "_320.jpg",  $car['images'][0]);
	$fbShareImg =  "http://".$_SERVER['HTTP_HOST']."/_cache/".$car['images'][0];
	}
else 
	{
	$fbShareImg = "http://".$_SERVER['HTTP_HOST']."/_cache/"."_no-image.gif"; 	
	}

// DETERMINE PRICE TO USE
/*if($car['isDriveAway']) {
	$priceNotation = " <span class=\"notation\">Drive Away</span>";
	$priceNotation_1 = " <span class=\"notation\">Drive Away</span>";

} else {
	$priceNotation = "<span class=\"notation sup-txt\">*</span>";
	$priceNotation_1 = "<span class=\"notation\">*</span>";
}*/

$loanAmount = $car['retailPrice'];

//autogate_inventory
$vehiclePrice = "";
if($car['igcPrice'] > '0.00')
	{
	//drive away
	$priceNotation = " <span class=\"notation\">Drive Away</span>";
	$priceNotation_1 = " <span class=\"notation\">Drive Away</span>";
	$vehiclePrice = $car['igcPrice'];
	}
else if($car['egcPrice'] > '0.00')
	{
		$priceNotation = "<span class=\"notation sup-txt\">*</span>";
		$priceNotation_1 = "<span class=\"notation\">*</span>";
		$vehiclePrice = $car['egcPrice'];
		}
else
	{
		$priceNotation = "";
		$priceNotation_1 = "";
		}

if(!empty($priceDisplay)) 
	{
	$loanAmount = $priceDisplay;
	$priceDisplay = "$".number_format($priceDisplay,0);
	} 
else 
	{
	$priceDisplay = "POA";
	}

$dataLayer = array();
$dataLayer['dealerName'] = $site['siteName'];
$dataLayer['pageName'] = "vehicleDetailPage";
$dataLayer['vehicleFranchise'] = $car['makeName'];
$dataLayer['vehicleModel'] = $car['modelName'];
$dataLayer['vehicleGrade'] = $car['badgeName'];
$dataLayer['vin'] = $car['vin'];

//$dataLayer['formName'] = 'Individual Vehicle Enquiry Used';
//$dataLayer['formStatus'] = 'viewed'; // OR 'submitted'
//$dataLayer['event'] = 'toyotaFormViewed'; // OR 'toyotaFormSubmitted'
//$dataLayer['eventCategory'] = 'siteActions';
//$datalayer['eventAction'] = 'Tracked Number Call'; // OR 'Click to Call'

/**
 * Set required page attributes here
 * REQUIRED
 */
$schema['title'] = $car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName']." | ".$car['inventoryID']." | Jarvis | Adelaide, South Australia";
$schema['meta']['description'] = "Buy the ".$car['year']." ".$car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName']." ".$car['bodyName']." from Jarvis";
$schema['meta']['keywords'] = $car['year'].",".$car['makeName'].",".$car['modelName'].",".$car['seriesName'].",".$car['badgeName'].",".$car['bodyName'];
$schema['meta']['og']['image']= $fbShareImg;
$schema['meta']['image']= $fbShareImg;
/**
 * Set custom Schema values here - Refer schema-markup.inc.php
 * OPTIONAL
 */



/**
 * Start Form Handling Here
 */
 
/**
 * Array of form fields
 * 
 * The value of each $formFields element should:
 * - start with a letter and not contain spaces or special characters
 * - contain a prefix and underscore separator ("prefix_")
 * - be the value of the 'name' attribute (<input type="text" name="prefix_email" value="<?php echo ($formData['prefix_email']); ?>" />)
 * The generated email will use the text after the first underscore as the data label in the email (Email: johndoe@emailaddress.com)
 * 
 * @param array $formFields Array containing name attribute values for all form fields
 */
$formFields = array();
$formFields[] = "enquireThisCarMobile_name";
$formFields[] = "enquireThisCarMobile_email";
$formFields[] = "enquireThisCarMobile_phone";
$formFields[] = "enquireThisCarMobile_comments";
$formFields[] = "enquireThisCarMobile_contact";
$formFields[] = "car_name";
$formFields[] = "car_phone";
$formFields[] = "car_email";
$formFields[] = "car_comments";
$formFields[] = "car_vID";
$formFields[] = "car_url";
if ($site['siteID']=='4')
	{
	$formFields[] = "buy_Marketing-OptIn";
	}





$formFileFields = array();


// test drive form
$testDriveFormFields = array();
$testDriveFormFields[] = "testdriver_name";
$testDriveFormFields[] = "testdriver_phone";
$testDriveFormFields[] = "testdriver_email";
$testDriveFormFields[] = "testdriver_comments";
$testDriveFormFields[] = "testdrive_vID";
$testDriveFormFields[] = "testdrivecar_url";


// enquire this car form mobile
$enquireThisCarFormMobileFields = array();
$enquireThisCarFormMobileFields[] = "enquireThisCarMobile_name";
$enquireThisCarFormMobileFields[] = "enquireThisCarMobile_email";
$enquireThisCarFormMobileFields[] = "enquireThisCarMobile_phone";
$enquireThisCarFormMobileFields[] = "enquireThisCarMobile_comments";
$enquireThisCarFormMobileFields[] = "enquireThisCarMobile_contact";



// enquire this car form
$enquireThisCarFormFields = array();
$enquireThisCarFormFields[] = "enquireThisCar_name";
$enquireThisCarFormFields[] = "enquireThisCar_email";
$enquireThisCarFormFields[] = "enquireThisCar_phone";
$enquireThisCarFormFields[] = "enquireThisCar_comments";
$enquireThisCarFormFields[] = "enquireThisCar_vID";
$enquireThisCarFormFields[] = "enquireThisCar_url";









//--implement 19/09/2019 START--/
//functionality for finance enquiry form
$financeFormFields = array();
//$financeFormFields[] = "finance_contactEmail";
//$financeFormFields[] ="finance_contactSMS";
//$financeFormFields[] = "finance_contactCall";
$financeFormFields[] = "finance_contactVia";
$financeFormFields[] = "finance_name";
$financeFormFields[] = "finance_phone";
$financeFormFields[] = "finance_email";
$financeFormFields[] = "finance_comments";
$financeFormFields[] = "finance_vID";
$financeFormFields[] = "finance_url";
$financeFormFields[] = "finance_salePrice";
$financeFormFields[] = "finance_periodPaymentLowest";
$financeFormFields[] = "finance_periodPaymentHighest";
$financeFormFields[] = "finance_deposit";
//--implement 19/09/2019 STOP--/


//enquiry type 
$etype="vehicle";

//check availability flag
$enquire_checkAvailabilityFlag=0;

$formFileFields = array();
/**
 * Array of form field data
 * 
 * Each $formFields element is checked for incoming data on form submission and if not set, the $formData[fieldName] is set to NULL
 * Each $formData[fieldName] is used to pre-fill the form if data is passed via the URL
 * ?prefix_email=johndoe@emailaddress.com
 * 
 * @param array $formData Array containing values for all form fields
 */
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

$testDriveFormData = array();
foreach($testDriveFormFields as $k => $v) 
	{
	if(isset($_REQUEST[$v])) 
		{ 
		$testDriveFormData[$v] = $_REQUEST[$v]; 
		} 
	else 
		{ 
		$testDriveFormData[$v] = NULL; 
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



//mobile
$enquireThisCarMobileFormData = array();
foreach($enquireThisCarFormMobileFields as $k => $v) 
	{
	if(isset($_REQUEST[$v])) 
		{ 
		$enquireThisCarMobileFormData[$v] = $_REQUEST[$v]; 
		} 
	else 
		{ 
		$enquireThisCarMobileFormData[$v] = NULL; 
		}
	}

//--implement 19/09/2019 START--/
$financeFormData = array();
foreach($financeFormFields as $k => $v) 
	{
	if(isset($_REQUEST[$v])) 
		{ 
		$financeFormData[$v] = $_REQUEST[$v]; 
		} 
	else 
		{ 
		$financeFormData[$v] = NULL; 
		}
	}
//--implement 19/09/2019 END--/


/**
 * Process the form if it was submitted
 * The submit button should have a name attribute with a value of "submit"
 * <input type="submit" name="submit" value="Send" class="btn btn-send" />
 */
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
	$emailTo = array("info@jarviscars.com.au" => "Jarvis");
		
	$emailCc = array();
	$emailBcc = array("webmaster@jarviscars.com.au");
	
	
	
	if($etype=="FinanceEnquiry"){
		$emailCc = array('christianh@jarviscars.com.au,stuartf@jarviscars.com.au');
		
	}
	
	/**
	 * Subject line of the email
	 */
	
	$siteName=""; 
	if($site['siteID']> 0)
		{
	  	$siteName="Jarvis Website";
		}
	 
	$emailSubject = $siteName. " | ". $car['typeName']." ". $car['makeName']." ".$car['modelName']." ".$car['bodyName']." Enquiry | ".$car['stockNumber'];
	/**
	 * Priority of the email
	 * 1 = Highest
	 * 2 = High
	 * 3 = Normal
	 * 4 = Low
	 * 5 = Lowest
	 */
	$emailPriority = 3;
	/**
	 * Custom email headers
	 * @param array $emailCustomHeaders Array containing any number of key/value pairs for inclusion as plain text headers to the email message
	 * array("NPSID" => "12345", "CID" => "98765");
	 */
	$emailCustomHeaders = array();
	
	/***** now catering for Testing drive bookings as well  */
	//check for enquiry type default to vehicle. 
	
	if(isset($_REQUEST['enquire_type']))
		{
		$etype=	$_REQUEST['enquire_type'];
		}
	
	//check for the availability flag
	if(isset($_REQUEST['enquire_checkAvailabilityFlag']))
		{
		$enquire_checkAvailabilityFlag=	$_REQUEST['enquire_checkAvailabilityFlag'];
		}
	
	//form data is used in enquiry-processor.inc.php
    $copyFormData = $formData;
	
	if($etype=="testdrive") 
		{
		$emailAddressField = "testdriver_email";
		$nameField = "testdriver_name";
		$emailSubject = $car['makeName']." ".$car['modelName']." ".$car['bodyName']." Test Drive Enquiry | ".$car['stockNumber'];
		// send the test drive form in 
		$formData = $testDriveFormData;
		}
	else if($etype=="enquireThisCar")
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
	
	//mobile
	else if($etype=="enquireThisCarMobile")
		{
		$emailAddressField = "enquireThisCarMobile_email";
		$nameField = "enquireThisCarMobile_name";
		$emailSubject = $siteName. " | ". $car['typeName']." ". $car['makeName']." ".$car['modelName']." ".$car['bodyName']." Enquiry | ".$car['stockNumber'];
		//check availability flag
		if($enquire_checkAvailabilityFlag=='1')
			{
			$emailSubject = $siteName. " | ". $car['typeName']." ". $car['makeName']." ".$car['modelName']." ".$car['bodyName']." Check Availability Enquiry | ".$car['stockNumber'];
			}
		// send the test drive form in 
		$formData = $enquireThisCarMobileFormData;
 		}
	
	
	
	
	
	
	//--implement 19/09/2019 START--/
	
	else if($etype=="FinanceEnquiry")
		{
		$emailAddressField = "finance_name";
		$nameField = "finance_email";	
		$eventLabel = "Finance Enquiry"." ".$car['typeName'];
		$emailSubject = $car['makeName']." ".$car['modelName']." ".$car['bodyName']." Finance Enquiry | ".$car['stockNumber'];
		$formData = $financeFormData;
		}
	
	//--implement  19/09/2019 END--/
	
	else
		{
		//do nothing
		}
	
	include("enquiry-processor.inc.php");
	
	$formData =  $copyFormData;
	
	/**
	 * enquiry-processor.inc.php
	 * Populates $emailResult on send. 
	 * Used to determine content to show in body
	 */
}
/**
 * End Form Handling Here
 */

$testDriveFormCarType = "Used";
if($car['typeName']=="Demo"){ 
	$testDriveFormCarType = "Demo";
}else if($car['typeName']=="New")
	{
	$testDriveFormCarType = "New";
	}
else if($car['typeName']=="Used")
	{
	$testDriveFormCarType = "Used";
	}
else 
	{
	$testDriveFormCarType = "Used";
	}

//to check if the visitor clicked on the enquire button in the search results page
$enquireBtnClicked = 0;
if(isset($_GET['enquireBtnClicked'])) 
	{
	$enquireBtnClicked = $_GET['enquireBtnClicked'];
	}

//finance calculator
$repayment_estimator_hidden = 0;
if(isset($_REQUEST['repayment_estimator_hidden'])) 
	{
	$repayment_estimator_hidden = $_REQUEST['repayment_estimator_hidden'];
	}

//check availability flag
$enquireFormHeading = "Enquire on this car";
$enquireFormButton = "Send Enquiry";
if(isset($_SESSION["checkAvailabilityFlag"])) 
	{
	if($_SESSION["checkAvailabilityFlag"]=='1')
		{
		$enquireFormHeading = "Check availability";
		$enquireFormButton = "Send";
		}
	}
if(isset($_POST['submit'])) 
	{
	if($enquire_checkAvailabilityFlag=='0')
		{
		$enquireFormHeading = "Enquire on this car";
		$enquireFormButton = "Send Enquiry";
		}
	else if($enquire_checkAvailabilityFlag=='1')
		{
		$enquireFormHeading = "Check availability";
		$enquireFormButton = "Send";
		}
	else{
		//do nothing
		}
	}

/**
 * End of Page Customisation
 */
include('start.inc.php');

?>

<link rel="stylesheet"  href="<?php echo(CDN_URL); ?>css/noUiSlider/nouislider.min.css" >
<!-- Justselect Styl;ing -->
<link rel="stylesheet"  href="<?php echo(CDN_URL); ?>css/Justselect/selectbox.min.css" >
<!-- Justselect Styl;ing -->
<?php
include('head.inc.php');
?>
<div class="page-body">

<!-- style for prind PDF link -->
<style type="text/css">
		a.tab-title
			{
			padding: 16px 30px 16px 30px !important; 
			text-decoration:none !important;	
		}
		a.tab-title .icon
			{
			margin-right :5px;			
			}	
		#sendFriendForm  .col
			{
			border-left:none !important;
		}
		.mobile-only a
			{
		 	text-decoration: none;
		}
		/*--- testing our weekly speicials-- */
		.weekly-special
			{
		 	background: #C00;
		}
		.weekly-special h2, .car-detail-page .featured-grad h2 
			{
		  	padding-top: 15px;
			padding-bottom: 15px;
			color: #fff;
		  	margin-bottom: 0;
		  	font-size: 21px;
			}
		.action-btn
			{			
			background-color: #090!important;
			color: #FFF!important;
			}
		.action-btn:hover
			{
    		background-color: #070!important; 
			}
		.car-sidepanel .cta-buttons li:last-child a 
			{
			color: #000;
			background-color: #e1e1e1;
			padding-left:50px;
			}
		.car-sidepanel .cta-buttons li:last-child a:hover 
			{
      		 /*background-color: #d1d1d1;*/
			}
		/*slide images*/
		.vehicleDetail .carousel.flexslider li:nth-child(4n+4)
			{
			margin-right:0px !important;
			width:169px !important;
			margin-top:1px !important;
			}
		.vehicleDetail .carousel.flexslider li 
			{
			margin-top:1px !important;
			}
		/*testimonials section*/
		@media (max-width: 960px) 
			{
			.testimonials 
				{
				padding:0 10px !important;
				}
			}
		.testimonials 
			{
			padding:0 20px;
			}
		/*VDP new style*/
		.box-shadow.gradient-bg
			{
			background:#FFFFFF !important;
			}
		.checkbox-container span.checkbox::before
			{
			box-shadow:none;
			}
		.chosen-container-multi .chosen-choices
			{
			box-shadow:none;
			}
		.basicSearchFields .btn-fullwidth
			{
			width:90%;
			}
		.success 
			{
			background-color: #83e195;
			border: 1px solid green;
			color: black;
			border-radius: 0px;
			}
		.left-column{
			text-align: left;
			}
		.centre-column{
			text-align: center;
			}
		.right-column{
			text-align: right;
			}
		@media (max-width: 620px) 
			{
				.aside.column.skinny
					{
					padding-left: 20px !important;
					padding-right: 20px !important;
					}
				.car-detail-page .aside 
					{
					margin-top: -20px !important;
					}
				.left-column, .centre-column, .right-column{
					text-align: center;
					}
			}
		/* ipad Portrait */
		@media screen  and (device-width: 768px)  and (device-height: 1024px) and (orientation: portrait) 
			{
				.aside.column.skinny
					{
					padding-left: 50px !important;
					padding-right: 50px !important;
					}
				.car-detail-page .aside 
					{
					margin-top: -20px !important;
					}  
				.column 
					{
					float: none
					}
				.column.main, .column.aside, .column.full-width 
					{
					width: 100%;
					padding: 20px 0
					}
				.column.aside 
					{
					padding: 20px 10px
					}
				box-content.closed.car-details
					{
					margin-left: 10px !important;
					margin-right: 10px !important;
					}
				.car-sidepanel
					{
					padding: 0px !important;
					border: 0px !important;
					}
				.box.box-tabs.tabs-rounded
					{
					display: none;
					}
				.box.box-tabs.tab-car-actions 
					{
					display: none;
					}
				.refineSearchForm
					{
					padding: 0px !important; 
					}
				.column.main.skinny.brand-text
					{		
					padding-bottom: 0px !important;
					}
				.aside.column.skinny
					{
					padding-top: 0px !important;
					}
				.refineSearchForm
					{
					padding-top: 0px !important;
					}
				.box.box-shadow.gradient-bg.refine-search
					{
					padding-top: 0px !important;
					}
				.refineSearch-hr
					{
					width: 93% !important;
					}
				.cta-buttons
					{
					display: none !important;
					}
				#search-cars-form
					{
					margin-top:12px !important;
					}
				#mobile-print
					{
					display:block !important;
					}
				button.box-tabs-accordion, button.box-tabs-accordion.active, button.box-tabs-accordion:hover 
					{
					display:block !important;
					}
				div.box-tabs-panel 
					{
					display:block !important;
					}
				.column.main.skinny 
					{
    				padding: 10px 50px 20px 50px;
					}
				.overview-row{
					width:30%;
					justify-content: space-between;
					}
				.overview-hr{
					display: none;
					}
				.overview-block{
					display: flex;
					}
				.overview-item{
					width: 100% !important;
					height: auto !important;
					text-align: center;
					padding: 0px !important;
					} 
				}
		.overview-row{
			width:100%;
			justify-content: space-between;
			}
		.lg-outer .lg-thumb-item
			{
			border:none !important;
			border-radius: 0px !important;
			}
		.lg-thumb-item
			{
			width:120px !important;
			height:auto !important;
			}
		div.box-tabs-panel
			{
			max-height: 0px;
    		}
		.finCollapsible 
			{
  				background-color: transparent;
   				color: #565656;
   				cursor: pointer;
   				width: 100%;
   				border: none;
   				text-align: left;
				padding:0,0,0,0;
				outline: none;
				font-size: 1.0vh;
			}
		.active, .finCollapsible:hover 
			{
			color: #000000;
			}
		
		.finContent 
			{
			padding-left:2.5%;
			padding-right:2.5%;
			padding-top:5%;
			max-height: 0;
			width: 100%;
			overflow: hidden;
			transition: max-height 0.2s ease-out;
			background-color: transparent;
			}
		a.btn.cta 
			{
			font-size: unset;
			font-weight: bold;
			}
	div#enquireThisCar_contact_chosen {
    margin-bottom: 14px;
}
</style>	

	<div class="row">
		<div class="row-inner">
			<?php //include("crumbs.inc.php"); ?>
			<div class="breadcrumbs">
			   <!-- <a id="back-action" href="#" title="Back To Search Results" style ="font-weight: bold;
				font-size: larger;">Back To Search Results</a>-->
				<?php 
				// Program to display URL of current page. 
				if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
					{
		$link = "https"; 
		}
				else
					{
		$link = "http"; 
		}
					// Here append the common URL characters. 
					$link .= "://"; 
					// Append the host(domain name, ip) to the URL. 
					$link .= $_SERVER['HTTP_HOST']; 
					// Append the requested resource location to the URL 
					$link .= $_SERVER['REQUEST_URI']; 
				?>
				<?php 
				//Build the functionality to check which search page user came from.Check for match
				if ($_SESSION['firstPageURL'] == $link) 
					{
		//Display edited text, generic search link
		echo("<a href=\"/search/\" title=\"To Search\" style =\"font-weight: bold;
		font-size: larger;\">To Search</a>");
		}
					//If no match, check session array for previous search result
				else
					{
		foreach($_SESSION['origURL'] as $key => $value)
			{
			if(preg_match('/.\?s./',$value))
				{
				$found[$key]=$value;	
				};
			};
			if (isset($found))
				{
				$corectOverview = array_reverse($found,true);
				echo("<a href=".reset($corectOverview)." title=\"Back To Search Results\" style =\"font-weight: bold;font-size: larger;\">Back To Search Results</a>");
				}
			else 
				{
				echo("<a href=\"/search/\" title=\"To Search\" style =\"font-weight: bold;font-size: larger;\">To Search</a>");
				}
	};
				?>
			</div>
			<div class="content car-detail-page">
				<div class="column main skinny brand-text">
					<?php if(!empty($emailResult)) 
						{
						include("enquiry-confirmation.inc.php");
						$formName = "Individual Vehicle Enquiry"." ".$car['typeName'];//Individual Vehicle Enquiry
						$eventLabel = "Individual Vehicle Enquiry"." ".$car['typeName'];
						$formName2 = "";
						$eventLabel2 = "";
						if($etype=="testdrive")
							{
							$formName = "Test Drive Booking"." ".$testDriveFormCarType;	
							$eventLabel = "Test Drive Booking"." ".$testDriveFormCarType;
							//only for toyota
								if ($site['siteID']=='3')
									{
									$formName2 = "Test Drive Booking"." ".$car['typeName'];
									$eventLabel2 = "Test Drive Booking"." ".$car['typeName'];
									if (($car['typeName']) == "Demo") 
										{
										$formName = "Test Drive Booking New";
										}
									else
										{
										$formName = "Test Drive Booking"." ".$car['typeName']; //Individual Vehicle Enquiry
										}
									$eventLabel = "Test Drive Booking"." ".$car['typeName'];
									}
							}
							else if($etype=="enquireThisCar" ||$etype=="enquireThisCarMobile")
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
	
	
	
	
	
	
								//--implement 19/09/2019 START--/
							else if($etype=="FinanceEnquiry")
								{
								$formName = "Finance Enquiry"." ".$car['typeName'];	
								$eventLabel = "Flexible Finance"." ".$car['typeName'];
								}
									//--implement 19/09/2019 END--/								 
								
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
							}
							// weekly specials 
							$class_special="closed";
							$carTitle= $car['year']." ".$car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName'];
							if($weekly_specials) 
								{
								// if greater than 42 characters 
								if(strlen($carTitle)>42)
									{
									$carTitle= $car['year']." ".$car['makeName']." ".$car['modelName']." ".$car['seriesName']." ".$car['badgeName'];
									}
								$carTitle= "Weekly Special - ". $carTitle;
								$class_special="weekly-special";	
								}
							else 
								{
								// featured car 	
								if( $car['onlineSpecial'])
									{
									$class_special="featured-grad";	
									}
								}
					?>				

					<div class="box box-content 
						<?php
							echo $class_special?> car-details">
						<h2>
							<?php
								echo ($carTitle);?>
						</h2>
						<!--            <p class="price">$
						<?php
							echo (number_format($vehiclePrice,0)." <span class=\"notation\">".$priceNotation."</span>"); 
						?></p> --> 
					</div>
					<style>
						@media (max-width: 1281px) 
							{
							#top-banner
								{
								margin-left: 10px !important;
								margin-right: 10px !important;
								margin-top: -10px !important;
								}
							}
					</style>
					<div id="top-banner" style="" > 
						<?php
							include("banner.php"); 
						?>
					</div>				
					<div class="box vehicleDetail">
						<div id="carSlider" class="slider flexslider" >
							<ul class="slides"  id="lightgallery" >
								<?php 
									foreach($car['images'] as $k => $image) 
										{
										echo ("<li data-src=\"//jarviscars.com.au/_cache/".$image."\" > <img  class=\"ajaxpop\" title=\"\" alt=\"\" src=\"//jarviscars.com.au/_cache/".$image."\" /> </li>");
										}
								 ?>
							</ul>
						</div>
						<div id="carCarousel" class="carousel flexslider">
							<ul class="slides">
							<?php 
								foreach($car['images'] as $k => $image) 
									{
									echo ("<li><img title=\"\" alt=\"\" src=\"//jarviscars.com.au/_cache/".$image."\" /></li>");
									}
							?>
							</ul>
						</div>
						<div class="box">
							<div class="car-sidepanel">
							   <p class="price">
									<?php 
										if ($car['isTestDrive']=="1")
										{
											echo ("Test Drive"); 
										}
										else { 
											echo("$".number_format($vehiclePrice,0).$priceNotation);
										} 
										?>
								</p>
								<!--//--implement 19/09/2019 start--/-->
								<!--finance block starts here-->
								<?php include('pages/disclaimer-code/index.php'); ?>
								<?php //echo("\$noFinance=".$noFinance); ?>

								<!--noFinance flag is being set in disclaimer-code/index.php and can be killed by setting $kill variable at top-->	
								<?php 
									if($noFinance!=1)
									{ 
								?>	 
								
								
								<!--
								implement 13-11	
								<ul class="cta-buttons">
									<li>

									</li>
								</ul>
								!-->	
								<div class="box box-shadow" style="margin-top:0px;">
									<!--implement 13-11!-->		
									<a class="financeOverview" style="padding:8px 0px; margin:0px;">
									Finance Overview
									</a>
									<div style="width:90%;margin:auto;text-align: center;background-color:#004b8d;margin-top:5%;color:white">

										<p span style="font-size:small;font-weight:regular;margin:auto;padding-top: 2%;">
											Weekly Driveaway Repayments^
										</span>
										</p>
										<p style="font-size: large;font-weight:bold;padding-top: -5%; margin-bottom: 10px;">
											<strong>
												<?php 
													echo("$".(ceil($periodPayment_lowest).".00")); 
												?>
												-
												<?php 
													echo("$".(ceil($periodPayment_highest).".00")); 
												?>
											</strong>
										</p>
									</div>
									<!-- close box Shadow -->
									<div style="width:90%;margin:auto">
										<div class="overview-block">
										<div class="overview-row">
											<div class="overview-item" style="width:15%;height:15%;float:left;display: inline-block">
												<img src="<?php echo(CDN_URL); ?>img/finance/finance-icons-03.svg" height="32px">
											</div>
											<div class="overview-item" style="font-size:small;font-weight:regular;margin:auto;padding-top: 2%;float:left;display: inline-block;margin-top:1%;padding-left:5%">
												Deposit
											</div>
											<div class="overview-item" style="font-size:small;font-weight:regular;margin:auto;padding-top: 2%;float:right;display: inline-block;margin-top:1%;padding-left:20%">
												<?php 
													echo("$".$deposit); 
												?>
											</div>
											<hr class="overview-hr" style="margin:0px 0px 10px 0px;">
										</div>
											
										<div class="overview-row">
											<div class="overview-item" style="width:15%;height:15%;float:left;display: inline-block;">
												<img src="
													<?php 
														echo(CDN_URL); 
													?>img/finance/icon_02.svg" height="32px">
											</div>
											<div class="overview-item" style="font-size:small;font-weight:regular;margin:auto;padding-top: 2%;float:left;display: inline-block;padding-left:5%">
												Finance Term
											</div>
											<div class="overview-item" style="font-size:small;font-weight:regular;margin:auto;padding-top: 2%;float:right;display: inline-block;padding-left:5%">
												5 Years
											</div>
											<hr class="overview-hr" style="margin:0px 0px 10px 0px;">
										</div>
											
										<div class="overview-row">
											<div class="overview-item" style="width:15%;height:15%;float:left;display: inline-block;">
												<img src="
													<?php
														echo(CDN_URL); 
													?>img/finance/finance-icons-05.svg" height="32px">
											</div>
											<div class="overview-item" style="font-size:small;font-weight:regular;margin:auto;padding-top: 2%;float:left;display: inline-block;padding-left:5%;margin-bottom:-5%">	
												<p style="padding-top:-7%;line-height: 120%; ">
													Interest <br class="overview-hr"> Rate P.A.
												</p>
											</div>
											<div class="overview-item" style="font-size:small;font-weight:regular;margin:auto;padding-top: 2%;float:right;display: inline-block;">
												<?php 
													echo($interestRateLowest); 
												?>
												-
												<?php
													echo($interestRateHighest); 
												?>%
											</div>
											<hr class="overview-hr" style="margin:0px 0px 10px 0px;">
										</div>
											
										</div>
										<div>
											<div style="width:100%;margin:auto;text-align: center;background-color:#004b8d;margin-top:5%;color:white">
												<p span style="font-size:small;font-weight:regular;margin:auto;padding-top: 2%">
													Comparison Rate P.A.
													<sup>#</sup>
												</p>
												<p strong style="font-size: large;font-weight:bold;padding-top: -5%;margin-bottom:0px;">
													<?php 
														echo($comparisonRateLowest."%"); 
													?>
													- 
													<?php
														echo($comparisonRateHighest."%"); 
													?>
				   <!--Is This And Extra?-->	</strong>
												</p>
											</div>
											 <!--Solution to scale text to DIV not supported in iE
											<a href="#" class="disclaimer_popup_open finCollapsible" style="font-size:48%">
												<svg width="100%" height="100%"  viewBox="0 0 169 15">
													<text x="0" y="15">^#Click for important comparison rate information</text>
												</svg>
											<hr style="margin:0px;">
											 Is This And Extra?-->
							
											<a href="#" class="disclaimer_popup_open finCollapsible">
												^#Click for important comparison rate information
											<hr style="margin:0px;">
											<div style="display: inline-block;">
												<div style="width:33%; display: inline-block;">
													<img style="width: 100%; " src="
													<?php 
														echo(CDN_URL); 
													?>img/finance/st-george.jpg">
												</div>
												<div style="width:64%; float:right;">
													<p class="finCollapsible" style="margin-bottom:0px;padding-left:5px;padding-top:5px;">
														This dealer offers finance as a representative of St. George
													</p>
												</div>
											</div>
											</a>
										</div>
										<div id="disclaimer_popup" class="well" style="max-width:44em;z-index: 20000001">
											<a href="#" class="disclaimer_popup_close" style="float:right;padding:0 0.4em;text-decoration:none;color:#000000;font-weight:bold;">
												X
											</a>		
											<!--<div class="finContent" style="font-size: 10px; margin-bottom:0px; line-height:1.1em;">-->
											<p>
												<?php 
													include('pages/disclaimer-text/index.php');
												?>
											</p>
											<span class="col34">
												<a style="float:left;padding-top: 15px;" href="https://cdn.jarviscars.com.au/pdf/FixedRateLoanAgreement_STG.pdf" target="_blank"><img  src="
													<?php 
														echo(CDN_URL); 
													?>img/icons/pdf-icon.gif" alt="St. George Fixed Rate Loan Agreement"/>
													Download the St. George Fixed Rate Loan Agreement
												</a> 
												<br/>
												<!--<button class="disclaimer_popup_open finCollapsible">#Click for important comparison rate information</button></p>-->
											</span>
											<span class="col4">
												<img style="width:100%; float:right;" src="
													<?php 
														echo(CDN_URL); 
													?>img/finance/st-george.jpg">
											</span>
										</div>
										<!--<hr style="margin:10px 0px 10px 0px;">-->
									</div>
									<p class="callnow" style="background-color: #004b8d; padding-left:12px;padding-right:12px;">
										<a class="finance_popup_open" href="" style="font-size:16px;color:#fff;">
											Personalised Quote
										</a>
									</p>
					
									<!--Finance Enquiry Popup Starts Here-->
									<div id="finance_popup" class="well" style="max-width:44em;z-index: 20000001">
										<a href="#" class="finance_popup_close" style="float:right;padding:0 0.4em;text-decoration:none;color:#000000;font-weight:bold;">
											X
										</a>
										<h2 style="text-align: left;">
											Personalised Quote
										</h2>
										<h3 style="text-align: left;">
											<?php 
												echo ($carTitle);
											?>
										</h3>
										<hr style="border-bottom: 1px solid black;">
										<span class="col3 left-column" style="padding-top: 10px;">
											<p span style="font-size:small;font-weight:regular;margin:auto;">
												Sale Price
											</p>
											<p  style="font-size: large;font-weight:bold;">
												
									          	
												
												$<?php echo (number_format($vehiclePrice,0)."<span".$priceNotation_1."</span>");?>
												
												<?php 
												$financeFormData['finance_salePrice'] =$vehiclePrice; 
										       
												?>
												
												
											<input type="hidden" name="finance_salePrice"   value="<?php echo($finance_salePrice);?>"  />		
												
												
											</p>
										</span>
										<span class="col3 centre-column" style="padding-top: 10px;">
											<p span style="font-size:small;font-weight:regular;margin:auto;white-space: nowrap;">
												Weekly Driveaway Repayments^
											</p>
											<p  style="font-size: large;font-weight:bold;">
												<?php 
													echo("$".ceil($periodPayment_lowest).".00"); 
												?> -
												<?php 
													echo("$".ceil($periodPayment_highest).".00"); 
												?>
												<?php 
												$financeFormData['finance_periodPaymentLowest'] =	("$".ceil($periodPayment_lowest).".00"); 
										      $financeFormData['finance_periodPaymentHighest'] =	("$".ceil($periodPayment_highest).".00"); 
												?>	
												
												
												
												
	                                    <input type="hidden" name="finance_periodPaymentLowest"   value="<?php echo ($financeFormData[$finance_periodPaymentLowest]); ?>"  />
		                                <input type="hidden" name="finance_periodPaymentHighest"   value="<?php echo($financeFormData[$finance_periodPaymentHighest]);?> "  /> 
												
												
												
												
											</p>
										</span>
										<span class="col3 right-column" style="padding-top: 10px;">
											<p style="font-size:small;font-weight:regular;margin:auto;">
												Comparison Rate P.A.
											<sup>#</sup>
											</p>
											<p style="font-size: large;font-weight:bold;">
												<?php 
													echo($comparisonRateLowest."%");
												?>					
												- 
												<?php 
													echo($comparisonRateHighest."%");
												?>
											</p>
										</span>
										<hr style="border-bottom: 1px solid black;margin-bottom:10px;">
										<p style="font-size:small;font-weight:regular;margin:auto;text-align: center;white-space: nowrap;">
											5 Year Term, <?php echo("$".$deposit);?> Deposit
											<?php 
												$financeFormData['finance_deposit'] =$deposit; 
										      
												?>
										<input type="hidden" name="finance_deposit" id="finance_deposit"  value="<?php echo ($deposit);?>"  />	
										</p>
										<hr style="border-bottom: 1px solid black;margin-top:10px;">
										<div class="container">
											<!-- onsubmit="return validate_test_drive_form()">!-->
											<form name="Finance Enquiry Form" id="finance_enquiry_vdp_form" method="post" action="
												<?php 
													echo ($_SERVER['REQUEST_URI']); 
												?>"><div class="form-row">
												<div class="form-group col-md-6">
												<select name="finance_contactVia" id="finance_contactVia" class="justselect" data-placeholder="Preferred contact method">
													
													<option style="margin-top: 7pt;" default   value="Preferred contact method">Preferred contact method</option>
														
														<option style="margin-top: 7pt;"    value="Email">Email</option>

														<option style="margin-top: 7pt;"   value="Call" >Call</option>

														<option style="margin-top: 7pt;"     value="SMS">SMS</option>
														
												</select>
													</div>
											<!--additional Finance Form Fields-->
												
												<input type="hidden" name="finance_periodPaymentLowest"  id="finance_periodPaymentLowest" value="<?php echo ($financeFormData['finance_periodPaymentLowest']); ?>"  />
		                        <input type="hidden" name="finance_periodPaymentHighest" id="finance_periodPaymentHighest"  value="<?php echo($financeFormData['finance_periodPaymentHighest']);?> "  /> 
								<input type="hidden" name="finance_salePrice"  id="finance_salePrice" value="<?php echo($financeFormData['finance_salePrice']);?>"  />							
												
												
									<input type="hidden" name="finance_deposit" id="finance_deposit"  value="<?php echo ($financeFormData['finance_deposit']);?>"  />
												
														<div class="form-group col-md-6">
															<input type="text" name="finance_name" id="finance_name" required="required" value="<?php echo ($financeFormData['finance_name']); ?>" placeholder="Name*" />
														</div>
														<div class="form-group col-md-6">
															<input type="tel" name="finance_phone" id="finance_phone" required="required" value="<?php echo ($financeFormData['finance_phone']); ?>"  placeholder="Phone*" />
														</div>
														<div class="form-group">
															<!--<label for="exampleInputEmail1">Email address</label>-->
															<input type="email" class="form-control" name="finance_email" id="finance_email" required="required" aria-describedby="emailHelp" value="<?php echo ($financeFormData['finance_email']); ?>"  placeholder="Email">
														</div>
														<div class="form-group">
															<textarea name="finance_comments" style="height:100px;" value="<?php echo ($financeFormData['finance_comments']); ?>"  placeholder="Your Message"></textarea>
														</div>
												</div>

											<!--only for toyota-->
											<?php 
												if ($site['siteID']=='3' && $car['isTestDrive']!="1"){ 
											?>
												<div class="form-group" style="margin-bottom:0px;margin-top:14px;padding-left:16px;">
													<label style="font-weight:normal;">
														<input type="checkbox" name="enquireThisCarForm_toyotaAgreement" id="enquireThisCarForm_toyotaAgreement" required="required" value="1" style="margin-left:-16px;"> I agree with the website <a href="https://www.jarvistoyota.com.au/about/usage/" target="_blank">Terms of Use</a> and that my information will be handled by Dealer and OneToyota in accordance with the <a href="https://www.jarvistoyota.com.au/about/privacy/" target="_blank">Dealer Privacy Policy</a>
													</label>
												</div>
											<?php 
												} 
											?>
											<!--only for toyota-->
												<div class="form-group emailAlt">
														<label for="emailAlt">Leave Blank</label>
														<input type="text" value="" id="emailAlt" name="emailAlt" />
												</div>
											<!--only for subaru-->
											<?php 
												if ($site['siteID']=='4'){ 
											?>
												<div class="form-group"style="margin-bottom:0px;margin-top:14px;padding-left:16px;">
														<label style="font-weight:normal;width:100%;">
														<input type="checkbox" name="buy_Marketing-OptIn" id="receiveOffers_yes"  style="margin-left:3px;" value="Yes"> Yes  I would like to receive latest offers and product updates.
													   </label>  
													</div> 
											<?php  
												}
											?>     
											<!--only for subaru-->
												<input type="hidden" name="enquire_type" value="FinanceEnquiry">
												<input type="hidden" name="finance_vID" value="<?php echo ($car['inventoryID']); ?>" />
												<input type="hidden" name="finance_url" value="<?php echo ($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" />
												<div style="display: flex;justify-content: center;">
												<input type="submit" name="submit" value="Send" class="btn btn-send vdp" style="-webkit-appearance: none;border-radius: 0;-webkit-border-radius: 0;"/>
											</div>
											</form>

										<div style="text-align: left;" class="col23">
											<a href="#" class="disclaimer_popup_open finance_popup_close">
												<sup>^#</sup>
												Click for important comparison rate information
											</a>
											<br>
											<a href="/about/privacy/">
												Privacy Policy
											</a>
										</div>
										<div class="col3">
											<img style="width:50%;float:right;" src="<?php echo(CDN_URL); ?>img/finance/st-george.jpg">
										</div>
									   </div>

									</div>
								<!--Impliment 14/11-->
								<!--</div>-->
								<!-- Close Box Shadow -->
				
									<p class="callnow">
										<a class="btn" href="tel:1800155588" style="color:#fff;font-size:16px;">
										FREE CALL NOW
										</a>
									</p>
									<!--Finance Enquiry Popup Ends Here-->
					
									<!--finance block stops here-->
									<?php 
										} 
										else 
										{ 
									?>
									<!--new enquire form-->
								
									<!--new enquire form-->
									
									<!--call now-->
									
									<!--call now-->
										
									<!--new enquire form-->
									<!--call now-->
                            <p class="callnow"><a class="btn" href="tel:1800155588" style="color:#fff;font-size:16px;">FREE CALL NOW</a></p>
                            <!--call now-->

                            <!--new enquire form-->
                            <div class="box box-shadow gradient-bg refine-search" style="margin-top:10px;border: none;box-shadow: none;margin-bottom: 0px;">
                               <form name="Enquire This Car <?php echo $car['typeName']; ?>" id="enquireThisCarForm" method="post" action="<?php echo ($_SERVER['REQUEST_URI']); ?>" class="refineSearchForm" style="padding:0px;" onsubmit="return validate_vdp_enquiry_form()">
                                    <div>
                                        <fieldset class="basicSearchFields">
                                        	<legend style="font-size: 1.7rem;font-weight: bold;padding:12px;padding-top: 20px;"><?php echo $enquireFormHeading; ?></legend>
                                            <hr class="refineSearch-hr" style="width: 225px;border-bottom: 1px solid #565656;margin: 0 10px 0 10px;">
                                            <div class="basicSearchFieldsWrapper">
                                            	<div class="form-group">
                                                	<input type="text" name="enquireThisCar_name" id="enquireThisCar_name" required="required" value="<?php echo ($enquireThisCarFormData['enquireThisCar_name']); ?>" placeholder="Name*" />
                                                </div>
                                                <div class="form-group">
                                                	<input type="text" name="enquireThisCar_email" id="enquireThisCar_email" required="required" value="<?php echo ($enquireThisCarFormData['enquireThisCar_email']); ?>" placeholder="Email*" />
                                                </div>
                                                <div class="form-group">
                                                	<input type="text" name="enquireThisCar_phone" id="enquireThisCar_phone" required="required" value="<?php echo ($enquireThisCarFormData['enquireThisCar_phone']); ?>" placeholder="Phone*" />
                                                </div>
                                                <div class="form-group" style="margin-bottom:0px;">
                                                    <textarea name="enquireThisCar_comments" style="height:210px;" placeholder="Comments"><?php echo ($enquireThisCarFormData['enquireThisCar_comments']) ?></textarea>
                                                </div>
                                                <!--only for toyota-->
                                                <?php if ($site['siteID']=='3'){ ?>
                                                <div class="form-group" style="margin-bottom:0px;margin-top:14px;padding-left:16px;">
                                    	<label style="font-weight:normal;"><input type="checkbox" name="enquireThisCarForm_toyotaAgreement" id="enquireThisCarForm_toyotaAgreement" required="required" value="1" style="margin-left:-16px;"> I agree with the website <a href="https://www.jarvistoyota.com.au/about/usage/" target="_blank">Terms of Use</a> and that my information will be handled by Dealer and OneToyota in accordance with the <a href="https://www.jarvistoyota.com.au/about/privacy/" target="_blank">Dealer Privacy Policy</a></label>
                                    </div>
                                                <?php } ?>
                                                <!--only for toyota-->
                                            
                                            <div class="form-group emailAlt">
                                                <label for="emailAlt">Leave Blank</label>
                                                <input type="text" value="" id="emailAlt" name="emailAlt" />
                                            </div>
                                             <!--only for subaru-->
							 <?php if ($site['siteID']=='4'){ ?>
               <div class="form-group"style="margin-bottom:0px;margin-top:14px;padding-left:16px;">
           <label style="font-weight:normal;width:100%;">
          <input type="checkbox" name="buy_Marketing-OptIn" id="receiveOffers_yes"  style="margin-left:3px;" value="Yes"> Yes  I would like to receive latest offers and product updates.
               </label>  </div> <?php  }?>     
                         
                   <!--only for subaru-->
                                            
                                            
                                            
                                            
                                            
                                            
                                            
                                            <input type="hidden" name="enquire_type" value="enquireThisCar">
                                            <input type="hidden" name="enquire_checkAvailabilityFlag" value="<?php echo $_SESSION["checkAvailabilityFlag"]; ?>">
                                            <input type="hidden" name="enquireThisCar_vID" value="<?php echo ($car['inventoryID']); ?>" />
                                            <input type="hidden" name="enquireThisCar_url" value="<?php echo ($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" />
                                           
                                            <input type="submit" name="submit" id="vdp-sendenquiry" value="<?php echo $enquireFormButton; ?>" class="btn btn-send" style="-webkit-appearance: none;border-radius: 0;-webkit-border-radius: 0;"/>
                                    </div>
                                </form>
                            </div>
									<!--new enquire form-->	
									<!--unnessary enquiry form-->
										<?php 
										} 
									?>
									<?php
										if(($car['typeID'] == "2") || ($car['typeID'] == "3")) 
										{
											$freeExtras['term'] = 5;
											$freeExtras['anchor'] = "#new-cars";
										} 
										else 
										{
											$freeExtras['term'] = 3;
											$freeExtras['anchor'] = "#used-cars";
										}
									?>
									<!--<div><a href="/free-extras/
										<?php 
											echo ($freeExtras['anchor']); 
										?>">
										<h3 class="brand-text">
											Jarvis Free Extras
										</h3>
										<ul class="check-list">
											<li>Free 
												<?php 
													echo ($freeExtras['term']); 
												?> Year Unlimited Km Jarvis Warranty
											</li>
											<li><img src="
											<?php 
												echo(CDN_URL); 
											?>content-img/raa.jpg" style="float:right;" />Free 
											<?php 
												echo ($freeExtras['term']); 
											?> Year RAA Road Service 
											</li>
										</ul>
										</a>
									</div>-->
									</div>
										<ul class="cta-buttons">
										<li>
											<a class="btn cta" href="#financeTab" data-target="financeTab" data-tab="7"><!--<i class="icon icon-database"></i>--> 
												Finance Calculator
											</a>
										</li>
										
										
										<li>
											<a class="btn cta enquireCTA" href="#testdriveTab" data-target="testdriveTab" data-tab="4"><!--<i class="flaticon flaticon-car"></i>--> 
												Test Drive
											</a>
										</li>
										<li>
											<a class="btn cta" href="#tradeinTab" data-target="tradeinTab" data-tab="6"><!--<i class="icon icon-key"></i>--> 
												Trade-In
											</a>
										</li>
										<li>
											<a class="btn cta" href="#interstateTab" data-target="interstateTab" data-tab="8"><!--<i class="icon icon-flight"></i>--> 
												Interstate
											</a>
										</li>
										<li>
											<a class="btn cta" href="#shareTab" data-target="shareTab" data-tab="5">
												Share 
													<i class="icon icon-forward">
													</i>
											</a>
										</li>
									</ul>
								
								<!--
								implement 13-11-2019
								-->
								<!-- Close Box Shadow 
								 -->
							</div>
							<!-- Close Sidepanel -->
						</div>
						<!-- Close Box -->


						<!-- start TOP tabs -->
						<div class="box box-tabs tabs-rounded">
							<div class="tab box-shadow gradient-bg" style="background:#FFFFFF;">
								<h3 class="tab-title">
									<a id="vehicleDetails" class="hidden-anchor"></a>
									Details
								</h3>
								<?php 
									include("details-box-tabs.inc.php"); 
								?>
							</div>
							<?php
							// begin features tab 
							echo ("<div class=\"tab box-shadow gradient-bg\">");
							echo ("<h3 class=\"tab-title\"><a id=\"vehicleFeatures\" class=\"hidden-anchor\"></a>Features</h3>");
							include("features-box-tabs.inc.php");
							echo ("</div><!--end vehicle feature -->");
							//end features tab

							//begin location tab
							echo ("<div class=\"tab box-shadow gradient-bg\">");
							echo ("<h3 class=\"tab-title\">Location</h3>");
							include("location-box-tabs.inc.php");
							echo ("</div><!--end location tab -->");
							//end location tab

							$logo="logo-jarvis.png";
							if($sID==1 or $sID==8)
							{
								$logo="logo-jarvis.png";
							}
							else 
							{
								$logo=$site['siteLogo'];			
							}
							?>
							<!-- Start enquire tab-->
							<a id="enquireTab" class="hidden-anchor"></a>
							<div id="enquireTabBox" class="tab  box box-shadow gradient-bg">
								<h3 class="tab-title enquireCTA" data-target="enquireTab">
									Enquire
									</h3> 
									<?php 
									include("enquire-box-tabs.inc.php"); 
								?>  
									
									
								 
								
								</div>
							   <script src="https://cdn.jsdelivr.net/npm/@simonwep/selection-js/dist/selection.min.js"></script>
								   <div id="enquire_popup" class="well" style="max-width:44em;z-index: 20000001">
						<a href="#" class="enquire_popup_close" style="float:right;padding:0 0.4em;text-decoration:none;color:#000000;font-weight:bold;" id="<?php echo $car['inventoryID']; ?>">
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
											<hr style="border-bottom: 1px solid black;">
										</h2>
										
										<div class="container" >
											<!-- onsubmit="return validate_test_drive_form()">!-->
										<form name="Enquire This Car Mobile <?php echo $car['typeName']; ?>" id="enquireThisCarFormMobile" method="post" action="
												<?php 
													echo ($_SERVER['REQUEST_URI']); 
												?>">
												<div class="form-row">
														<div class="form-group col-md-6">
												<select name="enquireThisCarMobile_contact" id="enquireThisCarMobile_contact" class="justselect" data-placeholder="Preferred contact method">
													
													<option style="margin-top: 7pt;" default   value="Preferred contact method">Preferred contact method</option>
														
														<option style="margin-top: 7pt;"    value="Email">Email</option>

														<option style="margin-top: 7pt;"   value="Call" >Call</option>

														<option style="margin-top: 7pt;"     value="SMS">SMS</option>
														
												</select>
													</div>
											</div>
													
										
												<div class="form-row">
														<div class="form-group col-md-6">
															<input type="text" name="enquireThisCarMobile_name" id="enquireThisCarMobile_name" required="required" value="<?php echo ($enquireThisCarMobileFormData['enquireThisCarMobile_name']); ?>" placeholder="Name*" />
														</div>
														<div class="form-group col-md-6">
															<input type="tel" name="enquireThisCarMobile_phone" id="enquireThisCarMobile_phone" required="required" value="<?php echo ($enquireThisCarMobileFormData['enquireThisCarMobile_phone']); ?>"  placeholder="Phone*" />
														</div>
														<div class="form-group">
															<!--<label for="exampleInputEmail1">Email address</label>-->
															<input type="email" class="form-control" name="enquireThisCarMobile_email" id="enquireThisCarMobile_email" required="required" aria-describedby="emailHelp" value="<?php echo ($enquireThisCarMobileFormData['enquireThisCarMobile_email']); ?>"  placeholder="Email">
														</div>
														<div class="form-group">
															<textarea name="enquireThisCarMobile_comments" style="height:100px;" value="<?php echo ($enquireThisCarMobileFormData['enquireThisCarMobile_comments']); ?>"  placeholder="Your Message"></textarea>
														</div>
												</div>

										
												<?php if ($site['siteID']=='3'){ ?>
                                                <div class="form-group" style="margin-bottom:0px;margin-top:14px;padding-left:16px;">
                                    	<label style="font-weight:normal;"><input type="checkbox" name="enquireThisCarForm_toyotaAgreement" id="enquireThisCarForm_toyotaAgreement" required="required" value="1" style="margin-left:-16px;"> I agree with the website <a href="https://www.jarvistoyota.com.au/about/usage/" target="_blank">Terms of Use</a> and that my information will be handled by Dealer and OneToyota in accordance with the <a href="https://www.jarvistoyota.com.au/about/privacy/" target="_blank">Dealer Privacy Policy</a></label>
                                    </div>
                                                <?php } ?>
                                                <!--only for toyota-->
                                            
                                            <div class="form-group emailAlt">
                                                <label for="emailAlt">Leave Blank</label>
                                                <input type="text" value="" id="emailAlt" name="emailAlt" />
                                            </div>
                                             <!--only for subaru-->
							 <?php if ($site['siteID']=='4'){ ?>
               <div class="form-group"style="margin-bottom:0px;margin-top:14px;padding-left:16px;">
           <label style="font-weight:normal;width:100%;">
          <input type="checkbox" name="buy_Marketing-OptIn" id="receiveOffers_yes"  style="margin-left:3px;" value="Yes"> Yes  I would like to receive latest offers and product updates.
               </label>  </div> <?php  }?>     
											
										
												<div style="display: flex;justify-content: center;">
												<input type="submit" name="submit" value="Send" class="btn btn-send vdp" style="-webkit-appearance: none;border-radius: 0;-webkit-border-radius: 0;"/>
											</div>
											</form>

									
									
									   </div>

									</div>     
							
							<!-- end enquire tab-->

							<!-- start test drive tab-->
							<a id="testdriveTab" class="hidden-anchor"></a> 
							<div id="testdriveTab" class="tab  box box-shadow gradient-bg">
								<h3 class="tab-title enquireCTA" data-target="testdriveTab">
									Test Drive
								</h3> 
								<?php 
									include("test-drive-box-tabs.inc.php"); 
								?>         
							</div>
							<!-- end testdrive tab-->

							<!-- start share tab -->
							<a id="shareTab" class="hidden-anchor"></a>
							<div id="shareTabBox" class="tab  box box-shadow gradient-bg">
							<h3 class="tab-title">Share <i class="icon icon-forward"></i></h3>
								<div class="col col1">
								   <!-- <h4>Share</h4> -->
									<!--<a class="btn btn-share" target="_blank" href="http://www.facebook.com/sharer/sharer.php?u=http://<?php //echo ($_SERVER[HTTP_HOST].$_SERVER['REQUEST_URI']); ?>&title=<?php //echo ($car['mfrYear']." ".$car['makeName']." ".$car['modelName']." ".$car['bodyName']); ?>"><i class="icon icon-facebook"></i> Facebook</a> -->

									<!-- <a class="btn btn-share" target="_blank" href="https://plus.google.com/share?url=<?php echo ($_SERVER['REQUEST_URI']); ?>"><i class="icon icon-gplus"></i> Google Plus</a> --> <!--<a class="btn btn-share" target="_blank" href="http://twitter.com/home?status=<?php //echo ($car['mfrYear']." ".$car['makeName']." ".$car['modelName']." ".$car['bodyName']); ?>+<?php //echo ($_SERVER['REQUEST_URI']); ?>"><i class="icon icon-twitter"></i> Twitter</a>-->
									<h2 class="txt_center">
										Send to a Friend
									</h2>
									<div style="display:none ;" id="sendFriendResult" class="alert-box"></div>
									<form name="sendFriendForm" id="sendFriendForm" method="post" action="
									<?php echo ($_SERVER['REQUEST_URI']); ?>">
										<div class="col col2">
											<div class="form-group">
												<!--<label for="send_sender-name">Your Name</label>-->
												<input name="send_sender-name" type="text" placeholder="Your Name" />
											</div>
											<div class="form-group">
												<!--<label for="send_sender-email">Your Email</label>-->
												<input name="send_sender-email" type="text" placeholder="Your Email" />
											</div>
										</div>
										<div class="col col2">
											<div class="form-group">
												<!--<label for="send_recipient-name">Recipient Name</label>-->
												<input name="send_recipient-name" type="text" placeholder="Recipient Name" />
											</div>
											<div class="form-group">
												<!--<label for="send_recipient-email">Recipient Email</label>-->
												<input name="send_recipient-email" type="text" placeholder="Recipient Email" />
											</div>
										</div>
										<div class="col col1">
											<div class="form-group emailAlt">
												<label for="emailAlt">Leave Blank</label>
												<input type="text" value="" name="emailAlt" />
											</div>
											<input type="hidden" name="send_vID" value="<?php echo ($car['inventoryID']); ?>" />
											<input type="hidden" name="send_url" value="<?php echo ("http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']); ?>" />
											<input type="submit" name="sendFriendSubmit" id="sendFriendSubmit" value="Send" class="btn btn-send btn-fullwidth" style="margin-bottom:0px;background: #004b8d;text-transform: none;width: 220px;font-size: 1.6rem;"/>
										</div>
									</form>
								</div>
							</div> 
							<!-- end share  tab -->
						</div>
						<!-- end TOP tabs -->

						<!-- start bottom tabs-->              
						<div class="box box-tabs tab-car-actions ">

							<!-- start trade-in tab-->       
							<a id="tradeinTab" class="hidden-anchor"></a> 
							<div id="tradeinTabBox" class="tab  box box-shadow gradient-bg">
								<h3 class="tab-title"><!--<i class="icon icon-key"></i>-->
									Trade In
								</h3>   
								<?php 
									include('tradein-box-tabs.inc.php'); 
								?>                        
							</div>
							<!-- stop trade-in tab-->   

							<!-- start finance-in tab-->    
							<a id="financeTab" class="hidden-anchor"></a>
							<div id="financeTabBox" class="tab  box box-shadow gradient-bg">
									<h3 class="tab-title"><!--<i class="icon icon-database"></i>-->
										Finance
									</h3>
										<div class="col col1">
											<?php 
												include('calculator.inc.php'); 
											?>
										</div>
							</div>
							<!-- stop finance tab-->    

							<!-- start interstate tab-->    
							<a id="interstateTab" class="hidden-anchor"></a> 
							<div id="interstateTabBox" class="tab  box box-shadow gradient-bg">
								<h3 class="tab-title"><!--<i class="icon icon-flight"></i>-->
									Interstate
								</h3>
								<?php 
									include('interstate-box-tabs.inc.php'); 
								?>
							</div>
							<!-- stop interstate tab-->  

							<!-- start reviews tab-->  
							<div id="reviewTabBox" class="tab  box box-shadow gradient-bg">
								<h3 class="tab-title"><!--<i class="icon icon-thumbs-up"></i>-->
									Reviews
								</h3>
								<?php 
									include('reviews-box-tabs.inc.php'); 
								?>
							</div>
							<!-- stop reviews tab-->  

							<!-- start print tab-->  
							<div class="tab  box box-shadow gradient-bg"> 
								<a id="print-pdf" href="/download/vehicle-pdf-download.php?vID=
									<?php 
										echo $inventoryID?>&logo=<?php echo $logo;
									?>" target="_blank" class="tab-title" data-action="print-pdf"> <!--<i class="icon icon-print"></i>--> 
									Print
								</a> 
							</div>
							<!-- stop print tab--> 

						</div>
						<!-- end bottom tabs -->

					</div>
					<!-- end vehicle details -->

				<!--</div>-->
				<?php // include("vehicles-others-viewed.inc.php");	 ?>
				<div class="breadcrumbs">
				<!-- 
				<a id="back-actions" href="#" title="Back To Search Results" style ="font-weight: bold;font-size: larger;">Back To Search Results
				</a> 
				-->
				<?php  
					if ($_SESSION['firstPageURL'] == $link) 
					{
					//Display edited text, generic search link
					echo("<a href=\"/search/\" title=\"To Search\" style =\"font-weight: bold; font-size: larger;\">To Search</a>");
					}
					//If no match, check session array for previous search result
					else
					{
						foreach($_SESSION['origURL'] as $key => $value)
						{
							if(preg_match('/.\?s./',$value))
							{
							$found[$key]=$value;	
							};
						};
						if (isset($found))
						{
							$corectOverview = array_reverse($found,true);
							echo("<a href=".reset($corectOverview)."\" title=\"Back To Search Results\" style =\"font-weight: bold;font-size: larger;\">Back To Search Results</a>");
						}

						else 
						{
							echo("<a href=\"/search/\" title=\"To Search\" style =\"font-weight: bold; font-size: larger;\">To Search</a>");
						}
					};
				?>
				</div>
				
				</div>
				<!-- end main skinny column tabs -->
				
				<!--hidden variable to check if the visitor clicked on the enquire button in the search results page-->
				<input type="hidden" name="enquireBtnClicked" id="enquireBtnClicked" value="
				<?php 
					echo $enquireBtnClicked; 
				?>" />

				<!-- Start Refine Search Bar -->
				<div class="aside column skinny">
					<?php 
						//mobile only box tabs
						include("mobile-box-tabs.inc.php");
						//mobile only box tabs
						include("refine-search.inc.php");
						//include("side-tiles.inc.php");
						?>
				</div>
				<!-- Stop Refine Search Bar -->
			</div>
			<!-- Close Car Detail Page -->
		</div>
		<!-- Close Row Inner -->
	</div>
	<!-- Close Row -->
	<?php
		include("blocks/jarvis-difference-full-width.php");
	?>
</div>
<!-- /Close home-body -->


<?php include('footer.inc.php'); ?>
<!-- PAGE SPECIFIC JAVASCRIPT HERE -->

<!-- implementation 13-11-19 -->
<!-- fitty v2.2.6 - Snugly resizes text to fit its parent container -->
<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/fitty/fitty.min.js"></script>
<script>
fitty ("#ClickImp"),{
	multiLine:false
};

</script>
<!-- //implementation 13-11-19 -->

<link type="text/css" rel="stylesheet" href="<?php echo(CDN_URL); ?>css/lightgallery/lightgallery.css" /> 
<!-- A jQuery plugin that adds cross-browser mouse wheel support. (Optional) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.13/jquery.mousewheel.min.js"></script>

<!-- lightgallery plugins -->

<!-- JustSelect  plugins -->
<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/Justselect/selectbox.min.js"></script>
<!-- Justselect plugins -->
<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/lightgallery/lightgallery.min.js"></script>
<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/lightgallery/lg-thumbnail.min.js"></script>
<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/lightgallery/lg-fullscreen.min.js"></script>

<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/noUiSlider/nouislider.min.js"></script> 
<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/slider-function.js"></script>

<!-- adding validaton --> 

<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/jquery.validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo(CDN_URL); ?>js/jquery.validation/additional-methods.min.js"></script>
<script type="text/javascript">

$(document).ready(function(e) {
	
	var priceStep =  5000;
	var yearStep  = 1;
	
	  // The slider being synced must be initialized first
	  $('#carCarousel').flexslider({
		animation: "slide",
		controlNav: false,
		animationLoop: false,
		slideshow: false,
		itemWidth: 168,
		itemMargin: 1,
		asNavFor: '#carSlider'
	  });
	   
	  $('#carSlider').flexslider({
		animation: "slide",
		controlNav: false,
		animationLoop: true,
		slideshow: false,
		slideshowSpeed: 3500,
		pauseOnAction: true,
		pauseOnHover: true,
		smoothHeight:true,
		itemWidth: 676, 
		itemHeight: 447,
		itemMargin: 0,
		sync: "#carCarousel"
	  });
	
	var $lg = $('#lightgallery')
	$lg.lightGallery();
	$lg.on('onAfterOpen.lg',function(event){
		//hide menu 
    	$('.page-top').hide();
	});
	
	$lg.on('onCloseAfter.lg',function(event){
    	$('.page-top').show();
	});
	
	$("#reset-search").on("click", function(event) {
		 
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
		year_slider_rs.noUis.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		
		
		if ($("#price-slider-rs" ).length ) {
			price_slider_rs.noUiSlider.set([<?php echo $priceMin ; ?>, <?php echo $priceMax; ?>]);
			year_slider_rs.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		}
		
		if ($("#year-slider-rs" ).length ) {
			year_slider_rs.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		}
		
		dataLayer.push({
			'eventCategory' : 'Site Actions',
			'eventAction' : 'SRP - Reset Search'
		});
		
	});
	
	//new code
	$("#reset-search-with-more-options").on("click", function(event) {
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
		
		
		if ($("#price-slider-rs" ).length ) {
			price_slider_rs.noUiSlider.set([<?php echo $priceMin ; ?>, <?php echo $priceMax; ?>]);
			year_slider_rs.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		}
		
		if ($("#year-slider-rs" ).length ) {
			year_slider_rs.noUiSlider.set([<?php echo $yearMin ; ?>, <?php echo $yearMax; ?>]);
		}
		
		dataLayer.push({
			'eventCategory' : 'Site Actions',
			'eventAction' : 'SRP - Reset Search With More Options'
		});
	
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
				$(this).chosen();
			})
			var advFlag = $("#refineSearch #advFlag").val();
			if(advFlag == 1) {
				$("#refineSearch .advSearchFieldsWrapper").css('display',"block");
				$("#refineSearch .advSearchFields legend").addClass("active");
			}
			
			if($( "#price-slider-rs" ).length ) {
				var price_slider_rs = create_slider(<?php echo $priceMin;?>, <?php echo $priceMax;?>, $( "#pMiRS" ).val(),$( "#pMaRS" ).val(), document.getElementById("price-slider-rs"), priceStep);
				// update on change 
				price_slider_rs.noUiSlider.on('update', function( values, handle ) {
					$( "#pMiRS" ).val(Math.round(values[0]));
					$( "#pMaRS" ).val(Math.round(values[1]));
					var minFormat  = Math.floor( values[0]/1000);
					if(minFormat> 0){ minFormat= minFormat + "k"; } 
					$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(values[ 1 ]/1000) + "k"  );		
				});
			}
			// re-create
			
			if($( "#year-slider-rs" ).length ) {
				var year_slider_rs = create_slider(<?php echo $yearMin;?>, <?php echo $yearMax;?>, $( "#yMiRS" ).val(),$( "#yMaRS" ).val(), document.getElementById("year-slider-rs"), yearStep); 
				year_slider_rs.noUiSlider.on('update', function( values, handle ) {
					$( "#yMiRS" ).val(Math.round(values[0]));
					$( "#yMaRS" ).val(Math.round(values[1]));
					$("#yearRangeAmountRS" ).text(Math.round(values[ 0 ]) + " - " + Math.round(values[ 1 ]));
					
				});	
			}
			
			resetSearchJS();
			resetSearchWithMoreoptionsJS();
			
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
	
	$(".vehicleCommentsToggle").on('click',this,function(e) {
		//e.preventDefault();
		if($(this).html() == 'More...') {
			$(this).html('Less...');
		} else {
			$(this).html('More...');
		}
		$(".vehicleComments").toggle(50);
	});
	$(".vehicleDetailsToggle").on('click',this,function(e) {
		//e.preventDefault();
		if($(this).html() == 'More...') {
			$(this).html('Less...');
		} else {
			$(this).html('More...');
		}
		$(".vehicleDetails").toggle(50);
	});
	$(".vehicleFeaturesToggle").on('click',this,function(e) {
		//e.preventDefault();
		if($(this).html() == 'More...') {
			$(this).html('Less...');
		} else {
			$(this).html('More...');
		}
		$(".vehicleFeatures").toggle(50);
	});
	
	
	

	
 
	$(".cta").on('click',this,function(e) {
		var target = $(this).data("target");
		var tabid = $(this).data("tab");
												  
		$(".tab-title").removeClass('active');
		
		$('.tab[data-tab="' + tabid + '"]').siblings(".tab").hide();
		$(".tab-car-actions .tab-slide").css('display','none');
		
  
		$(".tab-title[data-tab='"+tabid+"']").addClass('active');
  
//		$("#"+target+"Box").css('display','block');
		$('.tab[data-tab="' + tabid + '"]').show();
		$("#"+target+"Box .tab-slide").css('display','block');
		
	});
	
	$(".enquireCTA").on('click',this,function(e) {
		var target = $(this).data("target");
		console.log(target);
		if($("#"+target+"Box").is(":visible")) {
			console.log("individual vehicle enquiry form");
			/*dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': 'Individual Vehicle Enquiry <?php //echo ($car['typeName']); ?>' });
			dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': 'Individual Vehicle Enquiry <?php //echo ($car['typeName']); ?>', 'formStatus': 'viewed' });*/
			dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': 'Individual Vehicle Enquiry <?php echo ($car['typeName']); ?>' });
			dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': 'Individual Vehicle Enquiry <?php echo ($car['typeName']); ?>', 'formStatus': 'viewed' });
		}
		
		if(target=='testdriveTab'){
			if($("#testdriveTab").is(":visible")) {
				console.log("test drive booking form");
				dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': 'Test Drive Booking <?php echo ($testDriveFormCarType); ?>' });
				dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': 'Test Drive Booking <?php echo ($testDriveFormCarType); ?>', 'formStatus': 'viewed' });
			}	
		}
	});
	
	$(".tab-title").on('click',this,function(e) {
		var tabid = $(this).data("tab");
		console.log("tabid"+tabid);
		if(tabid==4){
				//--implement 19/09/2019 START--/
			//$(".tab-title").addClass('enquire_popup_open');
	
				//--implement 19/09/2019 STOP--/			 
			console.log("test drive booking form1");
			dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': 'Test Drive Booking <?php echo ($testDriveFormCarType); ?>' });
			dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': 'Test Drive Booking <?php echo ($testDriveFormCarType); ?>', 'formStatus': 'viewed' });
		}else if(tabid==3){
			//--implement 19/09/2019 START--/
			//$(".tab-title").addClass('enquire_popup_open');
			
			//--implement 19/09/2019 STOP--/	   
			console.log("individual vehicle enquiry form1");
		 	dataLayer.push ({ 'event': 'gaTriggerEvent', 'gaEventCategory': 'Form', 'gaEventAction': 'Viewed', 'gaEventLabel': 'Individual Vehicle Enquiry <?php echo ($car['typeName']); ?>' });
			dataLayer.push ({ 'event': 'toyotaFormViewed', 'formName': 'Individual Vehicle Enquiry <?php echo ($car['typeName']); ?>', 'formStatus': 'viewed' });
		}else{
			//--implement 19/09/2019 START--/
			//$(".tab-title").removeClass('enquire_popup_open');
			//--implement 19/09/2019 STOP--/  
		}
	});
	
	
	$('#sendFriendSubmit').on('click',this,function(e) {
		dataLayer.push({
			'event': 'gaTriggerEvent', 
			'gaEventCategory': 'Site Actions', 
			'gaEventAction': 'Send to a Friend' 
		});
		e.preventDefault();
		var formData = $("#sendFriendForm").serialize();
		$.ajax({
			type: "GET",
			url: "/search/send-to-friend.ajax.php",
			data: formData,
			dataType: "json"
		})
		.done(function(result) {
			console.log(result);
			$("#sendFriendResult").html(result['message']);
			$("#sendFriendResult").addClass(result['class']);
			$("#sendFriendSubmit").val("RE-SEND");
			$("#sendFriendResult").show();
			console.log('send to a friend');
		});
		
	});
	
	//mobile share form
	$('#sendFriendSubmit-mobile').on('click',this,function(e) {
		e.preventDefault();

		var formData = $("#sendFriendForm-mobile").serialize();
		$.ajax({
			type: "GET",
			url: "/search/send-to-friend.ajax.php",
			data: formData,
			dataType: "json"
		})
		.done(function(result) {
			console.log(result);
			$("#sendFriendResult-mobile").html(result['message']);
			$("#sendFriendResult-mobile").addClass(result['class']);
			$("#sendFriendSubmit-mobile").val("RE-SEND");
			$("#sendFriendResult-mobile").show();
			
		});
		
	});
	
	// added link to print pdf. 
	$('a.tab-title[data-action="print-pdf"]').attr("href", $('#print-pdf').attr("href"));
	$('a.tab-title[data-action="print-pdf"]').attr("target", $('#print-pdf').attr("target"));

	
	// added link to review tab. 
	$('a.tab-title[data-action="read-review"]').attr("href", $('#read-review').attr("href"));
	$('a.tab-title[data-action="read-review"]').attr("target", $('#read-review').attr("target"));
	
	$('#back-action').on('click',this,function(e) {
		
		if(location.hash !="")
{	window.history.go(-2);
console.log("location.hash=2");}
		else{
		window.history.go(-1);	
		}
		
		
	});
	
	
		$('#back-actions').on('click',this,function(e) {
		
		if(location.hash !="")
{	window.history.go(-2);
console.log("location.hash=2");}
		else{
		window.history.go(-1);	
		}
		
		
	});
	
	
	if($( "#price-slider-rs" ).length ) {
		// create price sliders for Refine Search
		var price_slider_rs = create_slider(<?php echo $priceMin;?>, <?php echo $priceMax;?>, $( "#pMiRS" ).val(),$( "#pMaRS" ).val(), document.getElementById("price-slider-rs"), priceStep);
			
		price_slider_rs.noUiSlider.on('update', function( values, handle ) {	
			$( "#pMiRS" ).val(Math.round(values[0]));
			$( "#pMaRS" ).val(Math.round(values[1]));
			var minFormat  = Math.floor( values[0]/1000);
			if(minFormat> 0){ minFormat= minFormat + "k"; } 
			$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(values[ 1 ]/1000) + "k"  );		
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
		var price_slider_as = create_slider(<?php echo $priceMin;?>, <?php echo $priceMax;?>, $( "#pMiAS" ).val(),$( "#pMaAS" ).val(), document.getElementById("price-slider-as"), priceStep);
		price_slider_as.noUiSlider.on('update', function( values, handle ) {
			$( "#pMiAS" ).val(Math.round(values[0]));
			$( "#pMaAS" ).val(Math.round(values[1]));
			var minFormat  = Math.floor( values[0]/1000);
			if(minFormat> 0){ minFormat= minFormat + "k"; } 
			$("#priceRangeAmountAS" ).text( "$" +  minFormat + " - $" + Math.floor(values[ 1 ]/1000) + "k"  );		
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
	
	//new code
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
								price_slider_rs = create_slider(<?php echo $priceMin;?>, <?php echo $priceMax;?>, $( "#pMiRS" ).val(),$( "#pMaRS" ).val(), document.getElementById("price-slider-rs"), priceStep);
								// update on change 
								price_slider_rs.noUiSlider.on('update', function( values, handle ) {
									var selectedMinValue = values[0].replace('$','').replace(',','');
									var selectedMaxValue = values[1].replace('$','').replace(',','');
									$( "#pMiRS" ).val(Math.round(selectedMinValue));
									$( "#pMaRS" ).val(Math.round(selectedMaxValue));
									var minFormat  = Math.floor( selectedMinValue/1000);
									if(minFormat> 0){ minFormat= minFormat + "k"; } 
									$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(values[ 1 ]/1000) + "k"  );		
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
							
							resetSearchJS();
							resetSearchWithMoreoptionsJS();
							
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
							price_slider_rs = create_slider(<?php echo $priceMin;?>, <?php echo $priceMax;?>, $( "#pMiRS" ).val(),$( "#pMaRS" ).val(), document.getElementById("price-slider-rs"), priceStep);
							// update on change 
							price_slider_rs.noUiSlider.on('update', function( values, handle ) {
								var selectedMinValue = values[0].replace('$','').replace(',','');
								var selectedMaxValue = values[1].replace('$','').replace(',','');
								$( "#pMiRS" ).val(Math.round(selectedMinValue));
								$( "#pMaRS" ).val(Math.round(selectedMaxValue));
								var minFormat  = Math.floor( selectedMinValue/1000);
								if(minFormat> 0){ minFormat= minFormat + "k"; } 
								$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(values[ 1 ]/1000) + "k"  );		
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
						
						resetSearchJS();
						resetSearchWithMoreoptionsJS();
						
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
							price_slider_rs = create_slider(<?php echo $priceMin;?>, <?php echo $priceMax;?>, $( "#pMiRS" ).val(),$( "#pMaRS" ).val(), document.getElementById("price-slider-rs"), priceStep);
							// update on change 
							price_slider_rs.noUiSlider.on('update', function( values, handle ) {
								var selectedMinValue = values[0].replace('$','').replace(',','');
								var selectedMaxValue = values[1].replace('$','').replace(',','');
								$( "#pMiRS" ).val(Math.round(selectedMinValue));
								$( "#pMaRS" ).val(Math.round(selectedMaxValue));
								var minFormat  = Math.floor( selectedMinValue/1000);
								if(minFormat> 0){ minFormat= minFormat + "k"; } 
								$("#priceRangeAmountRS" ).text( "$" +  minFormat + " - $" + Math.floor(values[ 1 ]/1000) + "k"  );		
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
						
						resetSearchJS();
						resetSearchWithMoreoptionsJS();
						
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

});

</script>
<script type="text/javascript">
$(document).ready(function(e) {
	
	//$("#sendFriendSubmit").on('click',this,function(e) {
		//console.log('send to a friend');
		/*dataLayer.push({
			'eventCategory' : ' Site Actions',
			'eventAction' : 'Send to a Friend'
		});*/
		//});
		
	//To track the clicks on the print button
	$('a.tab-title[data-action="print-pdf"]').on('click',this,function(e) {
		console.log('print-pdf');
		dataLayer.push({
			'eventCategory' : ' Site Actions',
			'eventAction' : 'Print VDP'
		});
	});
	
	
		$('#repayment-estimator-calculator').on('click',this,function(e) {
			
			dataLayer.push({
			'eventCategory' : ' Site Actions',
			'eventAction' : 'Viewed ',
			'eventLabel': 'Finance Calulator'
		});
			
		});
	
	
	
	
	//To track the clicks on the print button
	
	//To track the clicks on the print button (mobile)
	$('a.mobile-print-text[data-action="print-pdf"]').on('click',this,function(e) {
		console.log('print-pdf1');
		dataLayer.push({
			'eventCategory' : ' Site Actions',
			'eventAction' : 'Print VDP'
		});
	});
	//To track the clicks on the print button (mobile)
	
	//calculate function
	$('#repayment-estimator-calculator').on('click',this,function(e) {
		$('#financeCalForm').submit();
	});
	//end to track the number of clicks for the repayment estimator calculator button
	$( "#financeCalForm" ).validate({
			  
		errorPlacement: function(error, element) {
			//Custom position: first name
				if (element.attr("name") == "loanAmount" ) {
					error.insertAfter('#loanAmountBox');
										}
				//Custom position: second name
				else if (element.attr("name") == "interestRate" ) {
					error.insertAfter('#interRateBox');
					
				}
				
		},	
				
		rules: {
		  interestRate: {
			required: true,
			number: true
		  },
		  loanAmount: {
			required: true,
			number: true
		  },
		  
		},
		messages: {
			loanAmount: {
			  required: "Please enter a Loan Amount.",
			  number: "Please enter a valid Loan Amount."
			},
			 
			 interestRate: {
				required: "Please enter an Interest Rate",
				number: "Please enter a valid Interest Rate."
			 }
			
		 }
			  	  
	});
	//calculate function
	
	//redbook disclaimer
	$(".redbookDisclaimer").css('display','none');
	$('.displayRedbookDisclaimer').on('click', function(e) {

	  $(".redbookDisclaimer").slideToggle();
	
	});
});
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
function resetSearchJS(){
	var priceStep =  5000;
	var yearStep  = 1;	
	$("#reset-search").on("click", function(event) {
		 
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
		
		dataLayer.push({
			'eventCategory' : 'Site Actions',
			'eventAction' : 'SRP - Reset Search'
		});
		
	});
}
function resetSearchWithMoreoptionsJS(){
	//new code
	$("#reset-search-with-more-options").on("click", function(event) {			
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
		
		
		dataLayer.push({
			'eventCategory' : 'Site Actions',
			'eventAction' : 'SRP - Reset Search With More Options'
		});
	
	});
}
function enquireCar(){
	$("#enquireThisCarForm").submit();
	console.log('enquire submitted');
}
//Validates the form fields
function validate_individual_enquiry_form() {
  	var message = "";
	// Regular Expression For Email
  	var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
	var websiteID = document.getElementById('siteID').value;
	
	var name = document.getElementById("car_name").value;
	var phone = document.getElementById("car_phone").value;
	var email = document.getElementById("car_email").value;

    if(name == "")
        message += "* Name\n";
	if(phone == "")
        message += "* Phone\n";
	if(email == "")
        message += "* Email\n";
	
	if(message != ""){
        alert("Please check the following field(s):\n\n" + message);
        return false;
    }else{
         if (email.match(emailReg)) {
			if(websiteID=='3'){
				//only for toyota
				if(document.getElementById("individual_vehicle_enquiry_form_toyotaAgreement").checked==true){
					return true;
				}else{
					alert("Please read and accept the Terms of Use and Privacy Policy.");
					return false;
				}
				//only for toyota
			}else{
				return true;
			}
		 } else{
		 	alert("Incorrect email address");
            return false;
		 }
    }
}
function validate_test_drive_form() {
  	var message = "";
	// Regular Expression For Email
  	var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
	var websiteID = document.getElementById('siteID').value;
	
	var name = document.getElementById("testdriver_name").value;
	var phone = document.getElementById("testdriver_phone").value;
	var email = document.getElementById("testdriver_email").value;

    if(name == "")
        message += "* Name\n";
	if(phone == "")
        message += "* Phone\n";
	if(email == "")
        message += "* Email\n";
	
	if(message != ""){
        alert("Please check the following field(s):\n\n" + message);
        return false;
    }else{
         if (email.match(emailReg)) {
			if(websiteID=='3'){
				//only for toyota
				if(document.getElementById("testdrivevdpform_toyotaAgreement").checked==true){
					return true;
				}else{
					alert("Please read and accept the Terms of Use and Privacy Policy.");
					return false;
				}
				//only for toyota
			}else{
				return true;
			}
		 } else{
		 	alert("Incorrect email address");
            return false;
		 }
    }
}
function validate_vdp_enquiry_form() {
  	var message = "";
	// Regular Expression For Email
  	var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
	var websiteID = document.getElementById('siteID').value;
	
	var name = document.getElementById("enquireThisCar_name").value;
	var phone = document.getElementById("enquireThisCar_phone").value;
	var email = document.getElementById("enquireThisCar_email").value;

    if(name == "")
        message += "* Name\n";
	if(phone == "")
        message += "* Phone\n";
	if(email == "")
        message += "* Email\n";
	
	if(message != ""){
        alert("Please check the following field(s):\n\n" + message);
        return false;
    }else{
         if (email.match(emailReg)) {
			if(websiteID=='3'){
				//only for toyota
				if(document.getElementById("enquireThisCarForm_toyotaAgreement").checked==true){
					return true;
				}else{
					alert("Please read and accept the Terms of Use and Privacy Policy.");
					return false;
				}
				//only for toyota
			}else{
				return true;
			}
		 } else{
		 	alert("Incorrect email address");
            return false;
		 }
    }
}

</script>

<script type="text/javascript">
var enquireBtnClicked = $("#enquireBtnClicked").val();
if(enquireBtnClicked==1){
$(".tab-title").removeClass('active');
		
$('.tab[data-tab="3"]').siblings(".tab").hide();
$(".tab-car-actions .tab-slide").css('display','none');
		
$(".tab-title[data-tab='3']").addClass('active');
$('.tab[data-tab="3"]').show();
$("#enquireTabBox .tab-slide").css('display','block');
}
//new code
$( window ).resize(function() {
	$(".box-tabs-accordion").removeClass('active');
});
var acc = document.getElementsByClassName("box-tabs-accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].onclick = function() {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.maxHeight){
      //panel.style.maxHeight = null;
	  panel.style.minHeight = null;
	  panel.style.maxHeight = null;
	  panel.style.height = null;
	  panel.style.paddingTop = null;
	  panel.style.paddingBottom = null;
    } else {
      //panel.style.maxHeight = panel.scrollHeight + "px";
	  panel.style.minHeight = "0";
	  panel.style.maxHeight = "none";
	  panel.style.height = "auto";
	  panel.style.paddingTop = "10px";
	  panel.style.paddingBottom = "10px";
    } 
  }
};
	
	
	
	
//default to also display the trade in tab
$('.tab-car-actions.tab-titles .tab-title:first').addClass('active');
$('#tradeinTabBox').show();
<!-- finance calculator submission -->
var repayment_estimator_hidden = "<?php echo $repayment_estimator_hidden; ?>";
console.log("repayment_estimator_hidden= "+repayment_estimator_hidden);
if(repayment_estimator_hidden==1) 
{
	$(".tab-car-actions .tab-titles .tab-title").removeClass('active');
	
	$('.tab[data-tab="7"]').siblings(".tab").hide();
	$(".tab-car-actions .tab-slide").css('display','none');
			
	$(".tab-title[data-tab='7']").addClass('active');
	$('.tab[data-tab="7"]').show();
	$("#financeTabBox .tab-slide").css('display','block');
	
}else{
	//default to also display the trade in tab
	$('.tab-car-actions.tab-titles .tab-title:first').addClass('active');
	$('#tradeinTabBox').show();
}
</script>

<!-- finance disclaimer accordeon -->
<script>
var coll = document.getElementsByClassName("finCollapsible");
var i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.maxHeight){
      content.style.maxHeight = null;
    } else {
      content.style.maxHeight = content.scrollHeight + "px";
    } 
  });
}
		//--implement 19/09/2019 START--/
$(document).ready(function() {

							  // Initialize the plugin
							  $('#finance_popup').popup();
							  $('#enquire_popup').popup();
							  $('#testdrive_popup').popup();
								$('#disclaimer_popup').popup();
							});
	enquire
			//--implement 19/09/2019 END--/
</script>	
	
	
<?php include('end.inc.php'); 
?>
