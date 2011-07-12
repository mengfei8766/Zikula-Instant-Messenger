<form class="z-form" id="zim_performance" action="#">
    <fieldset>
        <legend id="zim-block-groupdrag">{gt text='Add a new group'}</legend>
        <div class="z-formrow">
            <label for="zim-block-groupname">{gt text='Group Name'}</label>
            <input id="zim-block-groupname" type="text" name="zim-block-groupname"/>
        </div>   
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src="button_ok.png" set="icons/extrasmall" __alt="Add" __title="Add" __text="Add" id="zim-block-group-submit"}
        <a id="zim-block-group-cancel" href="#" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
</form>
