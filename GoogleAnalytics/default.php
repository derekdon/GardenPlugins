<?php if(!defined('APPLICATION')) die();

$PluginInfo['GoogleAnalytics'] = array(
    'Name' => 'Google Analytics',
    'Description' => 'Simple Google Analytics, single domain version. Users must add their pageTracker param to the extension\'s default.php file.',
    'Version' => '1.0',
    'Author' => "Derek Donnelly",
    'AuthorUrl' => 'http://derekdonnelly.com',
    'RequiredApplications' => array('Vanilla' => '>=2.0'),
);

class GoogleAnalytics implements Gdn_IPlugin
{
    // Match this pageTracker param to the value listed in your tracking code. Should look something like this, UA-0000000-0
    public $pageTracker = "UA-0000000-0";

    // DON'T EDIT BELOW THIS LINE UNLESS YOU MODIFIED THE TRACKING CODE
    
    public function Base_AfterBody_Handler(&$Controller)
    {
        echo <<<EOT
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("$this->pageTracker");
pageTracker._trackPageview();
} catch(err) {}</script>
EOT;
    }
    
    public function Setup()
    {
    }
}