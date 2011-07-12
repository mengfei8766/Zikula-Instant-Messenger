<div id="zim-block" class="zim-block">
    <div class="zim-block-head" id="zim-block-head">
        {img src="indicator_circle.gif" modname="core" set="ajax" title="Update Status" alt="Status" class="status-img tooltips" id="zim-my-status}
        <p id="zim-uname" class="tooltips" title="Click to Edit Username">{$uname}</p>
        <div class="tooltips" style="float:right;border-left: 1px solid #D4D5D6;padding:0 3px 0 3px;font-weight:bold;cursor:pointer;" id="zim-settings-button" title="Click to Set Options">Options</div>
    </div>
    <input type="hidden" name="authid" id="zimauthid" value="{insert name="generateauthkey" module="Zim"}" />
    <input type="text" name="zim-contact-search" id="zim-contact-search"/>
    <ul id="zim-block-contacts">
    </ul>
</div>
