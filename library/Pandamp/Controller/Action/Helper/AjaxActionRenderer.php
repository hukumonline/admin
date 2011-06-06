<?php
class Pandamp_Controller_Action_Helper_AjaxActionRenderer
{
	function ajaxActionRenderer($url)
	{
		$o = new Pandamp_Core_Guid();
		$guid = $o->generateGuid('ajact');
		$s = "
		<div id='out$guid'></div>
		<div id='progress$guid'>&nbsp;Loading...</div>

			<script type='text/javascript'>
			$(document).ready(function()
			//setTimeout(function()
			{
				   $.ajax({
				   type: 'POST',
				   url: '$url',
				   beforeSend: function()
				   {
				   		$('#progress$guid').show();
				   },
				   success: function(msg){
				     $('#out$guid').html(msg);
				     $('#progress$guid').hide();
				   }
				 });
			 //}, 50);
			});
			</script>

		";

		return $s;
	}
}