<div style="width:100%;border-bottom:1px solid #D4D5D6;height:15px;font-weight:bold;" id="zim-block-history-box-header" class="zim-block-history-box-header">History
        <div style="float:right;margin-right:5px;"><img alt="close"  id='zim-block-history-close' src="modules/Zim/images/close.png" /></div>
</div>
<div id="zim-block-history-contacts" style="width:30%;min-height:435px;100%;border-right:1px solid #D4D5D6;float:left;text-align:left;">
	<ul style="list-style:none;width:100%;margin:0;">
	{foreach from=$contacts item='contact'}
		<li id='contact_history_user{$contact.uid}' style="border-bottom:1px solid #D4D5D6;margin:0;font-weight:bold;cursor:pointer;">{$contact.uname}</li>
	{/foreach}
	</ul>
</div>
<div id="zim-block-history-messages" style="margin:0;padding:0;width:69%;float:left;text-align:left;">Messages</div>
