<?php

/*
 * Trying to get storing of Twitter Streaming API into files with rotations of files at a time period.
 * More documentation to follow.
 *
 * - Corey
 */

// Import the necessary modules from Phirehose API (Simplified Twitter API for PHP)
require_once('lib/Phirehose.php');
require_once('lib/OauthPhirehose.php');
 
// Ensure script doesn't timeout after 30 seconds.
set_time_limit(0);

class GhettoQueueCollector extends OauthPhirehose
{
  // Subclass specific constants
  const QUEUE_FILE_PREFIX = 'twitter-queue';
  const QUEUE_FILE_ACTIVE = '.twitter-queue.current';

  // Member attributes specific to this subclass
  protected $queueDir;
  protected $rotateInterval;
  protected $streamFile;
  protected $statusStream;
  protected $lastRotated;

  /**
   * Overidden constructor to take class-specific parameters
   *
   * @param string $username
   * @param string $password
   * @param string $queueDir
   * @param integer $rotateInterval
   */
   
  //public function __construct($username, $password, $queueDir = '/tmp', $rotateInterval = 10)
  public function __construct($username, $password, $queueDir = 'TweetQueues', $rotateInterval = 10)
  {

    // Sanity check
    if ($rotateInterval < 5) {
      throw new Exception('Rotate interval set too low - Must be >= 5 seconds');
    }

    // Set subclass parameters
    $this->queueDir = $queueDir;
    $this->rotateInterval = $rotateInterval;

    // Call parent constructor
    return parent::__construct($username, $password, Phirehose::METHOD_FILTER);
  }

  // Called for each tweet
  public function enqueueStatus($status)
  {

    // Write the status to the stream (must be via getStream())
    fputs($this->getStream(), $status . PHP_EOL);

	// Check to rotate file
    $now = time();
    if (($now - $this->lastRotated) > $this->rotateInterval) {
      // Mark last rotation time as now
      $this->lastRotated = $now;

      // Rotate it
      $this->rotateStreamFile();
    }

  }

  /**
   * Returns a stream resource for the current file being written/enqueued to
   *
   * @return resource
   */
  private function getStream()
  {
    // If we have a valid stream, return it
    if (is_resource($this->statusStream)) {
      return $this->statusStream;
    }
	
    // If it's not a valid resource, we need to create one
	if (!is_dir($this->queueDir)) {
		mkdir($this->queueDir, 0777, true);
	}
	
    if (!is_writable($this->queueDir)) {
      throw new Exception('Unable to write to queueDir: ' . $this->queueDir);
    }

    // Construct stream file name, log and open
    $this->streamFile = $this->queueDir . '/' . self::QUEUE_FILE_ACTIVE;
    $this->log('Opening new active status stream: ' . $this->streamFile);
    $this->statusStream = fopen($this->streamFile, 'a'); // Append if present (crash recovery)

    // Redundant check
    if (!is_resource($this->statusStream)) {
      throw new Exception('Unable to open stream file for writing: ' . $this->streamFile);
    }

    // If we don't have a last rotated time, do now
    if ($this->lastRotated == NULL) {
      $this->lastRotated = time();
    }

    // Looking good, return the resource
    return $this->statusStream;

  }

  // Rotates the stream file if due
  private function rotateStreamFile()
  {
    // Close the stream
    fclose($this->statusStream);

    // Create queue file with timestamp so they're both unique and naturally ordered
    $queueFile = $this->queueDir . '/' . self::QUEUE_FILE_PREFIX . '.' . date('Ymd-His') . '.queue';

    // Do the rotate
    rename($this->streamFile, $queueFile);

    // Redundancy check
    if (!file_exists($queueFile)) {
      throw new Exception('Failed to rotate queue file to: ' . $queueFile);
    }

    $this->log('Successfully rotated active stream to queue file: ' . $queueFile);
  }

} // End of class

//////////
//Get the list of terms all users are watching...
require 'phpIncludes/dbConn.php';

$result_rows = []; //Initialize query result
$allWatchedTerms = []; //List of terms watched by all users

try {
	$query = 'SELECT watchedTerms FROM users;';
	$stmt = $db->prepare($query);
	$stmt->execute();
	$result_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);	
	//print_r($result_rows);
} catch(PDOException $ex) {
	header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=PDOfromqueue-collect'); //error
	exit;
} 	

//Iterates over all users watechedTerms
foreach ($result_rows as $row) {
	$usersWatchedTerms = [];
	
	if (empty($row) || $row['watchedTerms'] == '') {
		//This user is not watching any terms
		continue;
	} else {
		//Store terms in array
		$usersWatchedTerms = explode(',', $row['watchedTerms']);
	}
	
	//Check the user's term has not already been added (i.e. also watched by another user)
	foreach ($usersWatchedTerms as $term) {
		if (!in_array($term, $allWatchedTerms)) {
			$allWatchedTerms[] = $term;
		}
	}				
}
//////////

// Twitter API access keys. These are my personal Twitter API keys, please don't share
define("TWITTER_CONSUMER_KEY", "2WoPFbAiFpUuS89dpWkhD76JZ");
define("TWITTER_CONSUMER_SECRET", "JpMgkq0QmhthWNajVGdM0Yy62DWevNn1V7vK9m51A4B2zvwA9w");
define("OAUTH_TOKEN", "306689174-piN0juIBvqgvhUW7XrNOQj850CbW0bMze7b6hcti");
define("OAUTH_SECRET", "iPEM7ym9d5ioxBFTBOyye3eGsbnn7Pek9yL4MIsNswYJy");

// Start streaming/collecting under filter terms
$sc = new GhettoQueueCollector(OAUTH_TOKEN, OAUTH_SECRET);
//$sc->setTrack(array('morning', 'goodnight', 'hello', 'the', 'and'));
$sc->setTrack($allWatchedTerms);
$sc->consume();