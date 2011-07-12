
String.prototype.emote = function() {
    return Emoticon.replace(this);
};

        
var Emoticon = { 
    base : 'modules/Zim/images/emoticons/',
    emoticons: {
        ':)': ['005_ssmile.gif'],
        ':D': ['005_shappy.gif'],
        ':d': ['005_shappy.gif'],
        ';)': ['*WINK*'],
        ':(': ['005_ssad.gif'],
        ':O': ['005_ssuprised.gif'],
        ':o': ['005_ssuprised.gif'],
        ':\'(': ['005_scry.gif'],
        ':P': ['005_stongue.gif'],
        ':p': ['005_stongue.gif'],
    },
    
    replace: function(text) {
        for (var i in this.emoticons) {
            var econ = '<img src="' + Emoticon.base + this.emoticons[i] +'" />';
            text = text.replace(new RegExp(i.toString().replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&"), "g"), econ);
        }
        return text;
    }
}
