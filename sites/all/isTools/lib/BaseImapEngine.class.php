<?php

include_once 'BaseHookEngine.class.php';
/**
 * IMAP Inbox scanner
 *
 * @author clabarre
 */

/**
 *
 */
class BaseImapEngine extends BaseHookEngine {

  private $rootdir;
  private $mailServer;
  private $mailUserAccount;
  private $mailUserPassword;

  const HOOK_VALIDATE_EMAIL = 'validateEmail';
  const HOOK_VALIDATE_ATTACHMENT = 'validateAttachment';
  const HOOK_EXECUTE_EMAIL = 'executeEmail';
  const HOOK_EXECUTE_ATTACHMENTS = 'executeAttachments';

  /**
   *
   * @param string $server
   * @param string $username
   * @param string $password
   */
  public function __construct($server, $username, $password) {
    parent::__construct();

    $this->rootdir = '/tmp';
    $this->mailServer = $server;
    $this->mailUserAccount = $username;
    $this->mailUserPassword = $password;
  }

  /**
   * Hooks: (base +)
   *   HOOK_VALIDATE_EMAIL
   *   HOOK_VALIDATE_ATTACHMENT
   *   HOOK_EXECUTE_EMAIL
   *   HOOK_EXECUTE_ATTACHMENTS
   *
   * @param string $type
   * @param string $newHook
   */
  function addHook($type, $newHook) {
    parent::addHook($type, $newHook);
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

  /**
   *
   * @param unknown $mbox
   * @param unknown $mid
   * @param unknown $p
   * @param unknown $partno
   *
   * @return multitype:string NULL
   */
  private function getAttachmentPart($mbox, $mid, $p, $partno) {
    // Utility used to parse the information for a single message from the mail server
    $attachments = array();

    $data = imap_fetchbody($mbox, $mid, $partno);

    if ($p->encoding == 4) {
      $data = quoted_printable_decode($data);
    }
    else {
      if ($p->encoding == 3) {
        $data = base64_decode($data);
      }
    }

    // get all parameters, like charset, filenames of attachments, etc.
    $params = array();
    if ($p->parameters) {
      foreach ($p->parameters as $x) {
        $params[strtolower($x->attribute)] = $x->value;
      }
    }
    if (isset($p->dparameters)) {
      foreach ($p->dparameters as $x) {
        $params[strtolower($x->attribute)] = $x->value;
      }
    }

    // Any part with a filename is an attachment,
    // so an attached text file (type 0) is not mistaken as the message.
    if (isset($params['filename']) || isset($params['name'])) {
      // filename may be given as 'Filename' or 'Name' or both
      $attachments['filename'] = isset($params['filename']) ? $params['filename'] : $params['name'];

      // filename may be encoded, so see imap_mime_header_decode()
      $attachments['data'] = $data;  // this is a problem if two files have same name
      $attachments['type'] = $p->type;
      $attachments['encoding'] = $p->encoding;
      $attachments['subtype'] = $p->subtype;
    }

    return $attachments;
  }

  /**
   *
   * @param unknown $msg
   * @param number  $code
   */
  private function generateError($msg, $code = 0) {
    $this->generateBasicHook(BaseHookEngine::HOOK_ERROR, $msg, $code);
  }

  /**
   *
   * @param unknown $msg
   * @param number  $code
   */
  private function generateTrace($msg, $code = 0) {
    $this->generateBasicHook(BaseHookEngine::HOOK_TRACE, $msg, $code);
  }

  /**
   *
   * @param unknown $attachmentArray
   *
   * @return boolean
   */
  private function validateAttachment(&$attachment) {
    return $this->generateValidateHook(BaseImapEngine::HOOK_VALIDATE_ATTACHMENT, $attachment);
  }

  /**
   *
   * @param unknown $s
   */
  private function validateEmail(&$s) {
    return $this->generateValidateHook(BaseImapEngine::HOOK_VALIDATE_EMAIL, $s);
  }

  /**
   *
   * @param unknown $attachments
   *
   * @return boolean
   */
  private function executeAttachments(&$attachments) {
    return $this->generateExecuteHook(BaseImapEngine::HOOK_EXECUTE_ATTACHMENTS, $attachments);
  }

  /**
   *
   * @param unknown $s
   *
   * @return boolean
   */
  private function executeEmail(&$s) {
    return $this->generateExecuteHook(BaseImapEngine::HOOK_EXECUTE_EMAIL, $s);
  }

  /**
   *
   * @param string $deleteProcessedMessage
   */
  function processInbox($deleteProcessedMessage = FALSE) {

    // Open the connection to the mail server
    $mbox = imap_open($this->mailServer, $this->mailUserAccount, $this->mailUserPassword);
    if (!$mbox) {
      $this->generateError("The Online Patch Advisor cannot access the email inbox for account " . $this->mailUserAccount, __LINE__);
      return;
    }

    // Process each message
    $numMsg = imap_num_msg($mbox);

    $this->generateTrace("Inbox of (" . $this->mailUserAccount . ") has " . $numMsg . " messages", __LINE__);

    $msgs = imap_sort($mbox, SORTDATE, 1, SE_UID);

    foreach ($msgs as $msguid) {

      $msgno = imap_msgno($mbox, $msguid);
      $header = imap_headerinfo($mbox, $msgno);

      $attachments = array('header' => $header);
      // Retrieve the email complex information
      $s = imap_fetchstructure($mbox, $msgno);
      if ($this->validateEmail($s)) {
        // Contains desirable contents
        foreach ($s->parts as $partno0 => $p) {
          $att = $this->getAttachmentPart($mbox, $msgno, $p, $partno0 + 1);
          // Body is a part
          if (!empty($att)) {
            // Create a temp file to process
            $tmpFile = tempnam($this->rootdir, "TMPr1");
            $this->generateTrace("Creating temp file " . $tmpFile . " for input file " . $att['filename'], __LINE__);
            $handle = fopen($tmpFile, "w");
            fwrite($handle, $att['data']);
            fclose($handle);

            // No need for the data anymore
            unset($att['data']);

            // Pass the temp file as input to the validation
            $att['filepath'] = $tmpFile;
            if ($this->validateAttachment($att)) {

              if (empty($att['is_validated'])) {
                $this->generateError("Cannot modify the attachment array", __LINE__);
              }

              // Check if the file was decompressed to a new temp file
              if ($tmpFile != $att['filepath']) {
                // We can get rid of the original tmp file.
                $this->generateTrace("Unlinking TMPr1 " . $tmpFile, __LINE__);
                $wasDeleted = unlink($tmpFile);
                if (!$wasDeleted) {
                  $this->generateError("Was not able to unlink " . $tmpFile, __LINE__);
                }
              }

              $this->generateTrace("Attachment was valid " . $att['filename'], __LINE__);
              // Add it to the process list
              $attachments[] = $att;
            }
          }
        }
      }
      else {
        $this->generateTrace("Email is not valid, discard it ", __LINE__);
        continue;
      }

      if (!$this->executeEmail($s)) {
        $this->generateTrace("Failure to process email from " . $header->subject, __LINE__);
      }
      // Process ALL attachments at once
      if (!$this->executeAttachments($attachments)) {
        $this->generateTrace("Failure to process attachments from " . $header->subject, __LINE__);
      }

      // Unlink all temp files
      foreach ($attachments as $index => $att) {
        if ($index !== 'header' || is_numeric($index)) {
          // Delete temp files
          $this->generateTrace("Unlinking All " . $att['filepath'], __LINE__);
          $wasDeleted = unlink($att['filepath']);
          if (!$wasDeleted) {
            $this->generateError("Was not able to unlink " . $att['filepath'], __LINE__);
          }
        }
      }

      if ($deleteProcessedMessage) {
        $this->generateTrace("Deleting the message from the inbox", __LINE__);
        $uid = imap_uid($mbox, $msgno);
        // Assuming gmail accounts
        imap_mail_move($mbox, "{$uid}", '[Gmail]/Trash', CP_UID);
        imap_delete($mbox, $uid, FT_UID);
      }
    }
    if ($deleteProcessedMessage) {
      imap_expunge($mbox);
    }
    imap_close($mbox);
  }

  /**
   *
   * @param unknown $subject
   * @param unknown $body
   * @param unknown $rcpt
   * @param unknown $cc
   * @param string  $fileName
   */
  function sendMessage($subject, $body, $rcpt, $cc, $fileName = NULL) {
    $bound_text = md5(uniqid(time()));
    $bound = "--" . $bound_text . "\r\n";
    $bound_last = "--" . $bound_text . "--\r\n";

    $headers = "From: " . $this->mailUserAccount;
    if (!empty($cc)) {
      $headers .= "\r\n";
      $headers .= "Cc: " . $cc;
    }
    $headers .= "\r\n";
    $headers .= "MIME-Version: 1.0\r\n"
      . "Content-Type: multipart/mixed; boundary=\"$bound_text\"";

    $message = "If you can see this MIME than your client doesn't accept MIME types!\r\n"
      . $bound . "Content-Type: text/plain; charset=us-ascii\"\r\n"
      . "Content-Transfer-Encoding: 7bit\r\n\r\n" . $body . "\r\n" . $bound;

    if (is_array($fileName)) {
      foreach ($fileName as $f) {
        $file = file_get_contents($f);
        if ($file === FALSE) {
          // Cannot read the input file.
          return FALSE;
        }
        $basename = explode("/", $f);
        if (count($basename) == 1) {
          $basename = explode("\\", $f);
        }
        $basefilename = $basename[count($basename) - 1];
        $content = base64_encode($file);
        if ($content === FALSE) {
          // Cannot read the input file.
          return FALSE;
        }
        $message .= "Content-Type: application/msword; name=\"" . $basefilename . "\"\r\n"
          . "Content-Transfer-Encoding: base64\r\n"
          . "Content-disposition: attachment; file=\"" . $basefilename . "\"\r\n"
          . "\r\n"
          . chunk_split($content);
        $message .= $bound;
      }

    }
    else {
      $file = file_get_contents($fileName);

      $basename = explode("/", $f);
      if (count($basename) == 1) {
        $basename = explode("\\", $f);
      }
      $basefilename = $basename[count($basename) - 1];
      $content = base64_encode($file);
      if ($content === FALSE) {
        // Cannot read the input file.
        return FALSE;
      }
      $message .= "Content-Type: application/msword; name=\"" . $basefilename . "\"\r\n"
        . "Content-Transfer-Encoding: base64\r\n"
        . "Content-disposition: attachment; file=\"" . $basefilename . "\"\r\n"
        . "\r\n"
        . chunk_split($content);
      $message .= $bound;
    }
    $message .= $bound_last;

    return mail($rcpt, $subject, $message, $headers);
  }
}

