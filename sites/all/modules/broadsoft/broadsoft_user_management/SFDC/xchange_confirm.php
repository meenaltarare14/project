<?php
include_once('sfdcsoapclient/SforceEnterpriseClient.php');

include_once('config.php');
global $validConf;
global $gNDA_EXPIRATION_DAYS_ALLOWED;
global $developmentMode;	// defined in config.php
global $verbose;			// defined in config.php
$testingMode = false;

include_once('broadsoft_misc_php_utils.php');
global $NULL_DATE;
DebugStart();

include_once('LDAPIntegration.class.php');
include_once('SQLIntegration.class.php');

session_start();

// if we're not posting, fail
if($_SERVER['REQUEST_METHOD'] != 'POST')
{
  echo "Operation is not allowed.";
  die();
}

$redirectPage='/contactus/thankyou';
$whichCase="Unknown case! something went wrong";

/* ======================================================================= */
// Development mode options
if($developmentMode) 
{ 
	$redirectPage='';

	make_fake_data_TestCase_4();	// modifies _POST
//	$_POST['email'] 			= 'whoever@gmail.com';
//	$_POST['email'] 			= 'whoever@ericsson.com';

	//make_fake_data_TestCase_aaaaa();// modifies _POST

	$_POST['first_name'].=time(); 	// to make it unique in sfdc
	DebugPrint(__LINE__, true, "Test case is: ".$_POST['testcase']);
} 
elseif($_POST['00N70000002Hq0a']=="_aA_BcCE92g3H6i7I8jJk2K3l4L6M78914q5Qs9S12T34Uv78W9x1X23Y4zZ") // PASS-THROUGH FOR TESTING IN PRODUCTION, field is BroadSoft_Account_Manager
{
	global $conf;
	DebugPrint(__LINE__, true, "GOING IN TESTING MODE!");
	$testingMode = true;
	$developmentMode = true;
	$validConf['xchange']['email'] = $conf['test']['xchange']['email'];
	$verbose = true;
}
/* ======================================================================= */

validate_posted_data(); // Validate the posted information

// Check out the result of the validation and post an error if anything wrong was found
if(!empty($_SESSION['errors_xchange']))
{
	DebugPrint(__LINE__, true, "Validation error(s) in _POST!");
	DebugEnd();
	$_SESSION['fields_xchange'] = $_POST;
	header("Location: " . $_POST['returnurl']);
	return;
}
else
{
	Add2Log("Validated POST data as entered by user: \n".Array2Str2($_POST));

	try {
		$EMS_requested = false;
		if($_POST['00N70000002VJNL'] == 'Yes') 
		{
			$EMS_requested = true;
		}

		$myLdap = new LDAPIntegration($validConf['ldap']);

		$uid = $myLdap->MakeUID($_POST['first_name'], $_POST['last_name']);
		if (!$EMS_requested && $myLdap->isUserValidFromEmail($_POST['email']))
		{
			// user already has an account
			DebugPrint(__LINE__, true, "User email ".$_POST['email']." already associated to an account on LDAP server");
			redirectToPage('http://'.$validConf['xchange']['server'].'/GenericResultPages/SetPasswordSuccess?result=AccountAlreadyExists');		
			// ^^^ this will end the current script
		}
		elseif (!$EMS_requested && $myLdap->isUserValidFromUID($uid))
		{
			// user already has an account
			DebugPrint(__LINE__, true, "User email ".$_POST['email']." already associated to an account on LDAP server");
			redirectToPage('http://'.$validConf['xchange']['server'].'/GenericResultPages/SetPasswordSuccess?result=AccountAlreadyExists');		
			// ^^^ this will end the current script
		}
		else
		{
			// Connect to SFDC
			DebugPrint(__LINE__, true, "Attempting sfdc connection...");
			$mySforceConnection = new SforceEnterpriseClient();
			$mySforceConnection->createConnection($validConf['sfdc']['wsdlfile']);
			$mySforceConnection->login($validConf['sfdc']['userid'], $validConf['sfdc']['password']);
			DebugPrint(__LINE__, true, "Attempting sfdc connection SUCCESS");
/*========================================================================================*/
			// extract domain from email
			$splitEmail=split('@', $_POST['email']);
			$user_domain=$splitEmail[1];

			// globals
			$gSFDCNbMatchedAccounts			= 0;
			$gSFDCMatchedAccounts			= "";
			$gSFDCValidNDA					= false;
			$gSFDCValidLICENSE				= false;
			$gSFDCAccountIsActive			= false;
			$gSFDCNDAExpiration 			= "";
			$gSFDCAccountNb					= "n.a.";
			$gSFDCCompanyXchangeAccessLevel	= "n.a.";
			$gUserData; 

			$gUserData=MakeSFDCCompatibleUserData(); // from $_POST
			$gUserData=FetchDataFromSFDC($gUserData, $user_domain); // will enhance gUserData if match found on SFDC

			if($developmentMode) 
			{ 
				$gUserData['Lead']['Email']		= $validConf['xchange']['email']; // so that emails will go to developer
				$gUserData['Contact']['Email']	= $validConf['xchange']['email']; // so that emails will go to developer
				//DumpTestCaseDataFromSFDC();
			}

			$fullUserName=$gUserData['Lead']['FirstName']." ".$gUserData['Lead']['LastName'];
			DebugPrint(__LINE__, true, "User name is: ".$fullUserName);
			DebugPrint(__LINE__, ($developmentMode), "ValidNDA = ".($gSFDCValidNDA ? "true" : "false"));
			DebugPrint(__LINE__, ($developmentMode), "ValidLicense = ".($gSFDCValidLICENSE ? "true" : "false"));

			if($gSFDCNbMatchedAccounts==0)
			{
				// case A (flow chart) -------------------------------------------------------------------------------------
				$whichCase="Case A: Email domain not found";
				DebugPrint(__LINE__, true, "CASE A");
				$titleXchangeEmail="Xchange account request: manual intervention required. Company:".$gUserData['Lead']['Company'];
				Add2Log($titleXchangeEmail."\n--case A--\nUser data:\n".Array2Str2($gUserData['Lead']));
				if(CreateEntryOnSFDC($gUserData['Lead'], 'Lead')) {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site. His profile doesn't match an account on SFDC.\nA Lead has been created in SFDC for this user.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nHere is the user's data:";
				} else {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site. His profile doesn't match an account on SFDC.\nA Lead creation FAILED on SFDC for this user.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nHere is the user's data:";
				}
				SendEmail(	$titleXchangeEmail, 
							$bodyXchangeEmail."\n\n".Array2Str2($gUserData['Lead'])."- AccountNumber: ".$gSFDCAccountNb."\n",
							$validConf['xchange']['email'],
							"" /* no cc */);
			}
			elseif($gSFDCNbMatchedAccounts>1)
			{
				// case E (flow chart) -------------------------------------------------------------------------------------
				$whichCase="Case E: Multiple domain results";
				DebugPrint(__LINE__, true, "CASE E");
				$titleXchangeEmail="Xchange account request: manual intervention required. Company:".$gUserData['Lead']['Company'];
				Add2Log($titleXchangeEmail."\n--case E--\nUser data:\n".Array2Str2($gUserData['Lead']));
				if(CreateEntryOnSFDC($gUserData['Lead'], 'Lead')) { 
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site. His email domain matches multiple accounts on SFDC.\n\nHere are the matches:\n\n".$gSFDCMatchedAccounts."\nA Lead has been created in SFDC for this user.\n\nHere is the user's data:";
				} else {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site. His email domain matches multiple accounts on SFDC.\n\nHere are the matches:\n\n".$gSFDCMatchedAccounts."\nA Lead creation FAILED on SFDC for this user.\n\nTHere is the user's data:";
				}
				SendEmail(	$titleXchangeEmail, 
							$bodyXchangeEmail."\n\n".Array2Str2($gUserData['Lead'])."\n",
							$validConf['xchange']['email'],
							"" /* no cc */);
			}
			elseif(!($gSFDCValidNDA && GetDeltaDaysFromNow($gSFDCNDAExpiration)>$gNDA_EXPIRATION_DAYS_ALLOWED) && !$gSFDCValidLICENSE)
			{
				// case B (flow chart) -------------------------------------------------------------------------------------
				$whichCase="Case B: NDA or license agreement missing";
				DebugPrint(__LINE__, true, "CASE B");
				$titleXchangeEmail="Xchange account request: manual intervention required. Company:".$gUserData['Lead']['Company'];
				Add2Log($titleXchangeEmail."\n--case B--\nUser data:\n".Array2Str2($gUserData['Lead'])."- AccountNumber: ".$gSFDCAccountNb);
				if(CreateEntryOnSFDC($gUserData['Lead'], 'Lead')) {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site. His profile matches an account on SFDC but the associated NDA is invalid or (almost) expired.\nA Lead has been created in SFDC for this user.\nNDA forms have been sent the the user.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nHere is the user's data:";
				} else {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site. His profile matches an account on SFDC but the associated NDA is invalid or (almost) expired.\nA Lead creation FAILED on SFDC for this user.\nNDA forms have been sent the the user.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nHere is the user's data:";
				}
				SendEmail(	$titleXchangeEmail, 
							$bodyXchangeEmail."\n\n".Array2Str2($gUserData['Lead'])."- AccountNumber: ".$gSFDCAccountNb."\n",
							$validConf['xchange']['email'],
							"" /* no cc */);

				$titleUserEmail="Xchange NDA Expired or Not on File";
				$bodyUserEmail=	 "Dear ".$fullUserName.",\n\n"
								."Thank you for requesting a BroadSoft Xchange account. In order to activate your Xchange account, your company is required to have a current non-disclosure agreement (NDA) on file with BroadSoft.  Currently we are unable to process your request because your company's NDA agreement is either about to expire or we cannot locate it on file.\n\n"
								."Please have a representative from your organization sign and return the attached NDA and email a scanned copy to xchange@broadsoft.com once complete.  Once this is complete, we will process your request for a BroadSoft Xchange account within two business days.\n\n"
								."Do not hesitate to email xchange@broadsoft.com or contact your BroadSoft Account Representative with any questions or concerns.\n\n"
								."Regards,\n\n"
								."Xchange Support\n"
								."xchange@broadsoft.com\n";
				SendEmailWithAttachment(	$titleUserEmail, 
											$bodyUserEmail, 
											$gUserData['Lead']['Email'],
											$validConf['xchange']['email'],
											$validConf['NDAFileName']);
			}
			elseif(!$gSFDCAccountIsActive)
			{
				// case C (flow chart) -------------------------------------------------------------------------------------
				$whichCase="Case C: Account not active";
				DebugPrint(__LINE__, true, "CASE C");
				$titleXchangeEmail="Xchange account request: manual intervention required. Company:".$gUserData['Lead']['Company'];
				Add2Log($titleXchangeEmail."\n--case C--\nUser data:\n".Array2Str2($gUserData['Lead'])."- AccountNumber: ".$gSFDCAccountNb);
				if(CreateEntryOnSFDC($gUserData['Lead'], 'Lead')) { 
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site. His profile matches an account on SFDC but the account's status is not active.\nA Lead has been created in SFDC for this user.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nHere is the user's data:";
				} else {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site. His profile matches an account on SFDC but the account's status is not active.\nA Lead creation FAILED on SFDC for this user.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nHere is the user's data:";
				}
				SendEmail(	$titleXchangeEmail, 
							$bodyXchangeEmail."\n\n".Array2Str2($gUserData['Lead'])."- AccountNumber: ".$gSFDCAccountNb."\n",
							$validConf['xchange']['email'],
							"" /* no cc */);

				$titleUserEmail="Xchange Account Status is Under Review";
				$bodyUserEmail=	 "Dear ".$fullUserName.",\n\n"
								."Thank you for requesting a BroadSoft Xchange account. In order to activate your Xchange account we must have your company's credentials and payments up-to-date.  Due to your company's status your account is currently under review at this time.  Please contact your BroadSoft Account Representative to find out more information on this matter..\n\n"
								."Regards,\n\n"
								."Xchange Support\n"
								."xchange@broadsoft.com\n";
				SendEmail(	$titleUserEmail, 
							$bodyUserEmail, 
							$gUserData['Lead']['Email'],
							$validConf['xchange']['email']);
			}
			elseif($gUserData['Lead']['Requesting_an_EMS_Account__c'] == 'Yes')
			{
				// case F (flow chart) -------------------------------------------------------------------------------------
				$whichCase="Case F: EMS requested";
				DebugPrint(__LINE__, true, "CASE F");
				$titleXchangeEmail="Xchange account request (EMS requested): manual intervention required. Company:".$gUserData['Lead']['Company'];
				Add2Log($titleXchangeEmail."\n--case F--\nUser data:\n".Array2Str2($gUserData['Lead']));
				if(CreateEntryOnSFDC($gUserData['Lead'], 'Lead')) { 
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site and he is requesting an EMS account.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nA Lead has been created in SFDC for this user.\n\nHere is the user's data:";
				} else {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site and he is requesting an EMS account.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nA Lead creation FAILED on SFDC for this user.\n\nHere is the user's data:";
				}
				SendEmail(	$titleXchangeEmail, 
							$bodyXchangeEmail."\n\n".Array2Str2($gUserData['Lead'])."\n",
							$validConf['xchange']['email'],
							"" /* no cc */);
			}
			else
			{
				// case D (flow chart) -------------------------------------------------------------------------------------
				$whichCase="Case D: Successful";
				DebugPrint(__LINE__, true, "CASE D");
				// create account in xchange
				$token = GenerateXchangeAccountCredentials($gUserData['Contact']['Email']);

				// send credentials to user
				$titleUserEmail="Your BroadSoft Xchange account activation credentials";
				$bodyUserEmail=	"".
								"A BroadSoft Xchange account has been requested using your email address: ".$gUserData['Contact']['Email'].".\n".
								"\n".
								"If this was not you, you may safely do nothing.\n".
								"\n".
								"If you wish to proceed and activate your Xchange account, please visit this address:\n".
								"http://".$validConf['xchange']['server']."/GenericResultPages/SetPasswordForm".
								//............ all info to be passed to client when validating account ..............
								"?token=".$token.				
								"&first=".$gUserData['Contact']['FirstName'].				
								"&last=".$gUserData['Contact']['LastName'].				
								//...................................................................................
								"\n\n".
								"These credentials will expire in ".$validConf['xchange']['tokenLifeHr']." hours\n.";
				SendEmail(	$titleUserEmail, 
							$bodyUserEmail,
							$gUserData['Contact']['Email'],
							$validConf['xchange']['email']);

				// send xchange a notification regarding the account creation
				$titleXchangeEmail="Xchange account request: automatic processing notification. Company:".$gUserData['Lead']['Company'];
				Add2Log($titleXchangeEmail."\n--case D--\nUser data:\n".Array2Str2($gUserData['Contact'])."- AccountNumber: ".$gSFDCAccountNb);
				if(CreateEntryOnSFDC($gUserData['Contact'], 'Contact', true)) {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site and has been granted access to Xchange. His profile matches a valid account on SFDC.\nA Contact has been created in SFDC for this user.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nFYI only, here is the user's data:";
				} else {
					$bodyXchangeEmail="A user has requested an Xchange account from the Xchange web site and has been granted access to Xchange. His profile matches a valid account on SFDC.\nA Contact creation FAILED on SFDC for this user.\n\nThe company's Xchange access level is: ".$gSFDCCompanyXchangeAccessLevel."\n\nFYI only, here is the user's data:";
				}
				SendEmail(	$titleXchangeEmail, 
							$bodyXchangeEmail."\n\n".Array2Str2($gUserData['Contact'])."- AccountNumber: ".$gSFDCAccountNb."\n",
							$validConf['xchange']['email'],
							"" /* no cc */);

				$redirectPage='http://'.$validConf['xchange']['server'].'/GenericResultPages/SetPasswordSuccess?result=EmailSent';		
			}
		}
	} catch (Exception $e) {		
		DebugPrint(__LINE__, true, "Exception occured!");
		DebugPrint(__LINE__, true, var_export($e));
	}
}

if(!$testingMode)
{
	Add2CSV(array($_POST['email'], $gUserData['Lead']['Company'], $gSFDCCompanyXchangeAccessLevel, ($gSFDCValidNDA?"TRUE":"FALSE"), $whichCase));
}
else
{
	DebugEnd();
}
redirectToPage($redirectPage);
// ^^^ this will end the current script


/* ======================================================================= */
function GenerateXchangeAccountCredentials($email)
{
	// create token in DB
	global $validConf;
	$dbObject=new SQLIntegration($validConf['db']);
	$tok = $dbObject->createAndStoreToken($email);
	$dbObject->close();

	return $tok;
}


/* ======================================================================= */
// returns true if success, false otherwise
function CreateEntryOnSFDC($udata=array(), $strCase, $XchangeLoginGranted = false)
{
	global $mySforceConnection;

	$udata['Xchange_login_Granted__c'] = $XchangeLoginGranted;

	// create the entry
	// $udata must be wrapped in an array, as create() can create many objects with a single API call
	try {
		$result = $mySforceConnection->create(array($udata), $strCase);
	} catch (Exception $e) {
		DebugPrint(__LINE__, true, "Exception occured in ".$strCase." creation");
		DebugPrint(__LINE__, true, var_export($e));
	}

	// dump result array into a string for logging purposes
	ob_start();
	var_dump($result);
	$res_string = ob_get_contents();
	ob_end_clean();

	if(isset($result->success) && $result->success==1) {
		DebugPrint(__LINE__, true, $strCase." creation result: SUCCESS (".$result->success.")");
		return true;
	}

	// FAILURE! log something here
	DebugPrint(__LINE__, true, $strCase." creation result: FAILURE (".$result->success.")");
	DebugPrint(__LINE__, true, $res_string);

	return false;
}


/* ======================================================================= */
function DumpTestCaseDataFromSFDC()
{
	global $mySforceConnection;

	// Try to match the requested account with an existing customer on SFDC
	DebugPrint(__LINE__, true, "Making lookup on sfdc for Account with Domain containing ".$emailDomain);
	$query = "SELECT Id, Name, Domain__c, Xchange_Access_Level__c, NDA_Received__c, License_Agreement__c, Website, Account_Status__c, NDA_Expiration_Date__c, Company__c, AccountNumber FROM Account WHERE Name like '%Ericsson%'";
	$response = $mySforceConnection->query($query);

	DebugPrint(__LINE__, true, "====================Dumping Test Data from SFDC====================");
	DebugPrint(__LINE__, true, "Nb record found = ".$response->size);

	if ($response->size>0)
	{
		foreach ($response->records as $record) 
		{
			ob_start();
			var_dump($record);
			$res_string = ob_get_contents();
			ob_end_clean();
			DebugPrint(__LINE__, true, ".............................................................................");
			DebugPrint(__LINE__, true, "Record fetched: ".$res_string);
		}
	}
}


/* ======================================================================= */
function FetchDataFromSFDC($udata=array(), $emailDomain)
{
	global $mySforceConnection;
	global $gSFDCNbMatchedAccounts;
	global $gSFDCMatchedAccounts;
	global $gSFDCValidNDA;
	global $gSFDCValidLICENSE;
	global $gSFDCAccountIsActive;
	global $gSFDCNDAExpiration;
	global $gSFDCAccountNb;
	global $gSFDCCompanyXchangeAccessLevel;
	global $NULL_DATE;
	global $verbose;

	// Try to match the requested account with an existing customer on SFDC
	$gSFDCNbMatchedAccounts	= 0;
	$gSFDCValidNDA			= false;
	$gSFDCValidLICENSE		= false;
	$gSFDCAccountIsActive	= false;

	DebugPrint(__LINE__, $verbose, "Making lookup on sfdc for Account with Domain containing ".$emailDomain);
	$query = "SELECT Id, Name, Domain__c, Xchange_Access_Level__c, NDA_Received__c, License_Agreement__c, Website, Account_Status__c, NDA_Expiration_Date__c, Company__c, AccountNumber FROM Account WHERE Domain__c like '%".$emailDomain."%'";
	$response = $mySforceConnection->query($query);
	$uniqueRecordMatch=0;

	if ($response->size>0)
	{
		$exactSearchedRegExp='/(^|[ ,])'.$emailDomain.'([,]|$)/';
		foreach ($response->records as $record) 
		{
			// Warning: must analyse records found to eliminate longer matches
			//          e.g. searching LIKE 'ericsson.com' will also match 'felicsson.com'
			if(preg_match($exactSearchedRegExp, $record->Domain__c))
			{
				// just remember it for now
				$uniqueRecordMatch = $record;
				$gSFDCNbMatchedAccounts++;
				DebugPrint(__LINE__, $verbose, "Record matching: \"".$record->Domain__c."\" accepted, account name=\"".$record->Name."\" (looking for exactly \"".$emailDomain."\")");
				$gSFDCMatchedAccounts.=($record->Name."\n");
			} 
			else 
			{
				DebugPrint(__LINE__, $verbose, "Record matching: \"".$record->Domain__c."\" discarded (looking for exactly \"".$emailDomain."\")");
			}
		}

		DebugPrint(__LINE__, true, "Nb of exact matches found = ".$gSFDCNbMatchedAccounts);
	
		if($gSFDCNbMatchedAccounts==1)
		{
			ob_start();
			var_dump($uniqueRecordMatch);
			$res_string = ob_get_contents();
			ob_end_clean();
			DebugPrint(__LINE__, $verbose, "Record fetched: ".$res_string);

			$accountName   		= (isset ($uniqueRecordMatch->Name) ? $uniqueRecordMatch->Name : "Not Assigned");
			if(isset($uniqueRecordMatch->Xchange_Access_Level__c)) {
				$gSFDCCompanyXchangeAccessLevel = $uniqueRecordMatch->Xchange_Access_Level__c; 
			}
			$gSFDCNDAExpiration = (isset ($uniqueRecordMatch->NDA_Expiration_Date__c) ? $uniqueRecordMatch->NDA_Expiration_Date__c : $NULL_DATE);
			$webSite     		= (isset ($uniqueRecordMatch->Website) ? $uniqueRecordMatch->Website: "Not Assigned");
			$accountStatus  	= (isset ($uniqueRecordMatch->Account_Status__c) ? $uniqueRecordMatch->Account_Status__c: "Inactive");
			$gSFDCAccountNb  	= (isset ($uniqueRecordMatch->AccountNumber) ? $uniqueRecordMatch->AccountNumber: "n.a.");

			if($accountStatus == "Active") 
			{
				$gSFDCAccountIsActive = true;	
			}

			$udata['Lead']['Website'] 		= $webSite;

			$udata['Lead']['Company'] 		= $accountName;
			$udata['Contact']['AccountId']	= $uniqueRecordMatch->Id;

			$udata['Contact']['LeadSource']	= "Xchange Web Account Creation";

			if (isset($uniqueRecordMatch->NDA_Received__c) && $uniqueRecordMatch->NDA_Received__c)
			{
				$udata['Lead']['NDA_Status__c'] = 'Complete';
				$gSFDCValidNDA = true;
				DebugPrint(__LINE__, $verbose, "NDA is received"); 
			}
			else
			{
				DebugPrint(__LINE__, $verbose, "NDA is NOT received"); 
			}

			if (isset($uniqueRecordMatch->License_Agreement__c) && $uniqueRecordMatch->License_Agreement__c)
			{
				//$udata['Lead']['NDA_Status__c'] = 'Complete';
				$gSFDCValidLICENSE = true;
				DebugPrint(__LINE__, $verbose, "License agreement is valid"); 
			}
			else
			{
				DebugPrint(__LINE__, $verbose, "License agreement is NOT valid"); 
			}
		}
	}

	return $udata;
}


/* ======================================================================= */
// udata by reference so also a return value
// return: true (if account found) or false
function FetchDataFromSFDC_fromContacts(&$udata, $emailDomain) 
{
	global $mySforceConnection;
	global $developmentMode;

	// Try to match the requested account with an existing customer on SFDC

	$query = "SELECT Email, AccountId FROM Contact WHERE Email like '%@".$emailDomain."%'";
	$response = $mySforceConnection->query($query);

	if ($response->size>0) {
		foreach ($response->records as $record) {
			if(FetchDataFromSFDC_FromAccountId($udata, $record->AccountId)) {
				if($developmentMode) { 
					print "---------------------\n";
					print "(Match found by another contact's email domain match from ".$emailDomain.")\n";
					var_dump($udata); 
					print "\n---------------------\n";
				}
				return TRUE;
			}		
		}
	}

	return FALSE;
}


/* ======================================================================= */
function MakeSFDCCompatibleUserData()
{
	$tmp_user_data['Lead']['FirstName']  	= $form_state['values']['first_name'];
	$tmp_user_data['Contact']['FirstName'] 	= $form_state['values']['first_name'];

	$tmp_user_data['Lead']['LastName']  	= $form_state['values']['last_name'];
	$tmp_user_data['Contact']['LastName']  	= $form_state['values']['last_name'];

	$tmp_user_data['Lead']['Title']    		= $form_state['values']['title'];
	$tmp_user_data['Contact']['Title']    	= $form_state['values']['title'];

	$tmp_user_data['Lead']['Company']  		= $form_state['values']['company'];

	$tmp_user_data['Lead']['Email']    		= $form_state['values']['email'];
	$tmp_user_data['Contact']['Email']    	= $form_state['values']['email'];

	$tmp_user_data['Lead']['Phone']    		= $form_state['values']['phone'];
	$tmp_user_data['Contact']['Phone']    	= $form_state['values']['phone'];

	$tmp_user_data['Lead']['MobilePhone']	= $form_state['values']['mobile'];
	$tmp_user_data['Contact']['MobilePhone']= $form_state['values']['mobile'];

	$tmp_user_data['Lead']['City']    		= $form_state['values']['city'];
	$tmp_user_data['Contact']['MailingCity']= $form_state['values']['city'];

	$tmp_user_data['Lead']['Street']   			= $form_state['values']['address'];
	$tmp_user_data['Contact']['MailingStreet']	= $form_state['values']['address'];

	$tmp_user_data['Lead']['PostalCode']			= $form_state['values']['zip_postal_code'];
	$tmp_user_data['Contact']['MailingPostalCode']	= $form_state['values']['zip_postal_code'];

	$tmp_user_data['Lead']['Country']  			= $form_state['values']['Country'];
	$tmp_user_data['Contact']['MailingCountry']	= $form_state['values']['Country'];

	$tmp_user_data['Lead']['State']    			= $form_state['values']['state_prov'];
	$tmp_user_data['Contact']['MailingState']	= $form_state['values']['state_prov'];

	$tmp_user_data['Lead']['BroadSoft_Account_Manager__c'] = $form_state['values']['account_manager'];

	$tmp_user_data['Lead']['Relationship_with_BroadSoft__c'] = $form_state['values']['relationship'];

	$tmp_user_data['Lead']['Requesting_an_EMS_Account__c'] = $form_state['values']['requesting_EMS'];

	if ($tmp_user_data['Requesting_an_EMS_Account__c'] == 'Yes') {
		$tmp_user_data['Lead']['If_yes_Alt_Company_Email_Address__c']	=$form_state['values']['EMS_account_email'];
		$tmp_user_data['Contact']['If_yes_Alt_Company_Email_Address__c']=$form_state['values']['first_name'];
	}
	
	// add some extra fields
	$tmp_user_data['Lead']['RecordTypeID'] 	= '012700000001T61';

	return $tmp_user_data;
}


/* ======================================================================= */
// analyses content of $_POST
function validate_posted_data()
{
	global $validConf;

	// remove spaces if any in first & last names
    $_POST['first_name'] = str_replace (" ", "", $_POST['first_name']);
    $_POST['last_name']  = str_replace (" ", "", $_POST['last_name']);

	DebugPrint(__LINE__, true, "Validating POSTed data for user: ".$_POST['first_name']." ".$_POST['last_name']);
    // these fields are required
    $required = array(
		'first_name' => 'Please enter your first name.',
		'last_name' => 'Please enter your last name.',
		'company' => 'Please enter your company name.',
		'title' => 'Please enter your title.',
		'email' => 'Please enter your email address.',
		'phone' => 'Please enter your phone number.',
		'street' => 'Please enter your street address.',
		'city' => 'Please enter your city.',
		'state' => 'Please enter your state.',
		'country' => 'Please enter your country.',
//		'zip' => 'Please enter your zip code.',
		'00N70000002Hq0a' => 'Please enter your Broadsoft account manager.',
		'00N70000002Hq0k' => 'Please select your relationship with Broadsoft.',
		'00N70000002VJNL' => 'Please specify if you are requesting an EMS account.');

    $parenturl=$_REQUEST['parenturl'];
    $_SESSION['errors_xchange'] = $_SESSION['fields_xchange'] = array();

    foreach($_POST as $var => $val)
	{
		$val = is_array($val) ? array_map('trim', $val) : trim($val);

		// validate against $required
		if(array_key_exists($var, $required) && !$val)
		{
			$_SESSION['errors_xchange'][] = $required[$var];
		} 
		else 
		{
			$$var = $val;
		}
    }

    /* validate email + alternate email */
    if($_POST['email'] && (!preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/', $_POST['email'])))
    {
		$error = "Please enter a valid email address";
		$_SESSION['errors_xchange'][] = $error;
    }
    if($_POST['00N70000002VJNa'] && (!preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/', $_POST['00N70000002VJNa'])))
    {
		$error = "Please enter a valid alternate email address";
		$_SESSION['errors_xchange'][] = $error;
    }


	// check against restricted email domains
	$rejectDomains = explode(",", $validConf['rejectedDomains']);

	$splitEmail=split('@', $_POST['email']);
	$user_domain=$splitEmail[1];
	for($i=0;$i<count($rejectDomains);$i++)
	{
		if($user_domain==$rejectDomains[$i])
		{
			DebugPrint(__LINE__, true, "Email domain \"".$user_domain."\"matches restricted domain \"".$rejectDomains[$i]."\""); 
			$error = "Sorry, ".$user_domain." is not recognized as a valid business email address for a BroadSoft Xchange account. Please enter another email address.";
			$_SESSION['errors_xchange'][] = $error;

		}
	}

	$splitEmail=split('@', $_POST['00N70000002VJNa']);
	$user_domain=$splitEmail[1];
	for($i=0;$i<count($rejectDomains);$i++)
	{
		if($user_domain==$rejectDomains[$i])
		{
			DebugPrint(__LINE__, true, "Email domain \"".$user_domain."\"matches restricted domain \"".$rejectDomains[$i]."\""); 
			$error = "Sorry, ".$user_domain." is not recognized as a valid business email address for a BroadSoft Xchange account. Please enter another email address.";
			$_SESSION['errors_xchange'][] = $error;

		}
	}
}


/* ======================================================================= */
function make_fake_data_Tony()
{
	blankPOST();
	$_POST['testcase']			= "TestCase Tony";
	$_POST['first_name'] 		= 'Tony';
	$_POST['last_name'] 		= 'Pilote';
	$_POST['company'] 			= 'BroadSoft, Inc.';
	$_POST['title'] 			= 'Director Engineering - Platform Development';
	$_POST['email'] 			= 'tpilote@aaltevatel.com';
	$_POST['phone'] 			= '+15143332211';
	$_POST['mobile'] 			= '+15148924071';
	$_POST['street'] 			= '555 Frederic Philips, Suite 400';
	$_POST['city'] 				= 'St-Laurent';
	$_POST['state'] 			= 'Quebec';
	$_POST['country'] 			= 'Canada';
//	$_POST['zip'] 				= 'H4M 2X4';
	$_POST['00N70000002Hq0a']	= 'Tony Pilote'; 		// Account Manager Name	
	$_POST['00N70000002Hq0k'] 	= 'BroadSoft Employee'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
	$_POST['00N70000002VJNL'] 	= 'No'; 				// Are you requesting an EMS account? "Yes", "No"
	$_POST['00N70000002VJNa'] 	= 'tpilote@broadsoft.com';
}
function make_fake_data_TestCase_1()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 1";
	$_POST['company'] 			= 'Test 1 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test1.com'; 
//	$_POST['email'] 			= 'dummyuser2@altevatel.com'; // to test account nb
	$_POST['00N70000002Hq0k'] 	= 'Channel/Reseller'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
}
function make_fake_data_TestCase_2()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 2";
	$_POST['company'] 			= 'Test 2 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test2.com';
	$_POST['00N70000002Hq0k'] 	= 'Customer'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
}
function make_fake_data_TestCase_3()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 3";
	$_POST['company'] 			= 'Test 3 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test3.com';
	$_POST['00N70000002Hq0k'] 	= 'Customer'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
}
function make_fake_data_TestCase_4()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 4";
	$_POST['company'] 			= 'Test 4 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test4.com';
	$_POST['00N70000002Hq0k'] 	= 'Channel/Reseller'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
}
function make_fake_data_TestCase_5()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 5";
	$_POST['company'] 			= 'Test 5 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test5.com';
	$_POST['00N70000002Hq0k'] 	= 'InterOp/Tech Partner'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
}
function make_fake_data_TestCase_6()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 6";
	$_POST['company'] 			= 'Test 6 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test6.com';
	$_POST['00N70000002Hq0k'] 	= 'InterOp/Tech Partner'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
}
function make_fake_data_TestCase_7()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 7";
	$_POST['company'] 			= 'Test 7 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test7.com';
	$_POST['00N70000002Hq0k'] 	= 'Prospect'; 	// Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
}
function make_fake_data_TestCase_8()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 8";
	$_POST['company'] 			= 'Test 8 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test8.com';
	$_POST['00N70000002Hq0k'] 	= 'Customer'; 	// Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
}
function make_fake_data_TestCase_9()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 9";
	$_POST['company'] 			= 'Test 9 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@test9.com';
	$_POST['00N70000002Hq0k'] 	= 'Customer'; 	// Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
	$_POST['00N70000002VJNL'] 	= 'Yes'; 		// Are you requesting an EMS account? "Yes", "No"
}
function make_fake_data_TestCase_10()
{
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "TestCase 10";
	$_POST['company'] 			= 'st1 inc.';
	$_POST['email'] 			= $_POST['first_name'].'@st1.com';
	$_POST['00N70000002Hq0k'] 	= 'Prospect'; 	// Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
	$_POST['00N70000002VJNL'] 	= 'No'; 		// Are you requesting an EMS account? "Yes", "No"
}
function make_fake_data_TestCase_aaaaa()
{
	// data with low alphabetic values to help in sorting on sfdc
	make_fake_data_TestCase_Generic();
	$_POST['testcase']			= "aaaaa";
	$_POST['first_name'] 		= 'aaaaa';
	$_POST['last_name'] 		= 'aaaaa';
	$_POST['company'] 			= 'aaaaa inc.';
	$_POST['title'] 			= 'doing something';
	$_POST['email'] 			= $_POST['first_name'].'@aaaaa.com';
	$_POST['phone'] 			= '+15143332211';
	$_POST['mobile'] 			= '+15148924071';
	$_POST['street'] 			= '111 some street';
	$_POST['city'] 				= 'city';
	$_POST['state'] 			= 'region xyz';
	$_POST['country'] 			= 'Absurdistan';
//	$_POST['zip'] 				= 'H0H0H0';
	$_POST['00N70000002Hq0a']	= 'Tony Pilote'; // Account Manager Name	
	$_POST['00N70000002Hq0k'] 	= 'Customer'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
	$_POST['00N70000002VJNL'] 	= 'No'; // Are you requesting an EMS account? "Yes", "No"
	$_POST['00N70000002VJNa'] 	= 'slapierre@broadsoft.com';
}
function make_fake_data_TestCase_Generic()
{
	DebugPrint(__LINE__, true, "Making bogus test data");
	blankPOST();
	$_POST['first_name'] 		= 'Someone';
	$_POST['last_name'] 		= 'FamilyName';
	$_POST['company'] 			= 'Test 1 inc.';
	$_POST['title'] 			= 'doing something';
	$_POST['email'] 			= 'dummyuser1@test1.com';
	$_POST['phone'] 			= '+15143332211';
	$_POST['mobile'] 			= '+15148924071';
	$_POST['street'] 			= '111 some street';
	$_POST['city'] 				= 'city';
	$_POST['state'] 			= 'region xyz';
	$_POST['country'] 			= 'Absurdistan';
//	$_POST['zip'] 				= 'H0H0H0';
	$_POST['00N70000002Hq0a']	= 'Tony Pilote'; // Account Manager Name	
	$_POST['00N70000002Hq0k'] 	= 'Customer'; // Relationship "Customer", "Channel/Reseller", "InterOp/Tech Partner", Prospect", "BroadSoft Employee"
	$_POST['00N70000002VJNL'] 	= 'No'; // Are you requesting an EMS account? "Yes", "No"
	$_POST['00N70000002VJNa'] 	= 'slapierre@broadsoft.com';
}
function blankPOST()
{
	$_POST['first_name'] 		= '';
	$_POST['last_name'] 		= '';
	$_POST['company'] 			= '';
	$_POST['title'] 			= '';
	$_POST['email'] 			= '';
	$_POST['phone'] 			= '';
	$_POST['mobile'] 			= '';
	$_POST['street'] 			= '';
	$_POST['city'] 				= '';
	$_POST['state'] 			= '';
	$_POST['country'] 			= '';
	$_POST['zip'] 				= '';
	$_POST['recordType'] 		= '';	
	$_POST['00N70000002Hq0a']	= ''; 
	$_POST['00N70000002Hq0k'] 	= ''; 
	$_POST['00N70000002VJNL'] 	= ''; 
	$_POST['00N70000002VJNa'] 	= '';
}
?>
