<?php

define("BUCKET_SIZE_NB_TICKETS", 500);

class fileCachedTicketList {

  protected $group_nid;

  /**********************************************************************************/
  public function __construct($group_nid) {
    $this->group_nid = $group_nid;
  }

  /**********************************************************************************/
  public function getCacheFilepath($fileId) {
    $directory = variable_get('ticketing_cache_dir', "/bw/broadworks/xchangeTicketingCache");
    // if dir does not exist, create it
    if (!is_dir($directory))
      @mkdir($directory);
        
    return ($directory.'/'.$this->group_nid.'.'.$fileId.'.cache');
  }

  /**********************************************************************************/
  public function getTmpCacheFilepath($fileId) {
    return ($this->getCacheFilepath($fileId).'.tmp');
  }

  /*********************************************************************************
  */
  public function cacheExists() {
    return file_exists($this->getCacheFilepath(0));
  }

  /*********************************************************************************
  */
  public function read() {
		$bucketId = 0;
    $resData = array();
    while(file_exists($this->getCacheFilepath($bucketId))) {
      $fileData = unserialize(file_get_contents($this->getCacheFilepath($bucketId)));
      $resData['extra'] = $fileData['extra'];
      foreach($fileData['tickets'] as $ticketID => $ticket)
        $resData['tickets'][$ticketID] = $ticket;
      $bucketId++;
    }
    $group_nid=get_cached_group_selection($originatorID, $customerID);DebugPrintComplex('Ticketing Cache ['.$group_nid.', '.$originatorID.', '.$customerID.']: read '.($bucketId).' cache files'); 
    return $resData;
  }

  /*********************************************************************************
  buckets are computed based on nb of tickets, not bucket size. Could be optimized to minimize nb of files.
  */
  public function write($ticketData) {
		$totalEntries = count($ticketData['tickets']);
		$bucketId = 0;
		while ($bucketId*BUCKET_SIZE_NB_TICKETS < $totalEntries){
			$offset = $bucketId*BUCKET_SIZE_NB_TICKETS;
      $data = array();
      if(array_element('extra', $ticketData)) 
        $data['extra'] = $ticketData['extra'];
      $data['tickets'] = array_slice($ticketData['tickets'], $offset, BUCKET_SIZE_NB_TICKETS, true);
      $data['extra']['totNbTickets'] = $totalEntries;
      $data['extra']['curNbTickets'] = count($data['tickets']);
        
      // save to tmp
			$filepath = $this->getTmpCacheFilepath($bucketId);
      if($f = @fopen($filepath, "w"))  { 
          if(@fwrite($f, serialize($data))) {
              @fclose($f); 
          } else 
            DebugPrintComplex("Could not write to file ".$filepath); // @@@ die("Could not write to file ".$this->filename." at Persistant::save");
       } else 
         DebugPrintComplex("Could not open file ".$filepath); // @@@ die("Could not open file ".$this->filename." for writing, at Persistant::save");

			$bucketId++;
		}	
    // delete all prev cache buckets
    foreach(glob($this->getCacheFilepath('*')) as $f) {
      if(!preg_match('/extra/', $f))
        unlink($f);
    }
$group_nid=get_cached_group_selection($originatorID, $customerID);DebugPrintComplex('Ticketing Cache ['.$group_nid.', '.$originatorID.', '.$customerID.']: removing prev cache files'); 
    
    // swap all
    for($i=0 ; $i<$bucketId ; $i++)
      rename($this->getTmpCacheFilepath($i), $this->getCacheFilepath($i));
DebugPrintComplex('Ticketing Cache ['.$group_nid.', '.$originatorID.', '.$customerID.']: swapping '.($i).' cache files'); 
  }
}
