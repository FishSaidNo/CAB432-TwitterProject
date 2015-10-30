<?php
/**
 * Not sure where this will finish but in the meantime tinkering with it to show
 * very small samples of the captured data, and inputting it into our template index.php.
 *
 * - Corey
 */
 
set_time_limit(0);
class GhettoQueueConsumer
{
  protected $queueDir;
  protected $filePattern;
  protected $checkInterval;
  private $statusCounter;
  
  //public function __construct($queueDir = '/tmp', $filePattern = 'twitter-queue*.queue', $checkInterval = 10)
  public function __construct($queueDir = 'TweetQueues', $filePattern = 'twitter-queue*.queue', $checkInterval = 10)
  {
    $this->queueDir = $queueDir;
    $this->filePattern = $filePattern;
    $this->checkInterval = $checkInterval;
    
    // Sanity checks
    if (!is_dir($queueDir)) {
      throw new ErrorException('Invalid directory: ' . $queueDir);
    }
    
  }
  
  public function process() {
    $this->statusCounter = 0;
    // Init some things
    $lastCheck = 0;
    
    // Loop infinitely
    //while (TRUE) {
      
      // Get a list of queue files
      $queueFiles = glob($this->queueDir . '/' . $this->filePattern);
      $lastCheck = time();
      
      $this->log('Found ' . count($queueFiles) . ' queue files to process...');
      
      // Iterate over each file (if any)
      foreach ($queueFiles as $queueFile) {
		if ($this->statusCounter >= 30) {break;}
        $this->processQueueFile($queueFile);
      }
      
      // Wait until ready for next check
      //$this->log('Sleeping...');
      //while (time() - $lastCheck < $this->checkInterval) {
        //sleep(1);
      //}
      
    //} // Infinite loop
    
  }
  
  public function processQueueFile($queueFile) {
    $this->log('Processing file: ' . $queueFile);
    
    // Open file
    $fp = fopen($queueFile, 'r');
    
    // Check if something has gone wrong, or perhaps the file is just locked by another process
    if (!is_resource($fp)) {
      $this->log('WARN: Unable to open file or file already open: ' . $queueFile . ' - Skipping.');
      return FALSE;
    }
    
    // Lock file
    flock($fp, LOCK_EX);
    
    // Loop over each line (1 line per status)
    while ($rawStatus = fgets($fp)) {
      
      $data = json_decode($rawStatus, true);
	  if (json_last_error() != JSON_ERROR_NONE) {
		echo json_last_error_msg();
	  }
      if (is_array($data) && isset($data['user']['screen_name'])) {
		$this->statusCounter++;
		echo '<tr>';
		echo '<td>' . $this->statusCounter . '</td>';
		echo '<td>' . $data['user']['screen_name'] . '</td>';
		echo '<td>' . urldecode($data['text']) . '</td>';
        echo '</tr>';
      }
      
	  if ($this->statusCounter >= 30) {break;}
    } // End while
    
    // Release lock and close
    flock($fp, LOCK_UN);
    fclose($fp);
    
    // All done with this file
    $this->log('Successfully processed ' . $statusCounter . ' tweets from ' . $queueFile . ' - deleting.');
    //unlink($queueFile);    
    
  }

  protected function log($message)
  {
    @error_log($message, 0);
  }
    
}

// Construct consumer and start processing
$gqc = new GhettoQueueConsumer();
$gqc->process();
//$gqc->processQueueFile("/tmp/twitter-queue.20151028-102458.queue");