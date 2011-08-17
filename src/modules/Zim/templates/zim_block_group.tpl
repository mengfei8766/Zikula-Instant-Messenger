
<li id='{literal}zim_group_#{gid}{/literal}' class='zim-group' > 
    <div><div id='{literal}zim_groupname_#{gid}{/literal}'>{literal}#{groupname}{/literal}</div>
    <div class="zim-group-toggle-container">
        {if $modvars.Zim.contact_groups eq 1}
            {img class="zim-group-delete tooltips" modname="Zim" src="close.png" __alt="Delete Group" __title="Delete Group"}
        {/if}
        {img class="zim-group-toggle tooltips" modname=core src=arrow_down_grey.png set=global __alt="Toggle Group" __title="Toggle Group"}
        </div>
    </div>
</li>
<ul id='{literal}zim-group-list-#{gid}{/literal}'></ul>