<div class="zim-block-history-download">
    <form class="z-form" id="download-history" action="{modurl modname='Zim' type='HistoryExport' func='main'}" method="post" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="contact" value="{$contact}"/>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <button type="submit" title="Download History">Download (.csv)</button>
    </form>
    <form class="z-form" id="download-history" action="{modurl modname='Zim' type='HistoryExport' func='get_html'}" method="post" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="contact" value="{$contact}"/>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <button type="submit" title="Download History">Download (html)</button>
    </form>
</div>