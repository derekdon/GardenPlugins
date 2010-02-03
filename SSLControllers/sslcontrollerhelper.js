jQuery(document).ready(function($)
{ 
    // The following getJSON snippet is based on the AnonymizeURLs Plugin js snippet.

    var WebRoot = '';
    // Get the protocol and WebRoot for the current controller via an ajax call
    // "/plugin/getwebroot" maps to magic method "PluginController_GetWebRoot_Create"
    $.getJSON("/plugin/getwebroot", function (json)
    {
        WebRoot = json.WebRoot;
    });
    
    // Open href in a normal popup window or the current window
    SSLPopup = function() 
    {
        var UsePopups = definition('UsePopups', 0);
        var href = $(this).attr('href');
        if(UsePopups)
        {
            // TODO: Window sizing/positioning etc
            window.open(href, 'popup', 'height=500,width=400,toolbar=0,scrollbars=1,resizable=1').focus();
        }
        else 
            window.location = href;
        return false;
    }
    
    // Override Ajax popups
    $('a.Popup').click(SSLPopup);
    $('a.Popdown').click(SSLPopup);
    $('a.SignInPopup').click(SSLPopup);
});