<div id="zim-block-history-box-header"
	class="zim-block-history-box-header">History
<div class="zim-block-history-box-header-icons"><img alt="close"
	id='zim-block-history-close' src="modules/Zim/images/close.png" /></div>
</div>
<div id="zim-block-history-contacts">
<ul>
	{foreach from=$contacts item='contact'}
	<li id='contact_history_user{$contact.uid}'>{$contact.uname}{img
	modname=core set=icons/extrasmall src=mail_delete.png title='Delete
	History' alt='Delete History' class='tooltips'}</li>
	{/foreach}
</ul>
</div>
<div id="zim-block-history-messages">Messages</div>
