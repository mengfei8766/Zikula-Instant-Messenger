{admincategorymenu}
<div class="z-adminbox">
    {img modname='Zim' src='admin.png'}
    <h2>{gt text="Zikula Instant Messenger"}</h2>
    {modulelinks modname='Zim' type='admin'}
</div>
<div class="z-admincontainer z-clearfix">
    <div class="z-adminpageicon">{icon type="view" size="medium"}</div>
    <h3>{gt text="Zim Settings"}</h3>
<form class="z-form" id="zim_performance" action="{modurl modname='Zim' type='admin' func='settings_update'}" method="post" enctype="application/x-www-form-urlencoded">
	<fieldset>
		<input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
		<legend>{gt text='Performance Settings'}</legend>
		<div class="z-formrow">
			<label for="zim_performance_messagecheckperiod">{gt text='Message check period (seconds)'}</label>
			<input id="zim_performance_messagecheckperiod" type="text" name="settings[message_check_period]" value="{$modvars.Zim.message_check_period|safetext}"/>
		</div>
		<div class="z-formrow">
			<label for="zim_performance_contactupdatefreq">{gt text='Contact list refresh frequency'}</label>
			<input id="zim_performance_contactupdatefreq" type="text" name="settings[contact_update_freq]" value="{$modvars.Zim.contact_update_freq|safetext}"/>
		</div>
		<div class="z-formrow">
			<label for="zim_performance_timeoutperiod">{gt text='Contact timeout period (seconds)'}</label>
			<input id="zim_performance_timeoutperiod" type="text" name="settings[timeout_period]" value="{$modvars.Zim.timeout_period|safetext}"/>
		</div>
		<div class="z-formrow">
			<label for="zim_general_useminifiedjs">{gt text='Use minified JavaScript'}</label>
			<input id="zim_general_useminifiedjs" type="checkbox" name="settings_use_minjs" value="1" {if $modvars.Zim.use_minjs} checked="checked"{/if}/>
		</div>
	</fieldset>
	<fieldset>
		<legend>{gt text='General Settings'}</legend>
		<div class="z-formrow">
			<label for="zim_general_allowofflinemsg">{gt text='Allow offline messaging'}</label>
			<input id="zim_general_allowofflinemsg" type="checkbox" name="settings_allow_offline_msg" value="1" {if $modvars.Zim.allow_offline_msg} checked="checked"{/if}/>
		</div>
		<div class="z-formrow">
			<label for="zim_general_showoffline">{gt text='Show offline contacts in list'}</label>
			<input id="zim_general_showoffline" type="checkbox" name="settings_show_offline" value="1" {if $modvars.Zim.show_offline} checked="checked" {/if}/>
		</div>
		<div class="z-formrow">
			<label for="zim_performance_allowedtags">{gt text='Allowed message HTML tags (comma separated list)'}</label>
			<input id="zim_performance_allowedtags" type="text" name="settings[allowed_msg_tags]" value="{$modvars.Zim.allowed_msg_tags|safetext}"/>
		</div>
		<div class="z-formrow">
			<label for="zim_general_keephistory">{gt text='Keep message history'}</label>
			<input id="zim_general_keephistory" type="checkbox" name="settings_keep_history" value="1" {if $modvars.Zim.keep_history} checked="checked"{/if}/>
		</div>
	</fieldset>
    <fieldset>
        <legend>{gt text='Contact List Settings'}</legend>
        <div class="z-formrow">
            <label for="zim_contactlist_groups">{gt text='Groups'}</label>
            <select id="zim_contactlist_groups" name="settings[contact_groups]">
                    <option value="0" {if $modvars.Zim.contact_groups eq 0} selected="selected"{/if}>{gt text="Users may not create contact list groups, all contacts are in one list"}</option>
                    <option value="1" {if $modvars.Zim.contact_groups eq 1} selected="selected"{/if}>{gt text="Users may create contact list groups"}</option>
                    <option value="2" {if $modvars.Zim.contact_groups eq 2} selected="selected"{/if}>{gt text="Contact list groups are determined by Zikula user group"}</option>
            </select>
        </div>
    </fieldset>
	<div class="z-buttons z-formbuttons">
        {button src="button_ok.png" set="icons/extrasmall" __alt="Save" __title="Save" __text="Save"}
        <a href="{modurl modname=Zim type=admin func=main}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
</form>
</div>