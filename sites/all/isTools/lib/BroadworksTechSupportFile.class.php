<?php

include_once 'BaseHookEngine.class.php';


/**
 * Description of BW Tech Support File
 *
 * @author clabarre
 */

/**
 *
 */
class BroadworksTechSupportFile extends BaseHookEngine {

  const MAX_FILE_COUNT = 500;

  private $rootdir;

  /**
   *
   */
  public function __construct() {
    parent::__construct();
    $this->rootdir = '/tmp';
  }

  /**
   *
   * @param string $dir
   */
  function setTempDirectory($dir) {
    // Create the fodler if it does not exist.
    if (!file_exists($dir)) {
      mkdir($dir, 0777, TRUE);
    }
    $this->rootdir = $dir;
  }

  private function delTree($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  }

  /**
   * Utility is used to parse and actual tech-support file
   * Return: an array with
   * ['isTechSupport']
   * = true if all 4 following conditions are true
   * file contains line "BroadWorks Tech Support Report"
   * file contains line "Patch Name" providing patch name
   * file contains line "... hostname ... hostname ..."
   * file contains line "BroadWorks Software Version"
   * ['hasServiceUsageFile']
   * =true unless file contains "Service usage file has not been generated"
   * ['patches']
   * =array() with data contained in section "TechSupport has patch list"
   * ['licensing']
   * =array() with data contained in section "... service usage ..."
   **/
  function parseTechSupportFile($filename) {
    ini_set('memory_limit', '1024M'); // this will change the memory limit for this script-session only
    $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Entering parseTechSupportFile(" . $filename . ")", __LINE__);

    $result = array();
    $result['patches'] = array();
    $result['licensing'] = array();
    $result['isTechSupport'] = FALSE;
    $result['hasServiceUsageFile'] = TRUE;

    $lineArray = file($filename);

    $hasHeader = FALSE;
    $hasPatchList = FALSE;
    $hasHostname = FALSE;
    $hasBroadWorksSoftwareVersion = FALSE;

    $inPatchList = FALSE;
    $inLicensing = FALSE;
    $inHostname = FALSE;
    $inBroadWorksSoftwareVersion = FALSE;
    $inUname = FALSE;
    $tmpMatches = array();
    for ($i = 0; $i < count($lineArray); $i++) {
      if (preg_match('/Service usage file has not been generated/', $lineArray[$i])) {
        $result['hasServiceUsageFile'] = FALSE;
      }

      if (preg_match('/BroadWorks Tech Support Report/', $lineArray[$i])) {
        $hasHeader = TRUE;
        $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "TechSupport has header", __LINE__);
      }
      else {
        if (preg_match('/^Patch Name/', $lineArray[$i])) {
          $hasPatchList = TRUE;
          $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "TechSupport has patch list", __LINE__);
          $inPatchList = TRUE;
        }
        else {
          if (preg_match('/^listPatch command successfully executed/', $lineArray[$i])) {
            $inPatchList = FALSE;
          }
          else {
            if (preg_match('/\.\.\. service usage \.\.\./', $lineArray[$i])) {
              $inLicensing = TRUE;
            }
            else {
              if (preg_match('/^Number of trunk group/', $lineArray[$i])) {
                $result['licensing'][] = trim($lineArray[$i]);
                $inLicensing = FALSE;
              }
              else {
                if (preg_match('/BroadWorks Software Version/', $lineArray[$i])) {
                  $hasBroadWorksSoftwareVersion = TRUE;
                  $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "TechSupport has software version", __LINE__);
                  $inBroadWorksSoftwareVersion = TRUE;
                }
                else {
                  if (preg_match('/SWManager version: (.*)/', $lineArray[$i], $tmpMatches)) {
                    $result['SMVersion'] = trim($tmpMatches[1]);
                  }
                  else {
                    if (preg_match('/\.\.\. hostname \.\.\. hostname \.\.\./', $lineArray[$i])) {
                      $hasHostname = TRUE;
                      $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "TechSupport has hostname", __LINE__);
                      $inHostname = TRUE;
                    }
                    else {
                      if (preg_match('/\.\.\. uname \.\.\. uname \.\.\./', $lineArray[$i])) {
                        $inUname = TRUE;
                      }
                      else {
                        if (preg_match('/Date\/Time: (.*)/', $lineArray[$i], $tmpMatches)) {
                          $result['generatedOnDate'] = trim($tmpMatches[1]);
                        }
                        else {
                          if (preg_match('/^Customer Name: \[(C\d{5}).*\] (.*) - .* - .*/', $lineArray[$i], $tmpMatches)) {
                            //Customer Name: [C10088-A542] US Department of Justice - Production - Fri Sep 26 2014 @ 09:38
                            $result['CustomerName'] = array(trim($tmpMatches[1]), trim($tmpMatches[2]));
                          }
                          else {
                            if (preg_match('/^Customer ID: (C\d{5}).*/', $lineArray[$i], $tmpMatches)) {
                              //Customer ID: C10088
                              $result['CustomerID'] = trim($tmpMatches[1]);
                            }
                            else {
                              if (preg_match('/^=====================================/', $lineArray[$i])) {
                              }
                              else {
                                if ($inUname) {
                                  if (preg_match('/Linux/', $lineArray[$i])) {
                                    $result['os'] = 'Linux X86';
                                    $inUname = FALSE;
                                  }
                                  else {
                                    if (preg_match('/SunOS/', $lineArray[$i])) {
                                      $result['os'] = 'Solaris';
                                    }
                                    else {
                                      if (preg_match('/Machine = (.*)/', $lineArray[$i], $tmpMatches)) {
                                        if (preg_match('/86/', $tmpMatches[1])) {
                                          $result['os'] .= ' X86';
                                        }
                                        else {
                                          $result['os'] .= ' Sparc';
                                        }
                                        $inUname = FALSE;
                                      }
                                    }
                                  }
                                }
                                if ($inHostname) {
                                  $result['hostname'] = trim($lineArray[$i]);
                                  $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Hostname is (" . $result['hostname'] . ")", __LINE__);
                                  $inHostname = FALSE;
                                }
                                if ($inPatchList) {
                                  $matches = preg_split("/\s+/", trim($lineArray[$i]));
                                  $result['patches'][$matches[0]] = $matches[1];
                                }
                                if ($inLicensing) {
                                  if (preg_match('/^\.\.\./', $lineArray[$i])) {
                                    $inLicensing = FALSE;
                                  }
                                  else {
                                    $result['licensing'][] = trim($lineArray[$i]);
                                  }
                                }
                                if ($inBroadWorksSoftwareVersion) {
                                  if (preg_match('/Rel_/', $lineArray[$i])) {
                                    $inBroadWorksSoftwareVersion = FALSE;
                                    $matches = preg_split('/\s+/', preg_replace('/_/', ' ', preg_replace('/\t/', '', trim($lineArray[$i]))));
                                    $result['serverType'] = $matches[0];
                                    $result['release'] = $matches[3];
                                    $result['minorVersion'] = $matches[4];
                                    $this->generateBasicHook(BaseHookEngine::HOOK_TRACE,
                                      "Version is (" . $result['serverType'] . '-' . $result['release'] . '-' . $result['minorVersion'] . ")", __LINE__);
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    $result['isTechSupport'] = ($hasHeader && $hasPatchList && $hasHostname && $hasBroadWorksSoftwareVersion);

    $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Exiting parseTechSupportFile is Tech Support " . $result['isTechSupport'], __LINE__);

    return $result;
  }

  /**
   *
   * @param unknown $rootdir
   * @param unknown $uid
   * @param unknown $filename
   *
   * @return Ambigous <string, number>
   */
  function buildTechSupportPerUserTarget($rootdir, $uid, $filename) {
    // Put the uploaded file in a customer/monthly specific folder
    $target = $rootdir . "/" . $uid . "/" . date("MY");
    if (!is_dir($target)) {
      $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Making dir (" . $target . ")", __LINE__);
      mkdir($target, 0775, TRUE);
    }

    // Enforce uniqueness of the file name
    $target .= "/" . $filename;
    if (is_file($target)) {
      for ($i = 0; $i < BroadworksTechSupportFile::MAX_FILE_COUNT; $i++) {
        if (!is_file($target . $i)) {
          $target .= $i;
          break;
        }
      }
    }
    return $target;
  }

  /**
   *
   * @param unknown $filename
   * @param unknown $srcFile
   *
   * @return string|NULL|unknown
   */
  function processCompressedFile(&$filename, $srcFile) {

    // Support for: .zip, .gz, .tar.gz, .tgz
    // *** Not supporting RAR files
    //
    // Returned the first file of a compressed file.

    // Uncompress the file if necessary
    if (preg_match('/(.*)\.tgz/i', $filename, $tmpMatches) ||
      preg_match('/(.*)\.tar\.gz/i', $filename, $tmpMatches)
    ) {

      $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Untarring-unzipping and Writing attached file (" . $filename . ") to disk under (" . $srcFile . ")",
        __LINE__);

      $newSrcFile = $srcFile . '.tar.gz';
      if (copy($srcFile, $newSrcFile) === FALSE) {
        $this->generateBasicHook(BaseHookEngine::HOOK_ERROR, "Cannot Copy (" . $srcFile . ") to (" . $newSrcFile . ")", __LINE__);
      }
      // unarchive from the tar
      $dir = $this->rootdir . "/tartmp";
      if (!file_exists($dir)) {
        mkdir($dir, 0777, TRUE);
      }
      $res = FALSE;
      try {
        // decompress from gz
        $p = new PharData($newSrcFile);
        $newPhar = $p->decompress(); // creates /path/to/my.tar
        $pathinfo = pathinfo($newSrcFile);
        $tarFile = $pathinfo['dirname'] . '/' . $pathinfo['filename'];
        $phar = new PharData($tarFile);
        $res = $phar->extractTo($dir);
      } catch (Exception $e) {
        // Corruption, errors, etc...
        unlink($newSrcFile);
        return $srcFile;
      }

      $tmpFile2 = NULL;
      if ($res === TRUE) {
        $h = opendir($dir); //Open the current directory
        $firstFile = NULL;
        while (FALSE !== ($entry = readdir($h))) {
          if ($entry != '.' && $entry != '..') { //Skips over . and ..
            if (is_file($dir . '/' . $entry)) {
              $firstFile = $dir . '/' . $entry; //Do whatever you need to do with the file
              break; //Exit the loop so no more files are read
            }
            elseif (is_dir($dir . '/' . $entry)) {
              $h = opendir($dir . '/' . $entry); //Open the current directory
              $dir = $dir . '/' . $entry;
            }
          }
        }

        $tmpFile2 = tempnam($this->rootdir, "TMPr2");
        $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Copy (" . $firstFile . ") to (" . $tmpFile2 . ")", __LINE__);
        if (copy($firstFile, $tmpFile2) === FALSE) {
          $this->generateBasicHook(BaseHookEngine::HOOK_ERROR, "Cannot Copy (" . $firstFile . ") to (" . $tmpFile2 . ")", __LINE__);
        }
      }
      else {
        $this->generateBasicHook(BaseHookEngine::HOOK_ERROR, "Cannot unzip (" . $filename . ") to disk under (" . $newSrcFile . ")", __LINE__);
      }
      $filename = $tmpMatches[1];
      unlink($tarFile);
      unlink($newSrcFile);
      $this->delTree($dir);
      $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Returning (" . $tmpFile2 . ")", __LINE__);
      clearstatcache();
      return $tmpFile2;

    }
    else {
      if (preg_match('/(.*)\.gz/i', $filename, $tmpMatches)) {
        $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Gunzipping and Writing attached file (" . $filename . ") to disk under (" . $srcFile . ")",
          __LINE__);

        $realFile = $tmpMatches[1];
        $lines = gzfile($srcFile);
        $tmpFile2 = tempnam($this->rootdir, "TMPr2");
        $handle2 = fopen($tmpFile2, "w");
        foreach ($lines as $line) {
          fwrite($handle2, $line);
        }
        fclose($handle2);
        $filename = $realFile;
        // Update the current file
        $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Returning (" . $tmpFile2 . ")", __LINE__);
        return $tmpFile2;

      }
      else {
        if (preg_match('/(.*)\.zip/i', $filename, $tmpMatches)) {
          $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Unzipping and Writing attached file (" . $filename . ") to disk under (" . $srcFile . ")",
            __LINE__);

          $zip = new ZipArchive;
          $res = $zip->open($srcFile);
          $dir = $this->rootdir . "/ziptmp";
          if (!file_exists($dir)) {
            mkdir($dir, 0777, TRUE);
          }

          $tmpFile2 = NULL;
          if ($res === TRUE) {
            $zip->extractTo($dir);
            $zip->close();
            $h = opendir($dir); //Open the current directory
            $firstFile = NULL;
            while (FALSE !== ($entry = readdir($h))) {
              if ($entry != '.' && $entry != '..') { //Skips over . and ..
                if (is_file($dir . '/' . $entry)) {
                  $firstFile = $dir . '/' . $entry; //Do whatever you need to do with the file
                  break; //Exit the loop so no more files are read
                }
                elseif (is_dir($dir . '/' . $entry)) {
                  $h = opendir($dir . '/' . $entry); //Open the current directory
                  $dir = $dir . '/' . $entry;
                }
              }
            }

            $tmpFile2 = tempnam($this->rootdir, "TMPr2");
            $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Copy (" . $firstFile . ") to (" . $tmpFile2 . ")", __LINE__);
            if (copy($firstFile, $tmpFile2) === FALSE) {
              $this->generateBasicHook(BaseHookEngine::HOOK_ERROR, "Cannot Copy (" . $firstFile . ") to (" . $tmpFile2 . ")", __LINE__);
            }
          }
          else {
            $this->generateBasicHook(BaseHookEngine::HOOK_ERROR, "Cannot unzip (" . $filename . ") to disk under (" . $srcFile . ")", __LINE__);
          }
          $filename = $tmpMatches[1];
          $this->delTree($dir);
          $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Returning (" . $tmpFile2 . ")", __LINE__);
          clearstatcache();
          return $tmpFile2;
        }
      }
    }
    $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, "Raw File Returning (" . $srcFile . ")", __LINE__);
    return $srcFile;
  }

  /**
   *
   * @param array $parsedTechSupport
   *
   * @return Ambigous <NULL, unknown>
   */
  static function getCustomerID($parsedTechSupport) {
    $customerid = NULL;
    if (!empty($parsedTechSupport)) {
      if (!empty($parsedTechSupport['CustomerID'])) {
        $customerid = $parsedTechSupport['CustomerID'];
      }
      else {
        if (!empty($parsedTechSupport['CustomerName'])) {
          $customerid = $parsedTechSupport['CustomerName'][0];
        }
      }
    }
    return $customerid;
  }

  /**
   *
   * @param array $parsedTechSupport
   *
   * @return Ambigous <NULL, unknown>
   */
  static function getHostname($parsedTechSupport) {
    $hostname = NULL;
    if (!empty($parsedTechSupport) && !empty($parsedTechSupport['hostname'])) {
      $hostname = $parsedTechSupport['hostname'];
    }
    return $hostname;
  }


}


