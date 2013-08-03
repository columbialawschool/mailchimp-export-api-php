<?php
/**
 * MailChimp Export API Library for PHP v1.0.2
 *
 * A free, open-source library, or "wrapper," for the PHP programming language,
 * which provides support for the {@link http://mailchimp.com MailChimp}
 * {@link http://apidocs.mailchimp.com/export/1.0 Export API v1.0}.
 *
 * The library currently supports the member list and campaign subscriber
 * activity data export functionality of the MailChimp Export API v1.0
 * corresponding to the MailChimp v7.9 release (see this release's
 * {@link http://web.archive.org/web/20130124182745/http://apidocs.mailchimp.com/export/1.0/
 * API documentation}). It does not yet support changes introduced to the
 * MailChimp Export API v1.0 corresponding to MailChimp v8.0 or later releases.
 *
 * The library is developed and released to the general public by the
 * {@link http://www.law.columbia.edu/communications
 * Office of Communications and Public Affairs} at {@link http://www.law.columbia.edu
 * Columbia Law School}, Columbia University.
 *
 * @category    ColumbiaLawSchool
 * @package     Integration_Email
 * @subpackage  MailChimp_API
 * @author      Mike Kadin <MichaelKadin@gmail.com>
 * @author      Christian Stuck <crs2144@columbia.edu>
 * @copyright   Copyright (c) 2013 The Trustees of Columbia University
 *              in the City of New York
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @filesource
 * @version     1.0.2
 * @since       1.0
 */
namespace ColumbiaLawSchool\Integration\Email\MailChimp\API;

/**
 * The MailChimp Export API class.
 *
 * This class implements functionality of the
 * {@link http://mailchimp.com MailChimp}
 * {@link http://apidocs.mailchimp.com/export/1.0 Export API, v1.0}
 * and provides objects representing it.
 *
 * @category    ColumbiaLawSchool
 * @package     PHP_Integration_Email
 * @subpackage  MailChimp_API
 * @version     1.0.2
 * @since       1.0
 */
class Export {

  /**
   * The version of this class
   * @var string
   */
  public $version = '1.0.2';

  /**
   * The MailChimp Export API version supported by this class
   *
   * @link http://apidocs.mailchimp.com/export/1.0
   * @var string
   */
  public $api_version = '1.0';

  /**
   * The MailChimp Export API endpoint domain,
   * excluding the datacenter-specific sub-domain
   *
   * @link http://apidocs.mailchimp.com/export/1.0#submit-url-&-parameters
   * @var string
   */
  public $api_endpoint_domain = 'api.mailchimp.com';

  /**
   * The MailChimp Export API endpoint path,
   * excluding the API version and trailing slashes
   *
   * @link http://apidocs.mailchimp.com/export/1.0#submit-url-&-parameters
   * @var string
   */
  public $api_endpoint_path = 'export';

  /**
   * The cached MailChimp account API key
   *
   * This stores the authorization credential so that multiple
   * MailChimp Export API requests can be made per class instantiation
   * without the need to set the API key again.
   *
   * @link http://kb.mailchimp.com/article/where-can-i-find-my-api-key
   * @var string
   */
  protected $api_key;

  /**
   * Flag used to determine whether to create the connection
   * using HTTP or HTTPS
   *
   * @var bool
   */
  protected $secure = FALSE;

  /**
   * The MailChimp error code returned in a server response or by the class
   *
   * Although currently undocumented in the MailChimp Export API, returned codes
   * seem to closely follow the MailChimp API v1.3
   * {@link http://apidocs.mailchimp.com/api/1.3/exceptions.field.php exceptions}
   *
   * @var int
   */
  protected $error_code;

  /**
   * The MailChimp error message returned in a server response or by the class
   * @var int
   */
  protected $error_message;

  /**
   * The number of seconds to wait for a server response before a time-out.
   *
   * Defaults to 300 (i.e., 5 minutes)
   *
   * @var int
   */
  protected $timeout = 300;

  /**
   * The number of bytes to read
   *
   * Defaults to 8192
   *
   * @var int
   */
  protected $chunk_size = 8192;

  /**
   * Initializes the object and sets properties
   * 
   * @param string $api_key
   *   A MailChimp account API key
   * @param bool $secure
   *   (optional) A flag to determine the connection protocol to use
   *   to connect to the MailChimp Export API, either:
   *     - TRUE: Connect using HTTPS (<b>highly recommended</b>
   *       as a best practice to comply with baseline data security,
   *       encryption, privacy standards, most privacy policies,
   *       and accepted general public expectations) (default)
   *     - FALSE: Disable HTTPS and connect using HTTP (<i>not recommended</i> as HTTP
   *       is not secure and inappropriate to use when exchanging personal
   *       identity information and non-public data
   *   Defaults to TRUE.
   */
  public function __construct($api_key, $secure = TRUE) {
    $this->secure = $secure;
    $this->api_endpoint_url = parse_url("http://api.mailchimp.com/export/" . $this->api_version . "/");
    $this->api_key = $api_key;
  }

  /**
   * Sets the number of seconds before a time-out
   *
   * @param integer $seconds
   *   The number of seconds before a time-out
   */
  public function setTimeout($seconds) {
    if (is_int($seconds)) {
      $this->timeout = $seconds;
    }
  }

  /**
   * Gets the current number of seconds before a time-out
   *
   * @return integer
   *   The number of seconds before a time-out
   */
  public function getTimeout() {
    return $this->timeout;
  }

  /**
   * Sets the connection type/protocol for each request
   *
   * @param bool $secure
   *   (optional) A flag to determine the connection protocol to use
   *   to connect to the MailChimp Export API, either:
   *     - TRUE: Connect using HTTPS (<b>highly recommended</b>
   *       as a best practice to comply with baseline data security,
   *       encryption, privacy standards, most privacy policies,
   *       and accepted general public expectations) (default)
   *     - FALSE: Disable HTTPS and connect using HTTP (<i>not recommended</i> as HTTP
   *       is not secure and inappropriate to use when exchanging personal
   *       identity information and non-public data
   *   Defaults to TRUE.
   */
  public function useSecure($val = TRUE) {
    if ($val === TRUE) {
      $this->secure = TRUE;
    }
    else {
      $this->secure = FALSE;
    }
  }

  /**
   * Calls the list method of the MailChimp Export API
   * 
   * Returns list members, including their associated details, for a given
   * unique {@link http://kb.mailchimp.com/article/how-can-i-find-my-list-id
   * MailChimp List ID}
   * 
   * @param string $id
   *   The unique MailChimp List ID
   * @param string $status
   *   (optional) The status of members to return, one of:
   *     - 'subscribed': Members who are subscribed to the list (default)
   *     - 'unsubscribed': Members who are unsubscribed from the list
   *     - 'cleaned': Members who have been cleaned from the list due to bounces
   *   Defaults to 'subscribed'
   * @param array $segment
   *   (optional) An associative array of options to use to limit the result to
   *   a subset (i.e., segment) of members. For valid values according to the
   *   MailChimp Export API documentation, see the documentation for the options
   *   parameter in the
   *   {@link http://apidocs.mailchimp.com/api/1.3/campaignsegmenttest.func.php
   *   campaignSegmentTest()} method of the MailChimp API v1.3. Note:
   *   MailChimp suggests testing segment options against the MailChimp API v1.3
   *   campaignSegmentTest() method. MailChimp Export API Library for PHP does
   *   not provide support for this test method, as it is supported by
   *   {@link http://apidocs.mailchimp.com/api/downloads/#php MailChimp PHP API
   *   Wrapper (MCAPI) 1.3.2}. MCAPI is a free, open-source PHP library,
   *   or "wrapper," for the MailChimp API v1.3, and is provided by
   *   MailChimp under the GNU GPL-compatible Expat License (ambiguously
   *   referenced as the MIT License in its readme file).
   * @param int|string $since
   *   (optional) A date and time, corresponding to the PHP date format set in
   *   the $since_format parameter, to use to limit the result to only members
   *   whose data has changed since that time
   * @param string $since_format
   *   (optional) If $since is set, a PHP date format string suitable for input
   *   to @see DateTime::createFromFormat(). Defaults to 'U' for a POSIX/UNIX
   *   timestamp.
   * @param string $since_timezone
   *   (optional) If $since is set and $since_format is not set to 'U',
   *   a time zone string string suitable for input to
   *   @see DateTimeZone::__construct(). Defaults to 'UTC'.
   *
   * @return array
   *   An associative array, keyed by email address, of objects
   * @throws Exception
   *   Throws an exception if an error occurs
   */
  public function getList($id, $status = NULL, $segment = array(), $since = NULL, $since_format = 'U', $since_timezone = 'UTC') {
    // Parse and format the $since parameter
    if (!empty($since)) {
    $since_param_datetime = \DateTime::createFromFormat($since_format, $since, new \DateTimeZone($since_timezone));
    $since_param_datetime->setTimezone(new \DateTimeZone('GMT'));
    $since_param_datetime_formatted = $since_param_datetime->format('Y-m-d H:i:s');
    }
    else {
    $since_param_datetime_formatted = NULL;
    }

    // Set the MailChimp Export API method parameters
    $params = array(
      'id' => $id,
      'status' => $status,
      'segment' => $segment,
      'since' => $since_param_datetime_formatted,
    );
    
    // Retrieve the data from the MailChimp Export API
    $data = $this->callServer('list', $params);
    
    // Throw an exception if the MailChimp Export API returns an error
    if ($data === FALSE) {
      throw new \Exception($this->error_message . " CODE: " . $this->error_code);
    }
    
    // Rearrange the data from the structure provided by the
    // MailChimp Export API so that each item in the returned array is keyed
    // to the email address and is an object with the property names provided
    // in the header row
    $headers = array_shift($data);
    
    // Clean up the text in the header row
    foreach ($headers as &$header) {
      $header = strtolower($header);
      $header = str_replace(' ','_',$header);
      $header = str_replace("'",'',$header);
    }

    $list = array();

    foreach ($data as $list_counter => $row) {
      if (!empty($row)) {
        foreach($row as $index => $field) {
          $list[$row[0]]->{$headers[$index]} = $field;
        }
      }
    }

    return $list;
  }

  /**
   * Calls the campaignSubscriberActivity method of the MailChimp Export API
   * 
   * Returns member activity (opens and clicks) for a given unique
   * {@link http://mailchimp.com/contact/campaign-id MailChimp Campaign ID}.
   *
   * Note: Contrary to MailChimp's online API documentation, this API method
   * does not return all subscriber activity. Testing conducted during
   * development of this project indicated that it only returns
   * opens and clicks.
   * 
   * @param string $id
   *   The unique MailChimp Campaign ID
   * @param bool $include_empty
   *   A flag to determine if the result should return members who do not have
   *   any recorded open or click activity for the campaign. Defaults to FALSE.
   *
   * @return array
   *   An associative array, keyed by email address, of arrays of objects
   * @throws Exception
   *   Throws an execption if an error occurs
   */
  public function getCampaignSubscriberActivity($id, $include_empty = FALSE) {
    // Clean up the unique MailChimp Campaign ID
    $id = str_replace('mailchimp', '', $id);
    if (strstr($id, '.')) {
      list($account_user_uuid, $id) = explode('.', $id);
    }

    // Set the MailChimp Export API method parameters
    $params = array(
      'id' => $id,
      'include_empty' => $include_empty,
    );

    // Retrieve the data from the MailChimp Export API
    $data = $this->callServer('campaignSubscriberActivity', $params);

    // Throw an exception if the MailChimp Export API returns an error
    if ($data === false) {
      throw new \Exception($this->error_message . " CODE: " . $this->error_code);
    }

    // Rearrange the data from the structure provided by the
    // MailChimp Export API to return an associative array, keyed by
    // email address, of objects
    foreach ($data as $row) {
      foreach ($row as $member_email => $member_activities) {
        $activity[$member_email]->email_address = $member_email;
        $activity[$member_email]->activity = array();
        foreach($member_activities as $member_activity) {
          $activity[$member_email]->activity[$member_activity->action][] = $member_activity;
        }
      }
    }

    return $activity;
  }

  /**
   * Connects to the MailChimp Export API REST-like web service and calls the
   & requested remote method, parsing the result into an array
   * 
   * @param string $method
   *   The remote method to call
   * @param array $params
   *   An associative array of parameters to pass to the remote method
   *
   * @return array
   *   The decoded data returned by the service (likely requiring
   *   method-specific restructuring)
   */
  protected function callServer($method, $params) {
    // Set the apikey parameter using the API key provided in the constructor
    $params['apikey'] = $this->api_key;

    // Clear any old error messages or codes
    $this->error_message = '';
    $this->error_code = '';
    
    // Handle encoded and unencoded ampersands
    $sep_changed = false;
    if (ini_get('arg_separator.output') != '&') {
      $sep_changed = true;
      $orig_sep = ini_get('arg_separator.output');
      ini_set('arg_separator.output', '&');
    }
    $post_vars = http_build_query($params);
    if ($sep_changed) {
      ini_set('arg_separator.output', $orig_sep);
    }

    // Prepare the payload string to POST to the
    // MailChimp Export API endpoint URL.
    $payload = "POST " . $this->getAPIEndpointURL() . "/" . $method . "/?id=" . $params['id'] . "&apikey=" . $params['apikey'] . " HTTP/1.1\r\n";
    $payload .= "Host: " . $this->getAPIDatacenter() . "." . $this->api_endpoint_domain . "\r\n";
    $payload .= "User-Agent: php-mailchimp-export/" . $this->version . "\r\n";
    $payload .= "Content-type: application/x-www-form-urlencoded\r\n";
    $payload .= "Content-length: " . strlen($post_vars) . "\r\n";
    $payload .= "Connection: close \r\n\r\n";
    $payload .= $post_vars;

    // Open a socket to the remote URL
    ob_start();
    if ($this->secure && 1==9) {
      $sock = fsockopen('ssl://' . $this->getAPIDatacenter() . '.' . $this->api_endpoint_domain, 443, $errno, $errstr, 30);
    }
    else {
      $sock = fsockopen($this->getAPIDatacenter() . '.' . $this->api_endpoint_domain, 80, $errno, $errstr, 30);
    }
    if (!$sock) {
      // If the socket fails to open, set an error code and message,
      // and return FALSE
      $this->error_message = "Could not connect (ERR $errno: $errstr)";
      $this->error_code = -99;
      ob_end_clean();
      return false;
    }

    $response = '';
    
    // Send the payload
    fwrite($sock, $payload);
    stream_set_timeout($sock, $this->timeout);
    $info = stream_get_meta_data($sock);
    
    // Read back the data
    while ((!feof($sock)) && (!$info['timed_out'])) {
      $response .= fread($sock, $this->chunk_size);
      $info = stream_get_meta_data($sock);
    }
    fclose($sock);
    ob_end_clean();
    
    // Set an error if a time-out occured
    if ($info["timed_out"]) {
      $this->error_message = 'Could not read response (timed out)';
      $this->error_code = -98;
      return false;
    }
    
    // Parse the headers and response body
    list($headers, $response) = explode("\r\n\r\n", $response, 2);
    $headers = explode("\r\n", $headers);
    
    // Search for errors
    $errored = false;
    foreach ($headers as $h) {
      if (substr($h, 0, 26) === 'X-MailChimp-API-Error-Code') {
        $errored = TRUE;
        $error_code = trim(substr($h, 27));
        break;
      }
    }

    // Handle magic quotes
    if (ini_get('magic_quotes_runtime'))
      $response = stripslashes($response);
    
    // If an error occurred, set the code and message, and return FALSE
    if ($errored) {
      $this->error_message = 'No error message was found';
      $this->error_code = intVal($error_code);
      $decoded = json_decode($response);
      if (!empty($decoded->error)) {
        $this->error_message = $decoded->error;
        $this->error_code = intVal($decoded->code);
      }
      return FALSE;
    }
    
    // Parse each line as the MailChimp Export API provides each row
    // separated by "newlines"
    $lines = explode("\n", $response);
    
    // Decode each row
    $data = array_map('json_decode', $lines);
    unset($data[0]);
    $data = array_values($data);

    // If the response was empty or cannot be decoded, set an error code
    // and message, and return FALSE
    if (!empty($response) && (empty($data) || (empty($data[0])))) {
      $this->error_message = 'Bad Response. Got This: ' . $response;
      $this->error_code = -99;
      return FALSE;
    }

    return $data;
  }

  /**
   * Gets the MailChimp API Endpoint URL in order to connect to the
   * MailChimp Export API, excluding the connection protocol
   *
   * @return string
   *   A URL excluding the connection protocol
   */
  protected function getAPIEndpointURL() { 
    return '/' . $this->api_endpoint_path . '/' . $this->api_version;
  }

  /**
   * Gets the MailChimp API datacenter code
   *
   * @return string
   *   The datacenter code
   */
  protected function getAPIDatacenter() {
    // Parse the MailChimp datacenter code if it is in the API key
    if (strstr($this->api_key, '-')) {
      list($api_key_without_datacenter, $api_key_datacenter) = explode('-', $this->api_key);
    }
    else {
      $api_key_datacenter = FALSE;
    }
    return $api_key_datacenter ? $api_key_datacenter : 'us1';
  }

}