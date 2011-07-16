<div id="zim-block" class="zim-block">
    <div class="zim-block-head" id="zim-block-head">
        {img src="indicator_circle.gif" modname="core" set="ajax" title="Update Status" alt="Status" class="status-img tooltips" id="zim-my-status"}
        <p id="zim-uname" class="tooltips" title="Click to Edit Username">{$uname}</p>
        <div style="float:right;">
        	{if $modvars.Zim.contact_groups eq 1}
       			{img src="add_group.png" modname="core" set="icons/extrasmall" title="Add New Group" alt="Group Add" class="tooltips" id="zim-group-create" style="cursor:pointer;"}
        	{/if}
        	{img src="text_block.png" modname="core" set="icons/extrasmall" title="View History" alt="Group Add" class="tooltips" id="zim-view-history" style="cursor:pointer;"}
    	</div>
    </div>
    <input type="hidden" name="authid" id="zimauthid" value="{insert name="generateauthkey" module="Zim"}" />
    <input type="text" name="zim-contact-search" id="zim-contact-search"/>
    <ul id="zim-block-contacts">
    </ul>
</div>
