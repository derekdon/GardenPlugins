<?php if(!defined('APPLICATION')) die();

$PluginInfo['SSLControllers'] = array(
    'Name' => 'SSLControllers',
    'Description' => 'SSL Enabled Controllers',
    'Version' => '0.9',
    'Author' => "Derek Donnelly",
    'AuthorUrl' => 'http://www.derekdonnelly.com',
);

/**
* You will need the first five core file updates from Garden / Commit History / 2010-01-26
* See http://github.com/lussumo/Garden/commits/master
* 
* Change list 
* 5: fix for the Routing with wild-cards and back-reference (Not sure if the previous 4 updates use this, get it to be sure)
* 4: Changed menumodule to account for https as well as http (it is all handled within the Url() function now). 
* 3: Changed IAuthenticator interface to include the Protocol definition. Fixed handshakeauthenticator to implement Protocol method.
* 2: Added protocol to password authenticator. 
* 1: Changed default javascript definitions to be overridable.
*
* If you haven't customisied these files, it might be easier just to get the latest versions from the core. 
* They primarily enable us to override the default WebRoot definition and set the authenticator's protocol on demand.
* Some are probably not used by the core methods this plugin calls, but just get them to be sure. 
* 
* class.url.php
* class.dispatcher.php
* class.passwordauthenticator.php
* class.handshakeauthenticator.php
* interface.iauthenticator.php
* functions.render.php
* functions.general.php
* class.menumodule.php
* bootstrap.php
*
* PLEASE CHECK THIS PLUGIN IS WORKING AS EXPECTED ON YOUR STAGING SERVER BEFORE YOU PUT IT INTO PRODUCTION! 
* CHECK THEN RECHECK AJAX FORMS! EHCOING ANYTHING FROM HERE WILL PROBABLY STOP THEM FROM WORKING!
*
*/

class SSLControllers implements Gdn_IPlugin
{
    // Class defaults
    protected $_SSLSupport = TRUE;
    protected $_SecureControllers = array('entrycontroller', 'utilitycontroller', 'settingscontroller', 'postcontroller'); // Default controllers to secure at setup
    protected $_SecureSession = FALSE;
    protected $_HTTP_PROTOCOL = 'http://'; // Why do I get an error when I try to define constants?
    protected $_HTTPS_PROTOCOL = 'https://';
    protected $_ProtocolWebRoot = '';
    
    // TODO: Remove Tracing/Testing
    // TRUE gives you info re the current controller etc, but *** NONE OF YOUR AJAX FORMS WILL WORK PROPERLY!!!! ***
    // Added traces to aid debugging, but they were the cause of most of my problems!
    public $trace = FALSE; 
    
    public function Base_Render_Before(&$Sender)
    {
        // Don't proceed if the sender is Leaving
        if((Isset($Sender->Leaving) && ($Sender->Leaving))) return;
        
        // Get the SSL support config setting
        $SSLSupport = Gdn::Config('Garden.SSL', $this->_SSLSupport);
        
        // Add protocol webroot definition regardless
        $Sender->AddDefinition('WebRoot', $this->_WebRoot());
        
        // Set the authenticator protocol regardless (Always use SSL if available?)
        $this->_SetAuthenticatorProtocol(($SSLSupport) ? $this->_HTTPS_PROTOCOL : $this->_HTTP_PROTOCOL);
        
        // Get url info
        $URLSecure = $this->_URLSecure();
        
        // Exit if we don't have SSL support and if the page is not running on https
        if(!$SSLSupport && !$URLSecure) return;
        
        // Set variables
        $Session = Gdn::Session();
        $ControllerName = $Sender->ControllerName;
        $RequestMethod = $Sender->RequestMethod;
        $SControllers = Gdn::Config('Garden.SecureControllers', $this->_SecureControllers);
        $SSession = Gdn::Config('Garden.SecureSession', $this->_SecureSession);
        $pageURL = $this->_PageURL();
        
        // Add a small jQuery helper to expose the protocol WebRoot to js calls (Might be useful)
        //$Sender->AddJsFile('plugins/SSLControllers/sslcontrollerhelper.js');
        
        // TODO: Remove Tracing/Testing
        if($this->trace) echo 'SecureControllers: ' . implode(', ', $SControllers) . '. ';
        if($this->trace) echo 'PageURL: ' . $pageURL;
        if($this->trace) echo ' Sender->RedirectUrl: ' . $Sender->RedirectUrl;
        if($this->trace) if(Isset($Sender->Form)) echo ' Target: ' . $Sender->Form->GetValue('Target', '');
        if($this->trace) echo ' SignInUrl: ' . Gdn::Authenticator()->SignInUrl();
        if($this->trace) echo ' SSession: ' . $SSession;
        
        
        // Check if the sender is a secure controller or if we should secure the session
        if(($SSLSupport) && (in_array($ControllerName, $SControllers) || ($SSession && $Session->IsValid())))
        {
            // Check if the controller has a form and that it is not posting back
            if(Isset($Sender->Form) && ($Sender->Form->IsPostBack() !== TRUE))
            {
                // Check if the current connection is secure
                if(!$URLSecure)
                {
                    // Get the secure page url and update the RedirectUrl if set to the original
                    $NewPageURL = $this->_GetUrlProtocol($pageURL, $this->_HTTPS_PROTOCOL);
                    if($Sender->RedirectUrl == $pageURL) $Sender->RedirectUrl = $NewPageURL;
                    
                    // Redirect back to controller using the secure url
                    Redirect($NewPageURL);
                }
            }
            
            // TODO: Remove Tracing/Testing
            if($this->trace) echo ' (Secure Controller)';
        }
        else // Unsecure controller
        {
            // Make sure the controller is not on/still using a secure connection
            if($URLSecure)
            {
                // Get the unsecure page url and update the RedirectUrl if set to the original
                $NewPageURL = $this->_GetUrlProtocol($pageURL, $this->_HTTP_PROTOCOL);
                if($Sender->RedirectUrl == $pageURL) $Sender->RedirectUrl = $NewPageURL;
                
                // Redirect back to controller using the unsecure url
                Redirect($NewPageURL);
            }
            
            // TODO: Remove Tracing/Testing
            if($this->trace) echo ' (Unsecure Controller)';
        }        
    }
    
    // Get the incoming url
    protected function _PageURL($AddPort = FALSE)
    {
        $URL = 'http';
        if((Isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on")) 
            $URL .= "s";
        $URL .= "://";
        
        // Set the protocol web root for use elsewhere in the class
        $this->_ProtocolWebRoot = $URL . $_SERVER["SERVER_NAME"] . '/';
        
        // Adding the port will cause problems with redirects if used
        if(($AddPort) && (Isset($_SERVER["SERVER_PORT"])) && ($_SERVER["SERVER_PORT"] != "80")) 
            $URL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        else
            $URL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

        return $URL;
    }
    
    // Get url with protocol
    protected function _GetUrlProtocol($URL = '', $Protocol = '')
    {
        // Error trapping
        $URL = ($URL != '') ? $URL : $this->_PageURL();
        $Protocol = ($Protocol != '') ? $Protocol : $this->_HTTP_PROTOCOL;
        if($Protocol != $this->_HTTP_PROTOCOL && $Protocol != $this->_HTTPS_PROTOCOL) return;
        
        // Remove current protocol
        if(substr($URL, 0, 8) == $this->_HTTPS_PROTOCOL) $URL = substr($URL, 8);
        if(substr($URL, 0, 7) == $this->_HTTP_PROTOCOL) $URL = substr($URL, 7);
        
        return $Protocol . $URL;
    }
    
    // Check if a url is secure
    protected function _URLSecure($URL = '')
    {
        $URL = ($URL != '') ? $URL : $this->_PageURL();
        return (substr($URL, 0, 8) == $this->_HTTPS_PROTOCOL) ? TRUE : FALSE;
    }

    // Get a valid protocol
    protected function _GetValidProtocol($Protocol = '')
    {
        $Protocol = ($Protocol != '') ? $Protocol : $this->_HTTP_PROTOCOL;
        return (($Protocol != $this->_HTTP_PROTOCOL) && ($Protocol != $this->_HTTPS_PROTOCOL)) ? $this->_HTTP_PROTOCOL : $Protocol;
    }
    
    // Get the web root with the protocol included
    protected function _WebRoot()
    {
        if($this->_ProtocolWebRoot == '') $this->_PageURL();
        return $this->_ProtocolWebRoot;
    }
    
    // Set Authenticator Protocol
    protected function _SetAuthenticatorProtocol($Protocol = '')
    {
        $Protocol = $this->_GetValidProtocol($Protocol);
        Gdn::Authenticator()->Protocol(rtrim($Protocol, '://'));
    }
    
    // Used by the javascript helper
    public function PluginController_GetWebRoot_Create(&$Sender)
    {
        echo json_encode(array("WebRoot" => $this->_WebRoot())); 
    }

    // Do setup
    public function Setup()
    {
        // Get user defaults and if the server can support SSL
        $Domain       = Gdn::Config('Garden.Domain', '');
        $SSLSpecified = Gdn::Config('Garden.SSL', FALSE);
        $SControllers = Gdn::Config('Garden.SecureControllers', $this->_SecureControllers);
        $SSession     = Gdn::Config('Garden.SecureSession', $this->_SecureSession);
        
        // Any value other then FALSE is true
        if($SSLSpecified) $this->_SSLSupport = TRUE; 
        
        // Remove any protocol prefixes from the domain
        if(($Domain != '') && ($Domain !== FALSE))
        {
            if(substr($Domain, 0, 7) == $this->_HTTP_PROTOCOL)
                $Domain = substr($Domain, 7);
            else if(substr($Domain, 0, 8) == $this->_HTTPS_PROTOCOL)
                $Domain = substr($Domain, 8);
        }
        
        // Do a system check on SSL support
        // TODO: Find out if this is the correct or best way to do this
        if(!$this->_SSLSupport)
        {
            $SSLCheck = @fsockopen('ssl://' . $Domain, 443, $errno, $errstr, 30); // Not working for me at the moment? Might be my staging server. Error: Name or service not known?
            $this->_SSLSupport = ($SSLCheck) ? TRUE : FALSE;
            fclose($SSLCheck);
        }
                
        // Update the config
        $Config = Gdn::Factory(Gdn::AliasConfig);
        $Config->Load(PATH_CONF.DS.'config.php', 'Save');
        $Config->Set('Garden.SSL', $this->_SSLSupport, TRUE); // Override what the user specified if we know it to be different
        $Config->Set('Garden.SecureControllers', $SControllers, TRUE); // Override okay as we have the user value
        $Config->Set('Garden.SecureSession', $SSession, TRUE); // Override okay as we have user value
        $Config->Set('Garden.Domain',$Domain, TRUE); // Override the current domain as we will need it protocol free
        $Config->Save();
    }
}