<?php if (!defined('APPLICATION')) exit();
/*
Based on Mark O'Sullivan's (mark@vanillaforums.com) InThisDiscussionPlugin.
*/

// Define the plugin:
$PluginInfo['GoogleAdSenseSidePanel'] = array(
   'Name' => 'Google AdSense > Side Panel',
   'Description' => "Adds your Google AdSense code to the side panel of the discussion page in Vanilla.",
   'Version' => '1',
   'Requires' => FALSE,
   'HasLocale' => FALSE,
   'Author' => "Derek Donnelly",
   'AuthorUrl' => 'http://www.derekdonnelly.com',
   'RegisterPermissions' => FALSE,
   'SettingsPermission' => FALSE
);

class GoogleAdSenseSidePanelPlugin implements Gdn_IPlugin
{
    // Render Adsense unit only when the number of comments in this discussion exceed this value
    // Set to 0 if you always want it to render
    public $MinComments = 2;
    public $MinCommentsInSession = 2;
    
    public function DiscussionController_BeforeDiscussionRender_Handler(&$Sender)
    {
        if(!IsSet($Sender->Discussion)) return;
        $Session = Gdn::Session();
        if($Sender->Discussion->CountComments > (($Session->IsValid()) ? $this->MinCommentsInSession : $this->MinComments))
        {
            include_once(PATH_PLUGINS.DS.'GoogleAdSenseSidePanel'.DS.'class.adsensesidepanelmodule.php');
            $AdSenseSidePanelModule = new AdSenseSidePanelModule($Sender);
            $Sender->AddModule($AdSenseSidePanelModule);
        }
    }

    public function Setup()
    {
        // No setup required
    }
}