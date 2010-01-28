<?php if (!defined('APPLICATION')) exit();
/*
Based on Mark O'Sullivan's (mark@vanillaforums.com) InThisDiscussionPlugin.
*/

// Define the plugin:
$PluginInfo['GoogleAdSenseAfterDiscussion'] = array(
   'Name' => 'Google AdSense > After Discussion',
   'Description' => "Adds your Google AdSense code after the entire discussion on the discussion page in Vanilla.",
   'Version' => '1',
   'Requires' => FALSE,
   'HasLocale' => FALSE,
   'Author' => "Derek Donnelly",
   'AuthorUrl' => 'http://www.derekdonnelly.com',
   'RegisterPermissions' => FALSE,
   'SettingsPermission' => FALSE
);

class GoogleAdSenseAfterDiscussionPlugin implements Gdn_IPlugin
{
    // Render Adsense unit only when the number of comments in this discussion exceed this value
    // Set to 0 if you always want it to render
    public $MinComments = 0;
    
    public function DiscussionController_BeforeDiscussionRender_Handler(&$Sender)
    {
        if(!IsSet($Sender->Discussion)) return;
        if($Sender->Discussion->CountComments > $this->MinComments)
        {
            include_once(PATH_PLUGINS.DS.'GoogleAdSenseAfterDiscussion'.DS.'class.adsenseafterdiscussionmodule.php');
            $AdSenseAfterDiscussionModule = new AdSenseAfterDiscussionModule($Sender);
            $Sender->AddModule($AdSenseAfterDiscussionModule);
        }
    }

    public function Setup()
    {
        // No setup required
    }
}