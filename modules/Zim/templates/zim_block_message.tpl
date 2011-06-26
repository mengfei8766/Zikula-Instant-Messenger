{literal}
<div class="zim-message-header" id="zim-message-header-#{uid}">
    <img alt="status-img"  id="zim-message-window-#{uid}" src="#{color}"/>
    <div class="zim-message-contact-uname"> #{uname}</div>
    <div style='display:inline;float:right;'>
        <img alt="minimize"  id='zim-message-hide-#{uid}'  src="modules/Zim/images/minus.png" />
        <img alt="close"  id='zim-message-close-#{uid}' src="modules/Zim/images/close.png" />
    </div>
</div>
<div class="zim-message-body" id="zim-message-body-#{uid}">
	<div class="zim-message-message" id="zim-message-message-#{uid}"></div>
	<div class="zim-message-textbox">
		<textarea ignoreesc="true" id='zim-message-textbox-#{uid}'></textarea>
	</div>
</div>{/literal}
