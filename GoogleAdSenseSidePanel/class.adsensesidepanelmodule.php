<?php if (!defined('APPLICATION')) exit();
/**
 * Renders Google AdSense code to the side panel of the discussion page in Vanilla.
 */
class AdSenseSidePanelModule extends Module
{
    // Replace this values which your own AdSense unit code parameters
    protected $_google_ad_client = 'pub-1824246552737234';
    protected $_google_ad_slot   = '4838139777';
    protected $_google_ad_width  = 250;
    protected $_google_ad_height = 250;
    
    // Should have a value or be an empty string. An extra class called AdSense has also been added to the Box. 
    protected $_BoxStyleOverride = 'margin:0px; padding:0px; background: none;';
    
    // Don't edit below this line unless you know what you are doing.

    public function __construct(&$Sender = '') { parent::__construct($Sender); }
    public function AssetTarget() { return 'Panel'; }
    
    public function ToString()
    {
        $String = '';
        ob_start();
        ?>
        <div class="Box AdSense" style="<?php echo IsSet($this->_BoxStyleOverride) ? $this->_BoxStyleOverride : ''; ?>">
            <?php
            echo <<<EOT
<script type="text/javascript"><!--
google_ad_client = "$this->_google_ad_client";
google_ad_slot   = "$this->_google_ad_slot";
google_ad_width  =  $this->_google_ad_width;
google_ad_height =  $this->_google_ad_height;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
EOT;
            ?>
         </div>
      <?php
      $String = ob_get_contents();
      @ob_end_clean();
      return $String;
   }
}