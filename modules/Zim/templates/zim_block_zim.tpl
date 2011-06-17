<div id="zim-block" class="zim-block">
    <div class="zim-block-head" id="zim-block-head">
        <img id="zim-my-status" class="status-img" src="images/ajax/indicator_circle.gif" alt="Status" title="Update Status" />
        <p id="zim-uname">{$uname}</p>
        <div style="float:right;border-left: 1px solid #D4D5D6;padding:0 3px 0 3px;font-weight:bold;cursor:pointer;" id="zim-settings-button">Settings</div>
    </div>
    <input type="hidden" name="authid" id="zimauthid" value="{insert name="generateauthkey" module="Zim"}" />
    <input type="text" name="zim-contact-search" id="zim-contact-search"/>
    <ul id="zim-block-contacts">
    </ul>
</div>
