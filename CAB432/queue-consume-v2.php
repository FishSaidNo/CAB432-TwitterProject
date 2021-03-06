<?php
header("Content-type: text/javascript");
/**
 * Not sure where this will finish but in the meantime tinkering with it to show
 * very small samples of the captured data, and inputting it into our template index.php.
 *
 * - Corey
 */

require 'phpIncludes/functions.php';
$userId; // Initialise variable
//If not logged in, do something;
if (!inSession()) {
	//header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/pleaseLogin.php');
	exit;
} else {
	$userId = $_SESSION['userId'];
}
$latestTweets = array();
$fetchTweets = False;
if (!empty($_GET['fetchtweets'])) {
	if ($_GET['fetchtweets'] == 'true') {
		$fetchTweets = True;
						
	}
}
 
 
set_time_limit(0);
class GhettoQueueConsumer
{
  protected $queueDir;
  protected $filePattern;
  protected $checkInterval;
  private $statusCounter;
  
  //public function __construct($queueDir = '/tmp', $filePattern = 'twitter-queue*.queue', $checkInterval = 10)
  public function __construct($queueDir = 'TweetQueues', $filePattern = 'twitter-queue*.queue2', $checkInterval = 10)
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
      //foreach ($queueFiles as $queueFile) {
		//if ($this->statusCounter >= 30) {break;}
        $this->processQueueFile(end($queueFiles));
		//break;
      //}
      
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
    
	$result_array = array();
	$watchedTerms = array();
	//$latestTweets = array();
	global $latestTweets;
	//global $fetchTweets;
	
	require 'phpIncludes/dbConn.php';
	
	$row = []; //Initialize
	global $userId;
	
	try {
		$query = 'SELECT watchedTerms FROM users WHERE userId=:userId;';
		$stmt = $db->prepare($query);
		$stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);	
		
	} catch(PDOException $ex) {
		header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=PDO'); //error
		exit;
	} 
	if ($db != NULL) {
		$db = NULL; //Close db connection
	}

	if (empty($row) || $row['watchedTerms'] == '') {
		//user not watching any terms
		//do nothing currently
	} else {
		//Store terms in array
		$watchedTerms = explode(',', $row['watchedTerms']);
	} 
	
	//setup structure of result_array
	foreach ($watchedTerms as $term) {
		$termArray = array ('occurs' => 0, 'positive' => 0, 'negative' => 0, 'neutral' => 0);
		
		if(!isset($result_array[$term])) {
			$result_array[$term] = array();
		}
		$result_array[$term] = $termArray;
	}
	
	$tweetCount = 0;
    // Loop over each line (1 line per status)
    while ($rawStatus = fgets($fp)) {
      
		$data = json_decode($rawStatus, true);
		if (json_last_error() != JSON_ERROR_NONE) {
		echo json_last_error_msg();
		}
		//if (is_array($data) && isset($data['user']['screen_name'])) {
		if (is_array($data) && isset($data['text'])) {
			$this->statusCounter++;
			// echo '<tr>';
			// echo '<td>' . $this->statusCounter . '</td>';
			// echo '<td>' . $data['user']['screen_name'] . '</td>';
			// echo '<td>' . urldecode($data['text']) . '</td>';
			// echo '</tr>';
			
			if ($tweetCount < 5) {
				global $latestTweets;
				$latestTweets[] = array(
					'text' => urldecode($data['text']),
					'sentiment' => $data['sentiment']
				);				
			}
			$tweetCount += 1;

			// global $fetchTweets;
			// if ($fetchTweets) {
				// echo json_encode($latestTweets);
			// }
				
			$tweet = urldecode($data['text']);
			foreach ($watchedTerms as $term) {
				if (strpos(strtolower($tweet), $term) !== False) {
					//If the tweet contains the term...
					$result_array[$term]['occurs'] += 1;
					
					if ($data['sentiment'] == 'positive') {
						$result_array[$term]['positive'] += 1;
					} elseif ($data['sentiment'] == 'negative') {
						$result_array[$term]['negative'] += 1;
					} elseif ($data['sentiment'] == 'neutral') {
						$result_array[$term]['neutral'] += 1;
					} else {
						// Wha??
					}
				}
			}
      }
      
	  //if ($this->statusCounter >= 30) {break;}
    } // End while
    
    // Release lock and close
    flock($fp, LOCK_UN);
    fclose($fp);
    
    // All done with this file
    //$this->log('Successfully processed ' . $this->statusCounter . ' tweets from ' . $queueFile . ' - deleting.');
    global $fetchTweets;
	$fetchmode = $fetchTweets;

	//echo the data as json
	if (!$fetchmode) {
		echo json_encode($result_array);
	} else {
		echo json_encode($latestTweets);		
	}
	
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