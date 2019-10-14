<?php
/**
 * File:        /core/classes/Mail.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Mail
 */
class Mail
{
	/**
	 * List of To addresses
	 * @type array
	 */
	private $to = array();

	/**
	 * List of message headers
	 * @type array
	 */
	private $header = array();

    /**
     * Email addresses validator
     * @type boolean
     */
    private $check = TRUE;

	/**
	 * Priority of the message
	 * @type array
	 */
	private $priority = array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');

	/**
	 * Character set of message
	 * @type string
	 */
	private $charset = "UTF-8";

	/**
	 * Content-Transfer-Encoding, 8bit | base64
	 * @type string
	 */
	private $cencoding = "base64";

	/**
	 * The MIME Content-type of the message.
	 * @type string
	 */
	public $ctype = 'text/plain';

	/**
	 * The Sender email (Return-Path) of the message.
	 * If not empty, will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
	 * @type string
	 */
	public $sender = '';

	/**
	 * Paths of attached files
	 * @var array
	 */
	public $attached = array();

	/**
	 * MIME types files
	 * @var array
	 */
	public $mime = array();

    /**
     * The hostname to use in Message-Id and Received headers.
     * SERVER_NAME is used or 'localhost.localdomain'.
     * @type string
     */
    public $server = '';

	/**
	 * The SMTP HELO of the message.
	 * @type string
	 */
	public $helo = '';

	/**
	 * The Subject of the message.
	 * @type string
	 */
    public $subject = '';

    /**
     * Array with headline
     * @type array
     */
    private $headers = '';

	/**
	 * The Subject of the message.
	 * @type string
	 */
    private $box = '';

	/**
	 * Class Mail _contructor
	 */
	public function __construct()
	{
		$this->autoCheck(TRUE);
		$this->boundary = md5(uniqid(time()));
	}

	/**
	 * Activation send mail
	 */
	public function acting($box = FALSE)
	{
		global $config;

		$this->box = $box;
		if($config['mail_acting'] == 'mail') {
			$cho = $this->mail();
		} else {
			$cho = $this->smtp();
		}
		return ($cho === TRUE) ? TRUE : FALSE;
	}

	/**
	 * Activate or desactivate the email addresses validator
	 * @param boolean	$bool set to true to turn on the auto validation
	 */
	public function autoCheck($bool)
	{
		if($bool) {
			$this->check = TRUE;
		} else {
			$this->check = FALSE;
		}
	}

	/**
	 * Define the subject line of the email
	 * @param string $subject any monoline string
	 */
	public function Subject($subject)
	{
		$this->header['Subject'] = "=?".$this->charset."?Q?".str_replace("+","_",str_replace("%","=",urlencode(strtr( $subject, "\r\n" , "  " ))))."?=";
	}

	/**
	 * Set the sender of the mail
	 * @param string $from should be an email address
	 */
	public function From($from)
	{
		if( ! is_string($from) )
		{
			$this->error('Class Mail, method From: <b>From</b> is not a string!');
		}
		$this->header['From'] = $from;
	}

	/**
	 * Set the mail recipient
	 * @param string $to email address
	 */
	public function To($to)
	{
		if (is_array($to)) {
			$this->to = $to;
		} else {
			$this->to[] = $to;
		}
		if ($this->check == TRUE) {
			$this->Check($this->to);
		}
	}

	/**
	 * Body of mail message
	 * $body : Text letter;
	 */
	public function Body($body)
	{
		if ($this->cencoding == 'base64') {
			$this->body = chunk_split(base64_encode($body));
		} else {
			$this->body = $body;
		}
	}

	/**
	 * Priority
	 * set the mail priority
	 */
	public function Priority($priority)
	{
		if ( ! intval($priority) ) {
			return FALSE;
		}
		if ( ! isset($this->priority[$priority - 1]) ) {
			return FALSE;
		}
		$this->header["X-Priority"] = $this->priority[$priority - 1];

		return TRUE;
	}

	/**
	 * Build the email message
	 * @access protected
	 */
	private function BuildMail($type)
	{
		$this->header["Mime-Version"] = "1.0";
		$this->header["X-Mailer"] = "PHP/DN-Mailer ()";

		if ($type == 'mail')
		{
			if(count($this->attached) > 0) {
				$this->attachment();
			} else {
				$this->header["Content-Type"] = "".$this->ctype."; charset=".$this->charset."";
				$this->header["Content-Transfer-Encoding"] = $this->cencoding;
				$this->bodys = $this->body;
			}
			reset($this->header);
			foreach ($this->header as $key => $val)
			{
				if ($key != "Subject") {
					$this->headers .= "".$key.": ".$val."\r\n";
				}
			}
		}
		elseif ($type == 'smtp')
		{
			$this->helo = $this->getHost();
			$header["Date"] = $this->rDate();
			$header["Message-ID"] = sprintf('<%s@%s>', md5(uniqid(time())), $this->getHost());
			$this->header = $header += $this->header;
			if(count($this->attached) > 0) {
				$this->attachment();
			} else {
				$this->header["Content-Type"] = "".$this->ctype."; charset=".$this->charset."";
				$this->header["Content-Transfer-Encoding"] = $this->cencoding;
				$this->bodys = $this->body;
			}
			reset($this->header);
			foreach ($this->header as $key => $val)
			{
				$this->headers .= "".$key.": ".$val."\r\n";
			}
		}
	}

	/**
	 * Format and send the mail
	 * @access public
	 */
	public function mail()
	{
		$this->BuildMail('mail');
		$this->strTo = implode(", ", $this->to);
		if (empty($this->sender)) {
			$params = ' ';
		} else {
			$params = sprintf('-f%s', $this->sender);
		}
		if (! empty($this->sender) and ! ini_get('safe_mode')) {
			$_from = ini_get('sendmail_from');
			ini_set('sendmail_from', $this->sender);
		}
		$this->NewLine();
		if (count($this->to) > 1) {
			foreach ($this->to as $valTo) {
				mail($valTo, $this->header['Subject'], $this->bodys, $this->headers, $params);
			}
		} else {
			mail($this->strTo, $this->header['Subject'], $this->bodys, $this->headers, $params);
		}
		if (isset($_from)) {
			ini_set('sendmail_from', $_from);
		}
		return TRUE;
	}

	/**
	 * Format and send the smtp
	 * @access public
	 */
	public function smtp
	(
		$host = null,
		$user = null,
		$pass = null,
		$port = null,
		$tout = null
	) {
		global $config;

		$smtp = Json::decode($config['mail_smtp']);

		$this->host = (isset($host)) ? $host : $smtp['mail_host'];
		$this->user = (isset($user)) ? $user : $smtp['mail_user'];
		$this->pass = (isset($pass)) ? $pass : $smtp['mail_pass'];
		$this->port = (isset($port)) ? $port : $smtp['mail_port'];
		$this->tout = (isset($tout)) ? $tout : $smtp['mail_tout'];

		$this->BuildMail('smtp');
		//$this->NewLine();

		try
		{
			if ( ! $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->tout) ) {
				throw new Exception($errno.".".$errstr);
			}
			if ( ! $this->_code($this->socket, 220) ) {
				throw new Exception('Error: Connection');
			}
			fputs($this->socket, "EHLO ".$this->helo."\r\n");
			if ( ! $this->_code($this->socket, 250) ) {
				$this->close();
				throw new Exception('Error: EHLO');
			}
			fputs($this->socket, "AUTH LOGIN\r\n");
			if ( ! $this->_code($this->socket, 334) ) {
				$this->close();
				throw new Exception('Error: AUTH LOGIN');
			}
			fputs($this->socket, base64_encode($this->user)."\r\n");
			if ( ! $this->_code($this->socket, 334) ) {
				$this->close();
				throw new Exception('Error: Autorization login');
			}
			fputs($this->socket, base64_encode($this->pass)."\r\n");
			if ( ! $this->_code($this->socket, 235) ) {
				$this->close();
				throw new Exception('Error: Autorization password');
			}
			foreach ($this->to as $valTo)
			{
				$vTo = "To: ".$valTo."\r\n";
				fputs($this->socket, "MAIL FROM: ".$this->user."\r\n");
				if ( ! $this->_code($this->socket, 250) ) {
					$this->close();
					throw new Exception('Error: MAIL FROM');
				}
				fputs($this->socket, "RCPT TO: ".$valTo."\r\n");
				if ( ! $this->_code($this->socket, 250) ) {
					$this->close();
					throw new Exception('Error: RCPT TO');
				}
				fputs($this->socket, "DATA\r\n");
				if ( ! $this->_code($this->socket, 354) ) {
					$this->close();
					throw new Exception('Error: DATA');
				}
				fputs($this->socket, $vTo.$this->headers."\r\n".$this->bodys."\r\n.\r\n");
				if ( ! $this->_code($this->socket, 250) ) {
					$this->close();
					throw new Exception("E-mail: not sent");
				}
				fputs($this->socket, "RSET\r\n");
				if ( ! $this->_code($this->socket, 250) ) {
					$this->close();
					throw new Exception('Error: RSET');
				}
			}
			fputs($this->socket, "QUIT\r\n");
			$this->close();
		}
		catch (Exception $e) {
			$this->error('Class Mail, method smtp <b>'.$e->getMessage().'</b>');
		}
		return TRUE;
	}

	public function Attach($files)
	{
		global $config;

		if (isset($files['name']) AND ! empty($files['name'][0]))
		{
			$this->ClearTmp(DNDIR.'cache/tmp/');

			if (count($files['name']) > $config['mail_file_col']) {
				$this->error('Class Mail, method Attach: <b>The number of files exceeds the permitted!</b>');
			} else {
				$this->attached = $files;
			}
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * check and encode attach file(s) . internal use only
	 * @access private
	 */
	private function attachment()
	{
		global $config, $lang, $tm, $api, $global;

		$this->header["Content-Type"] = "multipart/mixed; boundary=\"".$this->boundary."\"\r\n";
		$this->bodys = "--".$this->boundary."\r\n";
		$this->bodys .= "Content-Type: ".$this->ctype."; charset=".$this->charset."\r\n";
		$this->bodys .= "Content-Transfer-Encoding: ".$this->cencoding."\r\n\r\n";
		$this->bodys .= "".$this->body."\r\n";

		$dirpath = DNDIR.'cache/tmp';

		if (is_array($this->attached['name']))
		{
			$count = count($this->attached['name']);
			for ($i = 0, $count; $i < $count; $i ++)
			{
				$unpath[] = $path = $dirpath.'/'.uniqid('tmp_').".".pathinfo($this->attached['name'][$i], PATHINFO_EXTENSION);
				move_uploaded_file($this->attached['tmp_name'][$i], $path);
				$basename = "=?utf-8?B?".base64_encode(basename($this->attached['name'][$i]))."?=";
				if ( ! file_exists($path)) {
					$this->error('Class Mail, method Attach: file <b>'.$this->attached['name'][$i].'</b> is not found!');
					exit;
				}
				$this->bodys .= "--".$this->boundary."\r\n";
				$this->bodys .= "Content-Type: ".$this->mimes($this->attached['name'][$i])."; name=\"".$basename."\"\r\n";
				$this->bodys .= "Content-Transfer-Encoding: base64\r\n";
				$this->bodys .= "Content-Disposition: attachment; filename=\"".$basename."\"\r\n\r\n";
				$this->bodys .= chunk_split(base64_encode(file_get_contents($path)));
			}
			foreach ($unpath as $rid) {
				unlink($rid);
			}
		}
		else
		{
				$unpath = $path = $dirpath.'/'.uniqid('tmp_').".".pathinfo($this->attached['name'], PATHINFO_EXTENSION);
				move_uploaded_file($this->attached['tmp_name'], $path);
				$basename = "=?utf-8?B?".base64_encode(basename($this->attached['name']))."?=";
				if ( ! file_exists($path)) {
					$this->error('Class Mail, method Attach: file <b>'.$this->attached['name'].'</b> is not found!');
					exit;
				}
				$this->bodys .= "--".$this->boundary."\r\n";
				$this->bodys .= "Content-Type: ".$this->mimes($this->attached['name'])."; name=\"".$basename."\"\r\n";
				$this->bodys .= "Content-Transfer-Encoding: base64\r\n";
				$this->bodys .= "Content-Disposition: attachment; filename=\"".$basename."\"\r\n\r\n";
				$this->bodys .= chunk_split(base64_encode(file_get_contents($path)));
				unlink($unpath);
		}
		$this->bodys .= "--".$this->boundary."--\r\n\r\n";
	}

	/**
	 * Clearing the temporary folder.
	 * @return delete all files older than 1 hour with the suffix tmp_.
	 */
	public function ClearTmp($dirtmp)
	{
		$farr = scandir($dirtmp); unset($farr[0], $farr[1]);
		foreach ($farr as $val)
		{
			if (preg_match('/tmp_.+/', $val))
			{
				$fname = $dirtmp.$val;
				if (file_exists($fname))
				{
					$ftime = filemtime($fname);
					$dtime = time() - $ftime;
					if ($dtime > 3600)
					{
						unlink($fname);
					}
				}
			}
		}
	}

	/**
	 * Get the MIME type for a file extension.
	 * @return string MIME type of file.
	 */
	public function mimes($file)
	{
		global $config, $lang, $tm, $api, $global;

		$in = Json::decode($config['mail_list_mime']);
		if (is_array($in) AND ! empty($in)) {
			foreach ($in as $v) {
				$this->mime[$v['type']] = $v['data'];
			}
		}
		$ext = strtolower(substr(strrchr(basename($file), '.'), 1));
		if (array_key_exists(strtolower($ext), $this->mime)) {
			return $this->mime[strtolower($ext)];
		} else {
			if ($config['mail_file_type'] == 'all') {
				return 'application/octet-stream';
			} else {
				$this->error('Class Mail, method mimes: This MIME type <b>'.$ext.'</b> is not supported!');
			}
		}
	}

	/**
	 * Check an email address validity
	 * @param string $address
	 * @return true if email adress
	*/
	public function ValidEmail($email)
	{
		if (function_exists('filter_var')) {
			return (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) ? FALSE : TRUE;
		} else {
			return (boolean)preg_match(
				'/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
				'[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
				$email
			);
		}
	}

	/**
	 * Check validity of email addresses
	 * @param	array $adress
	 * @return if unvalid, output an error message and exit
	 */
	public function Check($email)
	{
		foreach ($email as $k => $v)
		{
			if ( ! $this->ValidEmail($v) ) {
				$this->error('Class Mail, method ValidEmail : invalid address '.$v);
			}
		}
	}

	/**
	 * Sets message type to HTML.
	 * @return html
	 */
	public function Html()
	{
		$this->ctype = 'text/html';
	}

	/**
	 * Change end lines.
	 * @return clear
	 */
	protected function NewLine()
	{
		if ("\r\n" != PHP_EOL) {
			$this->headers = str_replace("\r\n", PHP_EOL, $this->headers);
			$this->bodys = str_replace("\r\n", PHP_EOL, $this->bodys);
		} else {
			$this->headers = str_replace("\r\n.", "\r\n..", $this->headers);
			$this->bodys = str_replace("\r\n.", "\r\n..", $this->bodys);
		}
	}

	/**
	 * Return an RFC 822 formatted date.
	 * @return string
	 */
	public function rDate()
	{
		date_default_timezone_set(date_default_timezone_get());
		return date('D, j M Y H:i:s O');
	}

	/**
	 * Get the server hostname.
	 * @return string
	 */
	protected function getHost()
	{
		$name = 'localhost.localdomain';
		if ( ! empty($this->server)) {
			$name = $this->server;
		} elseif (isset($_SERVER) AND array_key_exists('SERVER_NAME', $_SERVER) AND ! empty($_SERVER['SERVER_NAME'])) {
			$name = $_SERVER['SERVER_NAME'];
		} elseif (function_exists('gethostname') AND gethostname() !== FALSE) {
			$name = gethostname();
		} elseif (php_uname('n') !== FALSE) {
			$name = php_uname('n');
		}
		return $name;
	}

	/**
	 * Check code of the answer
	 * @return true or FALSE
	 */
	private function _code($socket, $code)
	{
		$response = NULL;
		while (substr($response, 3, 1) != ' ') {
			if ( ! ($response = fgets($socket, 256)) ) {
				return FALSE;
			}
		}
		if ( ! (substr($response, 0, 3) == $code) ) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Close the socket and clean up
	 * @return void
	 */
	private function error($message)
	{
		global $tm;

		if ($this->box == 1) {
			$tm->error($message, 0, 1);
		} else {
			$tm->error($message, 0);
		}
	}

	/**
	 * Close the socket and clean up
	 * @return void
	 */
	private function close()
	{
		if (is_resource($this->socket))
		{
			fclose($this->socket);
		}
	}
}
