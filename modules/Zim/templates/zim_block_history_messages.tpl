<table class="zim-block-history-messagetable">
	<tr class="zim-block-history-messageheader"><td class="zim-block-history-messagesetwidth">From</td><td class="zim-block-history-messagesetwidth">To</td><td>Message</td></tr>
	{foreach from=$messages item='message'}
		<tr><td class="zim-block-history-messagesetwidth">{$message.from.uname}</td><td class="zim-block-history-messagesetwidth">{$message.to.uname}</td><td>{$message.message}</td></tr>
	{/foreach}
</table>