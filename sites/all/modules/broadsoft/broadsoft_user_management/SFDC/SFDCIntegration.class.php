<?php
module_load_include('php', 'broadsoft_user_management', '/SFDC/sfdcsoapclient/SforceEnterpriseClient');
global $NULL_DATE;

define("SFDC_USERID", "apiuserxchange@broadsoft.com");
define("SFDC_PASSWORD", "!y2e3k4n5o6m");
define("SFDC_SECURITY_TOKEN", "JM7P3ni5qIrlWxMc3VAeXNjQ5");

/** **********************************************************************************************
 *  ********** CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS ***********
 *  **********************************************************************************************
 */
class SFDCIntegration {
  var $sfdcConnection;
  var $sfdcConfig;     // array
  var $isConnectedAsAdmin;


  /* ======================================================================= */
  function SFDCIntegration() {
    $CONF_USED = 'dev';

    // ------------------------------------------------
    // -- production mode
    // ------------------------------------------------
    $conf['prod']['sfdc']['wsdlfile'] = realpath(dirname(__FILE__)) . "/sfdcsoapclient/sfdcBroadSoft.wsdl";
    $conf['prod']['sfdc']['userid'] = SFDC_USERID;
    $conf['prod']['sfdc']['password'] = SFDC_PASSWORD . SFDC_SECURITY_TOKEN;

    // ------------------------------------------------
    // -- test mode - almost production!
    // ------------------------------------------------
    $conf['test']['sfdc']['wsdlfile'] = realpath(dirname(__FILE__)) . "/sfdcsoapclient/sfdcBroadSoft.sb.wsdl";
    $conf['test']['sfdc']['userid'] = SFDC_USERID;
    $conf['test']['sfdc']['password'] = SFDC_PASSWORD . SFDC_SECURITY_TOKEN;

    // ------------------------------------------------
    // -- dev mode
    // ------------------------------------------------
    $conf['dev']['sfdc']['wsdlfile'] = $conf['prod']['sfdc']['wsdlfile'];
    $conf['dev']['sfdc']['userid'] = SFDC_USERID;
    $conf['dev']['sfdc']['password'] = SFDC_PASSWORD . SFDC_SECURITY_TOKEN;

    $this->sfdcConfig = $conf[$CONF_USED]['sfdc'];

    $this->isConnectedAsAdmin = FALSE;
    DebugPrintComplex(__LINE__, "SFDC-Init", $this->sfdcConfig);
  }


  /* ======================================================================= */

  function FetchDataFromSFDC($domain,
                             $CountryCodeFromForm,
                             &$udata,
                             &$SFDCValidNDA,
                             &$SFDCValidLICENSE,
                             &$SFDCAccountIsActiveOrJeopardy,
                             &$SFDCAccountNb,
                             &$SFDCCompanyXchangeAccessLevel,
                             &$fetchedRawRecords) {
    try {
      // Turn off all error reporting
      $debug = FALSE;

      $this->assertConnectedAsAdmin();
      $fetchedRawRecords = array();

      // Try to match the requested account with an existing customer on SFDC
      $SFDCNbMatchedAccounts = 0;
      $SFDCNbMatchedAccounts_withCountryCode = 0;

      $SFDCValidNDA = FALSE;
      $SFDCValidLICENSE = FALSE;
      $SFDCAccountIsActiveOrJeopardy = FALSE;
      $SFDCNDAExpiration = "";
      $SFDCAccountNb = "n.a.";
      $SFDCCompanyXchangeAccessLevel = "n.a.";

      // search for domain field first, then website if nothing found
      DebugPrint(__LINE__, "SFDC-Making lookup on sfdc for Account with domain = " . $domain);
      $fetchedRawRecords = $this->sfdcConnection->query($this->FetchDataFromSFDC_QueryString("Email_Domain__c like '%" . $domain . "%' "));
      DebugPrint(__LINE__, "SFDC-Nb record found = " . $fetchedRawRecords->size);

      // If no Email Domain is found then guess using the website domain
      if (!$fetchedRawRecords || $fetchedRawRecords->size == 0) {
        $webSiteGuess = 'www.' . $domain;
        DebugPrint(__LINE__, "SFDC-Making lookup on sfdc for Account with Website like " . $webSiteGuess);
        $fetchedRawRecords = $this->sfdcConnection->query($this->FetchDataFromSFDC_QueryString("Email_Domain__c like '%" . $webSiteGuess . "%' "));
        DebugPrint2("SFDC query - Match found against Website", $debug);
      }
      else {
        DebugPrint2("SFDC query - Match found against Email_Domain__c", $debug);
      }
      DebugPrint(__LINE__, "SFDC-Nb record found = " . $fetchedRawRecords->size);

      // Do we have at least one match?
      if ($fetchedRawRecords->size > 0) {
        // By iteration filter out the accounts
        $filteredAccountArray = array();

        $total_records = $fetchedRawRecords->records;
        foreach ($total_records as $record) {
          $accountEntry = array();
          // 1. Check the Email Domain
          // Very likely this account contains multiple accounts. Check if the email domain boundary is respected.
          $emailValid = TRUE;
          if (strlen($domain) != strlen($record->Email_Domain__c)) {
            // Split emails into multiple entries
            $nospace_domains = str_replace(" ", ",", $record->Email_Domain__c);
            $std_domains = str_replace(";", ",", $nospace_domains);
            $domains = explode(",", $std_domains);
            // Reset to false until we find a match
            $emailValid = FALSE;
            foreach ($domains as $d) {
              // Check if this is an email address (@)
              $email = explode("@", $d);
              // Only use the end part of the email if there is a full email address
              if (count($email) > 1) {
                $email[0] = $email[1];
              }
              // Exact match?
              if (strcmp(strtolower($email[0]), strtolower($domain)) == 0) {
                $emailValid = TRUE;
                break;
              }
            }
          }

          if ($emailValid) {
            // 2. Check the Finance Status
            $validStatus = FALSE;
            $accountStatus = (isset ($record->Account_Status__c) ? $record->Account_Status__c : "Inactive");
            if ($accountStatus == "Active" || preg_match('/jeopardy/i', $accountStatus)) {
              $validStatus = TRUE;
            }

            // 3. Check the Legal stuff
            // 3.1 Fetch the BSFT Contracts for this account.
            //$result = $this->getXchangeContracts($record->Id, array( 'NOT Account.Company__c'=>'iLinc',
            //                                                    'Account.Account_Status__c'=>array(
            //                                                      'Active',
            //                                                      'Jeopardy - Collections',
            //                                                      'Jeopardy - Maintenance' ),
            //                                                    'NOT Account.type'=>array(
            //                                                      'Vendor',
            //                                                      'Employee',
            //                                                      'Tech Partner' )));
            $legal_result = $this->getXchangeContracts($record->Id, array('Account.Id' => $record->Id));

            $validAgreement = FALSE;
            $validNDA = FALSE;

            if (isset($legal_result)) {
              // License Agreement first.
              // Step 1 Fetch the BSFT Contracts for this account.
              if (isset($legal_result[$record->Id]['License Agreement'])) {
                if ($legal_result[$record->Id]['License Agreement']['status'] == 'Activated') {
                  $validAgreement = TRUE;
                }
              }
              // Reseller Agreement as well.
              if (isset($legal_result[$record->Id]['Reseller Agreement'])) {
                if ($legal_result[$record->Id]['Reseller Agreement']['status'] == 'Activated') {
                  $validAgreement = TRUE;
                }
              }
              // Adoption Agreement is a form of reseller agreement.
              if (isset($legal_result[$record->Id]['Adoption Agreement'])) {
                if ($legal_result[$record->Id]['Adoption Agreement']['status'] == 'Activated') {
                  $validAgreement = TRUE;
                }
              }
              // BroadCloud PBX Services Agreement is a form of reseller agreement.
              if (isset($legal_result[$record->Id]['BroadCloud PBX Services Agreement'])) {
                if ($legal_result[$record->Id]['BroadCloud PBX Services Agreement']['status'] == 'Activated') {
                  $validAgreement = TRUE;
                }
              }

              // General Software Distribution and Reseller Agreement
              if (isset($legal_result[$record->Id]['General Software Distribution and Reseller Agreement'])) {
                if ($legal_result[$record->Id]['General Software Distribution and Reseller Agreement']['status'] == 'Activated') {
                  $validAgreement = TRUE;
                }
              }

              // Approved Affiliate Agreement
              if (isset($legal_result[$record->Id]['Approved Affiliate Agreement'])) {
                if ($legal_result[$record->Id]['Approved Affiliate Agreement']['status'] == 'Activated') {
                  $validAgreement = TRUE;
                }
              }

              // Step 2 Fetch the Xchange information from the account.
              if (!$validAgreement) {
                if (isset($legal_result[$record->Id]['license_agreement']) && $legal_result[$record->Id]['license_agreement']) {
                  $validAgreement = TRUE;
                }
              }

              // NDA in second.
              $gNDA_EXPIRATION_DAYS_ALLOWED = 7; // check if NDA expires in less than _ days
              // Step 1 Fetch the BSFT Contracts for this account.
              if (isset($legal_result[$record->Id]['NDA'])) {
                if ($legal_result[$record->Id]['NDA']['status'] == 'Activated') {
                  $expirationDate = (isset ($legal_result[$record->Id]['NDA']['contract_expiration_date']) ? $legal_result[$record->Id]['NDA']['contract_expiration_date'] : $NULL_DATE);
                  $almostExpired = GetDeltaDaysFromNow($expirationDate) <= $gNDA_EXPIRATION_DAYS_ALLOWED;

                  // Only if the BSFT Contracts is valid, otherwise we use the Xchange Info oon the account.
                  if (!$almostExpired) {
                    $validNDA = TRUE;
                  }
                }
              }
              // Step 2 Fetch the Xchange information from the account.
              if (!$validNDA) {
                if (isset($legal_result[$record->Id]['nda_received']) && $legal_result[$record->Id]['nda_received']) {
                  $expirationDate = (isset ($legal_result[$record->Id]['nda_expiration_date']) ? $legal_result[$record->Id]['nda_expiration_date'] : $NULL_DATE);
                  $almostExpired = GetDeltaDaysFromNow($expirationDate) <= $gNDA_EXPIRATION_DAYS_ALLOWED;

                  // Only if the BSFT Contracts is valid, otherwise we use the Xchange Info oon the account.
                  if (!$almostExpired) {
                    $validNDA = TRUE;
                  }
                }
              }
            }
            // 4. Check the Billing Country Code
            $validCountry = FALSE;
            dd($record);
            if (isset($record->ShippingCountry) && $record->ShippingCountry == $CountryCodeFromForm) {
              $record->BillingCountry = $CountryCodeFromForm;
              $validCountry = TRUE;
            }
            else {
              if (isset($record->BillingCountry) && $record->BillingCountry == $CountryCodeFromForm) {
                $validCountry = TRUE;
              }
              else {
                if (isset($record->Extra_Country_Coverage__c) && strlen($record->Extra_Country_Coverage__c) > 0) {
                  // parse the extra country code to a match with the country in question
                  // Split code into multiple entries
                  $nospace_codes = str_replace(" ", ",", $record->Extra_Country_Coverage__c);
                  $std_codes = str_replace(";", ",", $nospace_codes);
                  $codes = explode(",", $std_codes);
                  // Reset to false until we find a match
                  $validCountry = FALSE;
                  foreach ($codes as $billing_code) {
                    if ($billing_code == $CountryCodeFromForm) {
                      $record->BillingCountry = $CountryCodeFromForm;
                      $validCountry = TRUE;
                    }
                  }
                }
              }
            }

            // 5. Keep this account
            $accountEntry = array();
            $accountEntry['record'] = $record;
            $accountEntry['country'] = $validCountry;
            $accountEntry['countryCode'] = ($validCountry == TRUE) ? $record->BillingCountry : '';
            $accountEntry['status'] = $validStatus;
            $accountEntry['NDA'] = $validNDA;
            $accountEntry['Agreement'] = $validAgreement;
            $accountEntry['type'] = (isset ($record->Type) ? $record->Type : "?");
            $accountEntry['recordType'] = (isset ($record->RecordType) && isset ($record->RecordType->Name) ? $record->RecordType->Name : "?");
            $accountEntry['company'] = (isset ($record->Company__c) ? $record->Company__c : "?");

            $SFDCNbMatchedAccounts++;
            if ($validCountry) {
              $SFDCNbMatchedAccounts_withCountryCode++;
            }
            array_push($filteredAccountArray, $accountEntry);
          }
        }

        // If we have only one entry then there is a match
        $matchCount = count($filteredAccountArray);
        if ($matchCount == 0) {
          return 0;
        }
        elseif ($matchCount == 1) {
          // No match or a single match
          DebugPrint2("SFDC query - query domain ============ [" . $domain . "]", $debug);

          $this->FetchDataFromSFDC_ValidAccount($filteredAccountArray[0],
            $udata, $SFDCValidNDA, $SFDCValidLICENSE, $SFDCAccountIsActiveOrJeopardy, $SFDCAccountNb, $SFDCCompanyXchangeAccessLevel);

          return 1;
        }
        elseif ($matchCount > 1) {
          // If we have multiple entries then we need to process the list further.
          $countryArray = array();
          $typeArray = array();
          $companyArray = array();
          $recordTypeArray = array();
          $countCountryLegal = 0;
          $countCountry = 0;
          $countLegal = 0;
          $indexCountryLegal = -1;
          $indexCountry = -1;
          $indexLegal = -1;
          foreach ($filteredAccountArray as $index => $account) {
            $legal = $account['NDA'] || $account['Agreement'];
            $country = $account['country'];
            $countryCode = $account['countryCode'];
            // Discard any inactive accounts
            if ($account['status']) {
              if(isset($countryArray[$countryCode])) {
                $countryArray[$countryCode] = $countryArray[$countryCode] + 1;
              }

              if (!isset($typeArray[$countryCode])) {
                $typeArray[$countryCode] = array();
                $companyArray[$countryCode] = array();
                $recordTypeArray[$countryCode] = array();
              }
              if(isset($typeArray[$countryCode][$account['type']])) {
                $typeArray[$countryCode][$account['type']] = $typeArray[$countryCode][$account['type']] + 1;
              }
              if(isset($companyArray[$countryCode][$account['company']])) {
                $companyArray[$countryCode][$account['company']] = $companyArray[$countryCode][$account['company']] + 1;
              }
              if(isset($companyArray[$countryCode][$account['recordType']])) {
                $recordTypeArray[$countryCode][$account['recordType']] = $companyArray[$countryCode][$account['recordType']] + 1;
              }
              
              if ($country && $legal) {
                $countCountryLegal++;
                $indexCountryLegal = $index;
              }
              if ($legal) {
                $countLegal++;
                $indexLegal = $index;
              }
              if ($country) {
                $countCountry++;
                $indexCountry = $index;
              }
            }
          }
          // ********************************************
          // First PASS Try to match as close as possible
          if ($countCountryLegal == 1) {
            // Perfect Match (Country and Legal)
            $this->FetchDataFromSFDC_ValidAccount($filteredAccountArray[$indexCountryLegal],
              $udata, $SFDCValidNDA, $SFDCValidLICENSE, $SFDCAccountIsActiveOrJeopardy, $SFDCAccountNb, $SFDCCompanyXchangeAccessLevel);

            return 1;
          }
          else {
            if ($countCountryLegal > 0 &&
              count($countryArray) == 1 &&
              $matchCount == $countCountryLegal
            ) {
              // All the matches are from the same VALID country and legal entries
              // *** Discard the country information
              // *** Pick the last entry as the access level
              $this->FetchDataFromSFDC_ValidAccount($filteredAccountArray[$indexCountryLegal],
                $udata, $SFDCValidNDA, $SFDCValidLICENSE, $SFDCAccountIsActiveOrJeopardy, $SFDCAccountNb, $SFDCCompanyXchangeAccessLevel);

              return 1;
            }
            else {
              if ($countCountryLegal == 0 &&
                count($countryArray) == 1 &&
                $countCountry == 0 &&
                $countLegal > 0
              ) {
                // All the matches are from the same (wrong) country and some have legal valid entries
                // *** Discard the country information
                // *** Pick the last entry as the access level
                $this->FetchDataFromSFDC_ValidAccount($filteredAccountArray[$indexLegal],
                  $udata, $SFDCValidNDA, $SFDCValidLICENSE, $SFDCAccountIsActiveOrJeopardy, $SFDCAccountNb, $SFDCCompanyXchangeAccessLevel);

                return 1;
              }
              else {
                if ($matchCount == 2 &&
                  $countCountry == 0 &&
                  $countLegal == 1
                ) {
                  // We only have 2 matches and one has a legal valid entry
                  // *** Discard the country information
                  $this->FetchDataFromSFDC_ValidAccount($filteredAccountArray[$indexLegal],
                    $udata, $SFDCValidNDA, $SFDCValidLICENSE, $SFDCAccountIsActiveOrJeopardy, $SFDCAccountNb, $SFDCCompanyXchangeAccessLevel);

                  return 1;
                }
                else {
                  if ($countCountryLegal > 1) {
                    // *********************************************
                    // Second PASS
                    // Start checking Products and Account Type and Relationship
                    //
                    if (count($typeArray[$CountryCodeFromForm]) == 1 &&
                      count($companyArray[$CountryCodeFromForm]) == 1 &&
                      count($recordTypeArray[$CountryCodeFromForm]) == 1
                    ) {
                      // They all have the same company, account type, record type with email and Legal.
                      // *** this is a duplicate account
                      $this->FetchDataFromSFDC_ValidAccount($filteredAccountArray[$indexCountryLegal],
                        $udata, $SFDCValidNDA, $SFDCValidLICENSE, $SFDCAccountIsActiveOrJeopardy, $SFDCAccountNb, $SFDCCompanyXchangeAccessLevel);

                      return 1;
                    }
                  }
                }
              }
            }
          }
        }
      }
    } catch (SoapFault $e) {
      $SFDCNbMatchedAccounts = 0;
      $this->handleException($e->getMessage());
    } catch (Exception $e) {
      $SFDCNbMatchedAccounts = 0;
      $this->handleException($e->getMessage());
    }

    return $SFDCNbMatchedAccounts;
  }

  /* ======================================================================= */

  function assertConnectedAsAdmin() {
    if (!$this->isConnectedAsAdmin) {
      // Connect to SFDC
      try {
        $this->sfdcConnection = new SforceEnterpriseClient();
        $this->sfdcConnection->createConnection($this->sfdcConfig['wsdlfile']);
        @$this->sfdcConnection->login($this->sfdcConfig['userid'], $this->sfdcConfig['password']);

        $this->isConnectedAsAdmin = TRUE;

        DebugPrint(__LINE__, "SFDC: Attempting connection to server SUCCESS");
      } catch (SoapFault $e) {
        $this->handleException($e->getMessage());
      } catch (Exception $e) {
        $this->handleException($e->getMessage());
      }
    }
  }

  /* ======================================================================= */

  function handleException($exceptionStr) {
    DebugPrintComplex($exceptionStr); // this should be very infrequent - SFDC down. Could email xchangesupport...
  }

  /* ======================================================================= */

  private function FetchDataFromSFDC_QueryString($filter) {
    $query = "SELECT Type, Id, Name, Xchange_Access_Level__c, Website, BillingCountry, ShippingCountry, Extra_Country_Coverage__c, Email_Domain__c, Account_Status__c, Company__c, AccountNumber, RecordType.Name "
      . "FROM Account "
      . "WHERE Type <> 'Vendor'";
    if (!is_null($filter)) {
      $query .= " AND " . $filter;
    }
    return $query;
  }

  /* ======================================================================= */
  // udata by reference so also a return value
  // return: nb account found

  function getXchangeContracts($cid, $filters = NULL) {
    if ($this->sfdcConnection == NULL) {
      throw new Exception('Connection is null');
    }

    // Phase 1, fill out the Xchange information section
    $query = "SELECT  AccountNumber,
                      Name,
                      Id,
                      NDA_Received__c,
                      NDA_Type__c,
                      NDA_Expiration_Date__c,
                      License_Agreement__c 
              FROM Account ";
    $query = $this->applyQueryFilter($query, $filters);
    // Execute the QUERY
    $done = FALSE;
    $firstTimeIn = TRUE;
    $result = array();

    $noAccountNumberCount = 0;

    try {
      while (!$done) {
        if ($firstTimeIn) {
          $response = $this->sfdcConnection->query($query);
          $firstTimeIn = FALSE;
        }
        else {
          if ($response->queryLocator != NULL) {
            $response = $this->sfdcConnection->queryMore($response->queryLocator);
          }
        }
        if ($response->queryLocator == NULL || $response->done) {
          $done = TRUE;
        }

        foreach ($response->records as $record) {

          $accountNumber = (isset ($record->AccountNumber) ? $record->AccountNumber : "?");
          $accountID = (isset ($record->Id) ? $record->Id : "");
          $accountName = (isset ($record->Name) ? $record->Name : "?");

          if ($accountID == '?') {
            $noAccountNumberCount += 1;
            $accountID = "?" . $noAccountNumberCount;
          }

          $status = (isset ($record->Account_Status__c) ? $record->Account_Status__c : "?");

          $ndaType = (isset ($record->NDA_Type__c) ? $record->NDA_Type__c : "?");
          $ndaReceived = (isset ($record->NDA_Received__c) ? $record->NDA_Received__c : "?");
          $ndaExpirationDate = (isset ($record->NDA_Expiration_Date__c) ? $record->NDA_Expiration_Date__c : "?");
          $licenseAgreement = (isset ($record->License_Agreement__c) ? $record->License_Agreement__c : "?");

          if (!isset($result[$accountID])) {

            $result[$accountID] = array();
            $result[$accountID]['id'] = $accountNumber;
            $result[$accountID]['name'] = $accountName;
            $result[$accountID]['nda_type'] = $ndaType;
            $result[$accountID]['nda_received'] = $ndaReceived;
            $result[$accountID]['nda_expiration_date'] = $ndaExpirationDate;
            $result[$accountID]['license_agreement'] = $licenseAgreement;

            $result[$accountID]['url'] = 'https://na5.salesforce.com/' . $accountID;
          }

          // Phase 2, fill out the contract information
          $query = "SELECT  Name,
                            ContractNumber,
                            Status,
                            Id,
                            Expiration_Date__c,
                            Non_Standard_Initial_Term_Yrs__c
                    FROM Contract ";
          $query = $this->applyQueryFilter($query, $filters);
          // Execute the QUERY
          $done2 = FALSE;
          $firstTimeIn2 = TRUE;

          while (!$done2) {
            if ($firstTimeIn2) {
              $response2 = $this->sfdcConnection->query($query);
              $firstTimeIn2 = FALSE;
            }
            else {
              if ($response2->queryLocator != NULL) {
                $response2 = $this->sfdcConnection->queryMore($response2->queryLocator);
              }
            }
            if ($response2->queryLocator == NULL || $response2->done) {
              $done2 = TRUE;
            }
            if ($response2->size > 0) {
              foreach ($response2->records as $record) {
                $this->updateContractInformation($result, $record, $accountID);
              }
            }
          }
          // Phase 3, fill out the related parties information
          $query = "SELECT Contract__r.Name,
                           Contract__r.ContractNumber,
                           Contract__r.Status,
                           Contract__r.Id,
                           Contract__r.Expiration_Date__c,
                           Contract__r.Non_Standard_Initial_Term_Yrs__c
                    FROM Related_Party__c ";
          $related_parties_filter = array('Related_Party_Name__r.Id' => $cid);
          $query = $this->applyQueryFilter($query, $related_parties_filter);
          // Execute the QUERY
          $done2 = FALSE;
          $firstTimeIn2 = TRUE;

          while (!$done2) {
            if ($firstTimeIn2) {
              $response2 = $this->sfdcConnection->query($query);
              $firstTimeIn2 = FALSE;
            }
            else {
              if ($response2->queryLocator != NULL) {
                $response2 = $this->sfdcConnection->queryMore($response2->queryLocator);
              }
            }
            if ($response2->queryLocator == NULL || $response2->done) {
              $done2 = TRUE;
            }
            if ($response2->size > 0) {
              foreach ($response2->records as $record) {
                $this->updateContractInformation($result, $record->Contract__r, $accountID);
              }
            }
          }
        }
      }
    } catch (SoapFault $e) {
      $this->handleException($e->getMessage());
    } catch (Exception $e) {
      $this->handleException($e->getMessage());
    }
    return $result;
  }


  /* ======================================================================= *
  +-----+----------------------+
  | rid | name                 |
  +-----+----------------------+
  |   1 | anonymous user       |
  |   2 | authenticated user   |
  |  21 | Synergy Customer     |
  |  22 | M6 Customer          |
  |  23 | PacketSmart Customer |
  |  19 | Content Editor       |
  |  20 | BroadWorks Customer  |
  |  24 | System Partner       |
  |  25 | Interop Partner      |
  |  26 | Channel Partner      |
  |  18 | BroadSoft Employee   |
  |  29 | User Manager         |
  |  30 | Prospect             |
  |  31 | Limited user           |
  |  32 | Xcelerate Viewer       |
  |  33 | Restricted_TEMPORARY_1 |
  +-----+----------------------+
  */

  private function applyQueryFilter($query, $filters = NULL) {
    // Apply any filter to the request
    if ($filters != NULL) {
      $filterPos = 0;
      foreach ($filters as $filterType => $value) {
        if (!$filterPos) {
          $query .= " WHERE ";
        }
        else {
          $query .= " AND ";
        }
        // Handle the NOT case
        $isNot = FALSE;
        $realFilterType = $filterType;
        if (strncmp($filterType, 'NOT ', 4) == 0) {
          $isNot = TRUE;
          $realFilterType = substr($filterType, 4);
        }

        if (is_array($value)) {
          $query .= '(';
          for ($i = 0; $i < count($value); $i++) {
            if ($i != 0) {
              if ($isNot) {
                $query .= " AND ";
              }
              else {
                $query .= " OR ";
              }
            }
            $query .= "$realFilterType";
            if ($isNot) {
              $query .= "!";
            }
            $query .= "='" . $value[$i] . "'";
          }
          $query .= ')';
        }
        else {
          $query .= "$realFilterType";
          if ($isNot) {
            $query .= "!";
          }
          $query .= "='" . $value . "'";
        }
        $filterPos++;
      }
    }
    return $query;
  }

  /* ======================================================================= */
  // udata by reference so also a return value
  // return: true (if account found) or false

  function updateContractInformation(&$result, $record, $accountID) {
    $contractName = (isset ($record->Name) ? $record->Name : "?");
    $contractNumber = (isset ($record->ContractNumber) ? $record->ContractNumber : "?");
    $contractStatus = (isset ($record->Status) ? $record->Status : "");
    $contractExpirationDate = (isset ($record->Expiration_Date__c) ? $record->Expiration_Date__c : "?");
    $contractID = (isset ($record->Id) ? $record->Id : "");
    $contractInitialTerms = (isset ($record->Non_Standard_Initial_Term_Yrs__c) ? $record->Non_Standard_Initial_Term_Yrs__c : "?");

    if (($contractExpirationDate == '?') && ($contractInitialTerms == 'Indefinitely')) {
      // This is wihtout an expiration date.
      $contractExpirationDate = '2100-01-01';
    }
    // Only keep the latest of each
    $bUpdate = TRUE;
    if (isset($result[$accountID][$contractName])) {
      // Is the expiration date is a later date?
      if (($contractExpirationDate == '?') ||
        (($contractExpirationDate < $result[$accountID][$contractName]['contract_expiration_date']) && ($result[$accountID][$contractName]['contract_expiration_date'] != '?'))
      ) {
        $bUpdate = FALSE;
      }
      // ISAPP-793 union of all the agreement, not just the last one.
      // Check if there was already a valid contract to avoid overwriting it with a more recent incomplete one.
      if ($result[$accountID][$contractName]['status'] == 'Activated' && $contractStatus != 'Activated') {
        $bUpdate = FALSE;
      }
    }

    if ($bUpdate) {
      $result[$accountID][$contractName]['contract_number'] = $contractNumber;
      $result[$accountID][$contractName]['contract_expiration_date'] = $contractExpirationDate;
      $result[$accountID][$contractName]['status'] = $contractStatus;
      $result[$accountID][$contractName]['url'] = 'https://na5.salesforce.com/' . $contractID;
    }
    return $bUpdate;
  }


  /* ======================================================================= */
  // $strCase is 'Lead' or 'Contact'

  private function FetchDataFromSFDC_ValidAccount($r,
                                                  &$udata,
                                                  &$SFDCValidNDA,
                                                  &$SFDCValidLICENSE,
                                                  &$SFDCAccountIsActiveOrJeopardy,
                                                  &$SFDCAccountNb,
                                                  &$SFDCCompanyXchangeAccessLevel) {
    // Turn off all error reporting
    $debug = FALSE;
    $record = $r['record'];

    if (is_null($record)) {
      return;
    }

    DebugPrint2("SFDC query - Email_Domain__c         = [" . $record->Email_Domain__c . "]", $debug);
    DebugPrint2("SFDC query - Website                 = [" . $record->Website . "]", $debug);
    DebugPrint2("SFDC query - Type                    = [" . $record->Type . "]", $debug);
    DebugPrint2("SFDC query - Id                      = [" . $record->Id . "]", $debug);
    DebugPrint2("SFDC query - Name                    = [" . $record->Name . "]", $debug);
    DebugPrint2("SFDC query - Xchange_Access_Level__c = [" . $record->Xchange_Access_Level__c . "]", $debug);
    DebugPrint2("SFDC query - Account_Status__c       = [" . $record->Account_Status__c . "]", $debug);
    DebugPrint2("SFDC query - Company__c              = [" . $record->Company__c . "]", $debug);
    DebugPrint2("SFDC query - AccountNumber           = [" . $record->AccountNumber . "]", $debug);

    $accountName = (isset ($record->Name) ? $record->Name : "Not Assigned");
    if (isset($record->Xchange_Access_Level__c)) {
      $SFDCCompanyXchangeAccessLevel = $record->Xchange_Access_Level__c;
    }
    $SFDCAccountNb = (isset ($record->AccountNumber) ? $record->AccountNumber : "n.a.");

    $SFDCAccountIsActiveOrJeopardy = $r['status'];

    $udata['Lead']['Website'] = (isset ($record->Website) ? $record->Website : "Not Assigned");
    $udata['Lead']['Company'] = $accountName;
    $udata['Contact']['AccountId'] = $record->Id;

    $udata['Contact']['LeadSource'] = "Xchange Web Account Creation";

    $SFDCValidLICENSE = $r['Agreement'];
    DebugPrint2("SFDC query - License_Agreement__c    = [" . $SFDCValidLICENSE . "]", $debug);
    DebugPrint(__LINE__, "SFDC-License agreement is " . ($SFDCValidLICENSE ? ":" : "NOT") . " valid");

    $SFDCValidNDA = $r['NDA'];
    DebugPrint2("SFDC query - NDA_Received__c    = [" . $SFDCValidNDA . "]", $debug);
    DebugPrint(__LINE__, "SFDC-NDA is " . ($SFDCValidNDA ? ":" : "NOT") . " valid");

  }

  /* ======================================================================= 
  return: an array of the form:
    [0..]['AccID'] = Id
    [0..]['CID'] = AccountNumber
    [0..]['Status'] = Account_Status__c
    [0..]['XchangeAccessLevel'] = Xchange_Access_Level__c
    [0..]['Type'] = Type
    [0..]['Name'] = Company__c
    [0..]['Email_Domain'] = Email_Domain__c
    [0..]['Region'] = Region__c    
    [0..]['Team'] = Team__c
  */

  function MapSFDCAccessLevel2XchangeRID($SFDCXchangeAccessString) {
    $UserRid = 0;
    switch ($SFDCXchangeAccessString) {
      case 'System/Partner':
        $UserRid = 24;
        break;
      case 'Interop':
        $UserRid = 25;
        break;
      case 'Channel':
        $UserRid = 26;
        break;
      case 'PS':
        $UserRid = 23;
        break;
      case 'Synergy':
        $UserRid = 21;
        break;
      case 'Limited User':
        $UserRid = 31;
        break;
      case 'Prospect':
        $UserRid = 30;
        break;
      case 'M6':
        $UserRid = 22;
        break;
      case 'Customer':
        $UserRid = 20;
        break;
    }
    return $UserRid;
  }

  /* ======================================================================= */

  function FetchDataFromSFDC_fromContacts(&$udata, $emailDomain) {
    $this->assertConnectedAsAdmin();

    // Try to match the requested account with an existing customer on SFDC
    $query = "SELECT Email, AccountId FROM Contact WHERE Email like '%@" . $emailDomain . "%'";
    try {
      $response = $this->sfdcConnection->query($query);

      if ($response->size > 0) {
        foreach ($response->records as $record) {
          if (FetchDataFromSFDC_FromAccountId($udata, $record->AccountId)) {
            DebugPrint(__LINE__, "SFDC: Match found by another contact's email domain match from " . $emailDomain);
            return TRUE;
          }
        }
      }
    } catch (SoapFault $e) {
      $this->handleException($e->getMessage());
    } catch (Exception $e) {
      $this->handleException($e->getMessage());
    }

    return FALSE;
  }

  /* ======================================================================= */

  function CreateEntry($udata = array(), $strCase, $XchangeLoginGranted = FALSE) {
    $this->assertConnectedAsAdmin();

    $udata['Xchange_login_Granted__c'] = $XchangeLoginGranted;

    // $udata must be wrapped in an array, as create() can create many objects with a single API call
    try {
      $result = $this->sfdcConnection->create(array($udata), $strCase);
    } catch (SoapFault $e) {
      $this->handleException($e->getMessage());
    } catch (Exception $e) {
      $this->handleException($e->getMessage());
    }

    if (isset($result->success) && $result->success == 1) {
      DebugPrint(__LINE__, $strCase . " creation result: SUCCESS (" . $result->success . ")");
      return TRUE;
    }

    // dump result array into a string for logging purposes
    ob_start();
    var_dump($result);
    $res_string = ob_get_contents();
    ob_end_clean();

    DebugPrint(__LINE__, $strCase . " creation result: FAILURE (" . $result->success . ")");
    DebugPrint(__LINE__, $res_string);

    return $result;
  }

  /* ======================================================================= */

  function FetchAccountRecords($CID = NULL) {
    set_time_limit(60 * 5); // this operation can be quite long!
    $this->assertConnectedAsAdmin();

    $retArr = array();

    $query = "SELECT Type, Id, Name, Xchange_Access_Level__c, Account_Status__c, Team__c, Region__c, Company__c, AccountNumber, Email_Domain__c FROM Account";
    $where = "";
    if ($CID) {
      $where .= " WHERE AccountNumber='" . $CID . "'";
    }
    $query .= $where;

    $done = FALSE;
    $firstTimeIn = TRUE;
    try {
      $i = 0;
      while (!$done) {
        if ($firstTimeIn) {
          $response = $this->sfdcConnection->query($query);
          $firstTimeIn = FALSE;
        }
        else {
          if ($response->queryLocator != NULL) {
            $response = $this->sfdcConnection->queryMore($response->queryLocator);
          }
        }
        if ($response->queryLocator == NULL || $response->done) {
          $done = TRUE;
        }

        foreach ($response->records as $record) {
          // discard some accounts
          $skip = FALSE;
          if (!$record->AccountNumber) // CID is NULL for a lot of accounts
          {
            $skip = TRUE;
          }
          if (!$skip) {
            $retArr[$i]['AccID'] = $record->Id;
            $retArr[$i]['CID'] = $record->AccountNumber;
            $retArr[$i]['Status'] = $record->Account_Status__c;
            $retArr[$i]['Region'] = $record->Region__c;
            $retArr[$i]['Team'] = $record->Team__c;
            $retArr[$i]['XchangeAccessLevel'] = $record->Xchange_Access_Level__c;
            $retArr[$i]['Type'] = $record->Type;
            $retArr[$i]['Name'] = $record->Name;
            $retArr[$i]['Email_Domain'] = $record->Email_Domain__c;
            $i++;
          }
        }
      }
    } catch (SoapFault $e) {
      $this->handleException($e->getMessage());
    } catch (Exception $e) {
      $this->handleException($e->getMessage());
    }

    return $retArr;
  }

  /* ======================================================================= */

  function LookupUser($userEmail) {
    $this->assertConnectedAsAdmin();

    // extract domain from email
    $splitEmail = split('@', $userEmail);
    $user_domain = $splitEmail[1];

    // Try to match the requested account with an existing customer on SFDC
    $webSiteGuess = 'www.' . $user_domain;
    DebugPrint(__LINE__, "SFDC-Lookup from accounts with Website like " . $webSiteGuess);
    $query = "SELECT Type, Id, Name, Xchange_Access_Level__c, NDA_Received__c, License_Agreement__c, Website, Account_Status__c, NDA_Expiration_Date__c, Company__c, AccountNumber FROM Account WHERE Website like '%" . $webSiteGuess . "%'";
    try {
      $response = $this->sfdcConnection->query($query);
      DebugPrint(__LINE__, "Nb record found = " . $response->size);
      if ($response->size > 0) {
        foreach ($response->records as $record) {
          DebugPrint(__LINE__, "SFDC-Match found with : " . $record->Name);
          DebugPrint(__LINE__, "...NDA status         : " . $record->NDA_Received__c);
          DebugPrint(__LINE__, "...NDA expiration date: " . $record->NDA_Expiration_Date__c);
          DebugPrint(__LINE__, "...NDA delta days     = " . GetDeltaDaysFromNow($record->NDA_Expiration_Date__c));
        }
      }

      // Try to match the requested account with an existing customer on SFDC
      DebugPrint(__LINE__, "SFDC-Lookup from existing contacts with email like ...@" . $user_domain);
      $query = "SELECT Email, AccountId FROM Contact WHERE Email like '%@" . $user_domain . "%'";
      $response = $this->sfdcConnection->query($query);
      DebugPrint(__LINE__, "Nb record found = " . $response->size);
      if ($response->size > 0) {
        foreach ($response->records as $record) {
          DebugPrint(__LINE__, "SFDC-Match found with: " . $record->Email);
        }
      }
    } catch (SoapFault $e) {
      $this->handleException($e->getMessage());
    } catch (Exception $e) {
      $this->handleException($e->getMessage());
    }
  }

  /* ======================================================================= */

  function getXchangeProjectManagers($filters = NULL, $include_owners = FALSE) {
    if ($this->sfdcConnection == NULL) {
      throw new Exception('Connection is null');
    }

    $query = "SELECT  AccountNumber,
                      Name,
                      Owner.Name,
                      Owner.Email,
                      Id,
                      Type, 
                      RecordType.Name,
                      Company__c,
                      BillingCountry,
                      Account_Status__c,
                      Email_Domain__c,
                      Website,
                      NDA_Received__c,
                      NDA_Type__c,
                      NDA_Expiration_Date__c,
                      License_Agreement__c,
                      Xchange_Access_Level__c,
                      (SELECT TeamMemberRole, User.Name, User.Email 
                       FROM AccountTeamMembers 
                       WHERE (TeamMemberRole='Project Manager' OR TeamMemberRole='Project Manager – Alternate')) 
              FROM Account ";
    $query = $this->applyQueryFilter($query, $filters);
    // Execute the QUERY
    $done = FALSE;
    $firstTimeIn = TRUE;
    $result = array();

    try {
      while (!$done) {
        if ($firstTimeIn) {
          $response = $this->sfdcConnection->query($query);
          $firstTimeIn = FALSE;
        }
        else {
          if ($response->queryLocator != NULL) {
            $response = $this->sfdcConnection->queryMore($response->queryLocator);
          }
        }
        if ($response->queryLocator == NULL || $response->done) {
          $done = TRUE;
        }

        foreach ($response->records as $record) {

          $accountName = (isset ($record->Name) ? $record->Name : "?");
          $accountNumber = (isset ($record->AccountNumber) ? $record->AccountNumber : "?");

          $owner = 'unknown';
          $owner_email = 'unknown';
          if (isset($record->Owner)) {
            $owner = (isset($record->Owner->Name) ? $record->Owner->Name : '?');
            $owner_email = (isset($record->Owner->Email) ? $record->Owner->Email : '?');
          }

          if (isset ($record->AccountTeamMembers->records)) {
            foreach ($record->AccountTeamMembers->records as $role) {
              $pm = (isset($role->User->Name) ? $role->User->Name : '?');
              $pm_email = (isset($role->User->Email) ? $role->User->Email : '?');
              $newAccountNumber = $this->addXchangeProjectManagerEntry($result, $accountNumber, $accountName, $pm, $pm_email, TRUE);
              $this->updateAccountInformation($result, $record, $pm, $newAccountNumber);
            }
          }
          else {
            if ($include_owners) {
              $newAccountNumber = $this->addXchangeProjectManagerEntry($result, $accountNumber, $accountName, $owner, $owner_email, FALSE);
              $this->updateAccountInformation($result, $record, $owner, $newAccountNumber);
            }
            else {
              $newAccountNumber = $this->addXchangeProjectManagerEntry($result, $accountNumber, $accountName, 'unknown', 'unknown', FALSE);
              $this->updateAccountInformation($result, $record, 'unknown', $newAccountNumber);
            }
          }
        }
      }
    } catch (SoapFault $e) {
      $this->handleException($e->getMessage());
    } catch (Exception $e) {
      $this->handleException($e->getMessage());
    }
    return $result;
  }

  /* ======================================================================= */

  private function addXchangeProjectManagerEntry(&$result, $accountNumber, $accountName, $user, $user_email, $is_pm) {
    // Add the Account to the PM list
    if (!isset($result[$user])) {
      $result[$user] = array();
      $result[$user]['account'] = array();
    }

    $result[$user]['user_email'] = $user_email;
    $result[$user]['is_pm'] = $is_pm;
    if (!isset($result[$user]['count'])) {
      $result[$user]['count'] = 0;
    }
    $result[$user]['count'] += 1;
    if ($accountNumber == '?') {
      $accountNumber = "?" . $result[$user]['count'];
    }
    $result[$user]['account'][$accountNumber] = array();
    $result[$user]['account'][$accountNumber]['name'] = $accountName;

    return $accountNumber;
  }

  /* ======================================================================= */

  private function updateAccountInformation(&$result, $record, $user, $accountNumber) {
    $accountID = (isset ($record->Id) ? $record->Id : "");

    $accountType = (isset ($record->Type) ? $record->Type : "?");
    $accountRecordType = (isset ($record->RecordType) && isset ($record->RecordType->Name) ? $record->RecordType->Name : "?");
    $accountCompany = (isset ($record->Company__c) ? $record->Company__c : "?");
    $BillingCountry = (isset ($record->BillingCountry) ? $record->BillingCountry : "?");

    $status = (isset ($record->Account_Status__c) ? $record->Account_Status__c : "?");

    $emailDomain = (isset ($record->Email_Domain__c) ? $record->Email_Domain__c : "?");
    $website = (isset ($record->Website) ? $record->Website : "?");
    $ndaType = (isset ($record->NDA_Type__c) ? $record->NDA_Type__c : "?");
    $ndaReceived = (isset ($record->NDA_Received__c) ? $record->NDA_Received__c : "?");
    $ndaExpirationDate = (isset ($record->NDA_Expiration_Date__c) ? $record->NDA_Expiration_Date__c : "?");
    $licenseAgreement = (isset ($record->License_Agreement__c) ? $record->License_Agreement__c : "?");
    $xchangeAccessLevel = (isset ($record->Xchange_Access_Level__c) ? $record->Xchange_Access_Level__c : "?");

    // Add the Account to the PM list
    $result[$user]['account'][$accountNumber]['url'] = 'https://na5.salesforce.com/' . $accountID;

    $result[$user]['account'][$accountNumber]['account_type'] = $accountType;
    $result[$user]['account'][$accountNumber]['account_record_type'] = $accountRecordType;
    $result[$user]['account'][$accountNumber]['account_company'] = $accountCompany;
    $result[$user]['account'][$accountNumber]['billing_country'] = $BillingCountry;
    $result[$user]['account'][$accountNumber]['status'] = $status;
    $result[$user]['account'][$accountNumber]['email_domain'] = $emailDomain;
    $result[$user]['account'][$accountNumber]['website'] = $website;
    $result[$user]['account'][$accountNumber]['nda_type'] = $ndaType;
    $result[$user]['account'][$accountNumber]['nda_received'] = $ndaReceived;
    $result[$user]['account'][$accountNumber]['nda_expiration_date'] = $ndaExpirationDate;
    $result[$user]['account'][$accountNumber]['license_agreement'] = $licenseAgreement;
    $result[$user]['account'][$accountNumber]['xchange_access_level'] = $xchangeAccessLevel;

    // Array of actions required to make the Xchange User Request automated
    $result[$user]['account'][$accountNumber]['actions'] = array();

    // Rule 1: Email Domain
    if (!isset($record->Email_Domain__c) && !isset($record->Website)) {
      $result[$user]['account'][$accountNumber]['actions']['email'] = "[PM/Owner] Enter the Email Domain for the account";
    }

    // Rule 2: Billing Country
    if (!isset($record->BillingCountry)) {
      $result[$user]['account'][$accountNumber]['actions']['billing_country'] = "[Finance] Enter the Billing Country for the account";
    }

    // Rule 3: NDAs
    if (!$licenseAgreement && !$ndaReceived) {
      // TODO Add the calculation of the expiration date to the condition
      $result[$user]['account'][$accountNumber]['actions']['NDA'] = "[Legal] Update the NDA part of the Xchange information for the account";
    }

  }

  /* ======================================================================= */

  function getXchangeBadAccounts($filters = NULL, $bGroupedByPM = TRUE) {

    if ($this->sfdcConnection == NULL) {
      throw new Exception('Connection is null');
    }

    // $this->sfdcConnection->setQueryOptions( array('batchSize', 256) );
    $query = "SELECT  AccountNumber,
                      Name,
                      Id,
                      Type, 
                      RecordType.Name,
                      Company__c,
                      BillingCountry,
                      Account_Status__c,
                      Email_Domain__c,
                      Website,
                      NDA_Received__c,
                      NDA_Type__c,
                      NDA_Expiration_Date__c,
                      License_Agreement__c,
                      Xchange_Access_Level__c,
                      Owner.Name,
                      Owner.Email,
                      (SELECT TeamMemberRole, User.Name, User.Email 
                       FROM AccountTeamMembers 
                       WHERE TeamMemberRole='Project Manager' OR TeamMemberRole='Project Manager - Alternate')
              FROM Account ";
    $query = $this->applyQueryFilter($query, $filters);
    // Execute the QUERY
    $done = FALSE;
    $firstTimeIn = TRUE;
    $badResult = array();
    $goodResult = array();
    try {
      while (!$done) {
        if ($firstTimeIn) {
          $response = $this->sfdcConnection->query($query);
          $firstTimeIn = FALSE;
        }
        else {
          if ($response->queryLocator != NULL) {
            $response = $this->sfdcConnection->queryMore($response->queryLocator);
          }
        }
        if ($response->queryLocator == NULL || $response->done) {
          $done = TRUE;
        }

        foreach ($response->records as $record) {

          $accountNumber = (isset ($record->AccountNumber) ? $record->AccountNumber : "?");
          $accountName = (isset ($record->Name) ? $record->Name : "?");
          $accountID = (isset ($record->Id) ? $record->Id : "");

          $accountType = (isset ($record->Type) ? $record->Type : "?");
          $accountRecordType = (isset ($record->RecordType) && isset ($record->RecordType->Name) ? $record->RecordType->Name : "?");
          $accountCompany = (isset ($record->Company__c) ? $record->Company__c : "?");
          $BillingCountry = (isset ($record->BillingCountry) ? $record->BillingCountry : "?");

          $status = (isset ($record->Account_Status__c) ? $record->Account_Status__c : "?");

          $emailDomain = (isset ($record->Email_Domain__c) ? $record->Email_Domain__c : "?");
          $website = (isset ($record->Website) ? $record->Website : "?");
          $ndaType = (isset ($record->NDA_Type__c) ? $record->NDA_Type__c : "?");
          $ndaReceived = (isset ($record->NDA_Received__c) ? $record->NDA_Received__c : "?");
          $ndaExpirationDate = (isset ($record->NDA_Expiration_Date__c) ? $record->NDA_Expiration_Date__c : "?");
          $licenseAgreement = (isset ($record->License_Agreement__c) ? $record->License_Agreement__c : "?");
          $xchangeAccessLevel = (isset ($record->Xchange_Access_Level__c) ? $record->Xchange_Access_Level__c : "?");

          $owner = 'unknown';
          $owner_email = 'unknown';
          if (isset($record->Owner)) {
            $owner = (isset($record->Owner->Name) ? $record->Owner->Name : '?');
            $owner_email = (isset($record->Owner->Email) ? $record->Owner->Email : '?');
          }

          $pmFound = FALSE;
          $pm = 'unknown';
          $pm_email = 'unknown';
          if (isset ($record->AccountTeamMembers->records)) {
            $test1 = $record->AccountTeamMembers->records[0];
            $test2 = $record->AccountTeamMembers->records[1];
            if (strcmp($test1->TeamMemberRole, "Project Manager") == 0) {
              $pm = (isset($test1->User->Name) ? $test1->User->Name : '?');
              $pm_email = (isset($test1->User->Email) ? $test1->User->Email : '?');
              $pmFound = TRUE;
            }
            else {
              if (isset($test2)) {
                $pm = (isset($test2->User->Name) ? $test2->User->Name : '?');
                $pm_email = (isset($test2->User->Email) ? $test2->User->Email : '?');
                $pmFound = TRUE;
              }
            }
          }

          // Add the Account to the PM list
          $accountInfo = array();
          $accountInfo['name'] = $accountName;
          $accountInfo['PM'] = $pmFound;
          $accountInfo['url'] = 'https://na5.salesforce.com/' . $accountID;

          $accountInfo['account_type'] = $accountType;
          $accountInfo['account_record_type'] = $accountRecordType;
          $accountInfo['account_company'] = $accountCompany;
          $accountInfo['billing_country'] = $BillingCountry;
          $accountInfo['status'] = $status;
          $accountInfo['email_domain'] = $emailDomain;
          $accountInfo['website'] = $website;
          $accountInfo['nda_type'] = $ndaType;
          $accountInfo['nda_received'] = $ndaReceived;
          $accountInfo['nda_expiration_date'] = $ndaExpirationDate;
          $accountInfo['license_agreement'] = $licenseAgreement;
          $accountInfo['xchange_access_level'] = $xchangeAccessLevel;

          // Array of actions required to make the Xchange User Request automated
          $accountInfo['actions'] = array();

          // Rule 1: Email Domain
          if (!isset($record->Email_Domain__c) && !isset($record->Website)) {
            $accountInfo['actions']['email'] = "[PM] Enter the Email Domain for the account";
          }

          // Rule 2: Billing Country
          if (!isset($record->BillingCountry)) {
            $accountInfo['actions']['billing_country'] = "[Finance] Enter the Billing Country for the account";
          }

          // Rule 3: NDAs
          if (!$licenseAgreement && !$ndaReceived) {
            $accountInfo['actions']['NDA'] = "[Legal] Update the NDA part of the Xchange information for the account";
          }

          $user = ($pmFound ? $pm : $owner);

          if (!empty($accountInfo['actions'])) {
            // First build the base array for the PM
            if (!is_array($badResult[$user])) {
              $badResult[$user] = array();
              $badResult[$user]['user_email'] = ($pmFound ? $pm_email : $owner_email);
              $badResult[$user]['accounts'] = array();
            }
            if (isset($badResult[$user]['accounts'][$accountNumber])) {
              $accountNumber .= "?";
            }
            $badResult[$user]['accounts'][$accountNumber] = $accountInfo;
          }
          else {
            // First build the base array for the PM
            if (!is_array($goodResult[$user])) {
              $goodResult[$user] = array();
              $goodResult[$user]['user_email'] = ($pmFound ? $pm_email : $owner_email);
              $goodResult[$user]['accounts'] = array();
            }
            if (isset($goodResult[$user]['accounts'][$accountNumber])) {
              $accountNumber .= "?";
            }
            $goodResult[$user]['accounts'][$accountNumber] = $accountInfo;
          }
        }
      }

      // Only keep the PM with bad accounts
      $result = array();
      if ($bGroupedByPM) {
        $result['bad'] = $badResult;
        $result['good'] = $goodResult;
      }
      else {
        // Flatten the result
        foreach ($badResult as $user => $entry) {
          foreach ($entry['accounts'] as $accountNumber => $account) {
            $result[$accountNumber] = $account;
            $result[$accountNumber]['user'] = $user;
            $result[$accountNumber]['user_email'] = $entry['user_email'];
          }
        }
        foreach ($goodResult as $user => $entry) {
          foreach ($entry['accounts'] as $accountNumber => $account) {
            $result[$accountNumber] = $account;
            $result[$accountNumber]['user'] = $user;
            $result[$accountNumber]['user_email'] = $entry['user_email'];
          }
        }
      }
    } catch (SoapFault $e) {
      $this->handleException($e->getMessage());
    } catch (Exception $e) {
      $this->handleException($e->getMessage());
    }

    return $result;

  }

}

?>
