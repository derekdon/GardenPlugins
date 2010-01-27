// Based on Arne Diekmann, Author of the AnonymizeURLs Plugin js snippet.

var WebRoot = '';
// Get the protocol and WebRoot for the current controller via an ajax call
// "/plugin/getwebroot" maps to magic method "PluginController_GetWebRoot_Create"
$.getJSON("/plugin/getwebroot", function (json)
{
    WebRoot = json.WebRoot;
});