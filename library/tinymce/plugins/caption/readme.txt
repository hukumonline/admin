Caption plugin for TinyMCE
-----------------------------

About:
  This is an image caption dialog contributed by Gusztáv Pálvölgyi (Hungary, Sopron).
  
Usage:
  Select an image in your tiny editor and then use this plugin to add caption and modify some values described below: 
  You can set:
   * Caption text
   * Caption position (top/bottom)
   * Caption text-alignment
   * Caption text color
   * Caption text background color
   * Margin
   * Padding
   * Background color
   * Border color, style and width
   * Float and clear
  You can:
   * Save and load settings (values are stored in cookie)
   * Apply changes while dialog is still open, so you can preview the effect 
   
Numerical values are treated as pixel values. In color fields you caan enter any valid css color value e.g.:red, blue, #F00, #FeFa67, rgb (100,23,100) 

The plugin generates one of the following structures based on position setting:

  * <span class="imageCaption"><img/><span>captiontext</span></span>
  * <span class="imageCaption"><span>captiontext</span><img/></span>

So you can set in a stylesheet the properties for the imageCaption class and the enclosed span and img element.
In this case you only need to override some values in the plugin dialog that will generate the inline styles for you.

Compatibility:
  I developed this plugin on a linux machine and tested on Firefox 1.0, tiny_MCE 1.43. It worked fine.
  Some features were tested on WinXP IE too.

Installation instructions:
  * Copy the caption directory to the plugins directory of TinyMCE (/jscripts/tiny_mce/plugins).
  * Add plugin to TinyMCE plugin option list example: plugins : "caption".
  * Add this "span[*]" to extended_valid_elements option.

Initialization example:
  tinyMCE.init({
    theme : "advanced",
    mode : "textareas",
    plugins : "caption",
    theme_advanced_buttons3_add : "caption",
    extended_valid_elements : "span[*]"
  });
