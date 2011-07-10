/**
 * Zikula-Instant-Messenger(ZIM)
 * Main client-side JavaScript.
 * 
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

Event.observe(window, 'load', function() {
    Zim.init();
});

var Zim ={
    periodical_update_contact: '',
    settings: {
        execute_period: 6,
        contact_update_freq: 4,
        allow_offline_msg: 1
    },
    execute_count: 0,
    periodical_get_messages: '',
    messages_to_confirm: Array(),
    contact_template: '',
    message_template: '',
    settingsmenu_template: '',
    sentmessage_template: '',
    groupadd_template: '',
    group_template: '',
    my_uname: '',
    my_uid: '',
    status: '',
    status_colors:{
        '0': 'modules/Zim/images/icons/offline.png',
        '1': 'modules/Zim/images/icons/online.png',
        '2': 'modules/Zim/images/icons/busy.png',
        '3': 'modules/Zim/images/icons/invisible.png'
    },
    contacts: Array(),
    groups: Array(),
    init_in_progress: false,
    
    init: function() {
        Zim.init_in_progress = true;
        
        var pars = '';
        if (Zim.status !== '') {
        	var pars = "status=" + Zim.status;
        }
        
        new Zikula.Ajax.Request("ajax.php?module=Zim&type=ajax&func=init", {
            parameters: pars,
            onComplete : function(req) {
                if (!req.isSuccess()) {
                    Zikula.showajaxerror(req.getMessage());
                    return;
                    Zim.init_in_progress = false;
                }
                var data = req.getData();
                Zim.message_template = new Template(data.message_template);
                Zim.contact_template = new Template(data.contact_template);
                Zim.settingsmenu_template = data.settingsmenu_template;
                var message_container = document.createElement('div');
                Element.extend(message_container);
                message_container.addClassName('zim-block-message-container');
                message_container.id = 'zim-block-message-container';
                document.body.appendChild(message_container);
            
                Zim.status = data.status;
                Zim.my_uname = data.my_uname;
                Zim.my_uid = data.my_uid;
                Zim.sentmessage_template = new Template(data.sentmessage_template);
                Zim.groupadd_template = data.groupadd_template;
                if (typeof data.group_template != 'undefined') {
                	Zim.group_template = new Template(data.group_template);
                }
                Zim.contacts = data.contacts;
                Zim.groups = data.groups;
                Zim.settings = data.settings;
                new Tooltips('.tooltips', {});
                Zim.set_status_image();
                Zim.status_observer();
             
                if (Zim.status == '0') return;
                //Zim.contacts.each(function(item) {
                 //   Zim.toggle_contact_state(item);
                //});
                Zim.contacts.each(function(item) {
                	if (typeof item.uname != 'undefined') {
                		Zim.toggle_contact_state(item);
                	}
                });
                Zim.contacts.each(function(item) {
                	if (typeof item.gid != 'undefined') {
                		var show = {groupname: item.groupname, gid: item.gid};
                		if ($('zim_group_' + item.gid) == undefined) {
                			$('zim-block-contacts').insert(Zim.group_template.evaluate(show));
                		}
                        item.members.each(function(item2) {
                    		Zim.toggle_contact_state(item2, item.gid);
                    	});
                	}
                });

                Zim.periodical_update_contact = new PeriodicalExecuter(function(pe) {
                    Zim.update_contacts();
                }, Zim.settings.execute_period);
                
                if (typeof data.state != 'undefined') {
                    (data.state.windows).each(function(window) {
                        if(!has_open_message(window.uid)) {
                            Zim.add_message_box(window);
                        }
                    });
                    
                    (data.state.messages).each(function(message) {
                        Zim.add_message(message);
                    });
                }
                Zim.state.clear();
                Zim.contact_search_observer();
                
                
                Zim.uname_editor = new Ajax.InPlaceEditor('zim-uname', 'ajax.php?module=Zim&type=contact&func=update_username', {
                    okControl:false,
                    submitOnBlur:true,
                    cancelControl:false,
                    ajaxOptions: Zikula.Ajax.Request.defaultOptions(),
                    onFormCustomization: function(obj, form) {
                        $(form).observe('keypress',function(e) {
                            if(e.keyCode == Event.KEY_RETURN) {
                                e.stop();
                                e.element().blur();
                            }
                        });
                    },
                    callback: function(form, value) {
                        return 'uname='+encodeURIComponent(value);
                    },
                    onComplete: function(transport, element) {
                        transport = Zikula.Ajax.Response.extend(transport);
                        if (!transport.isSuccess()) {
                            this.element.innerHTML = Admin.Editor.getOrig(element.id);
                            Zikula.showajaxerror(transport.getMessage());
                            return;
                        }
                        var data = transport.getData();
                        this.element.innerHTML = data;
                    },
                    formId: 'uname'
                });

                Event.observe('zim-settings-button', 'click', function(event) {
                    Zim.open_settings_window();
                });
                Zim.init_in_progress = false;
            }
        });
    },
    
    update_contacts: function() {
        if( Zim.execute_count <= (Zim.settings.contact_update_freq - 1)) {
            Zim.get_messages();
            Zim.execute_count++;
            return;
        }
        Zim.execute_count = 1;
        if (Zim.periodical_update_contact.currentlyExecuting == false) {return false;}
        new Zikula.Ajax.Request("ajax.php?module=Zim&type=contact&func=get_online_contacts", {
            onComplete : function(req) {
                if (!req.isSuccess()) {
                    Zikula.showajaxerror(req.getMessage());
                    return;
                }
                var data = req.getData();
                var contacts = data.contacts;
                contacts.each(function(item) {
                	if (typeof item.uname != 'undefined') {
                		Zim.toggle_contact_state(item);
                	}
                });
                contacts.each(function(item) {
                	if (typeof item.gid != 'undefined') {
                		var show = {groupname: item.groupname, gid: item.gid};
                		if (typeof $('zim_group_' + item.gid) != undefined) {
                			$('zim-block-contacts').insert(Zim.group_template.evaluate(show));
                		}
                		item.members.each(function(item2) {
                    		Zim.toggle_contact_state(item2, item.gid);
                    	});
                	}
                });
                Zim.contacts = contacts;
                Zim.get_messages();
                
            }
        });
    },
    
    add_contact_observer: function(contact) {
        Event.stopObserving('contact_' + contact.uid); 
        Event.observe('contact_' + contact.uid, 'click', function(event) {
                 Zim.add_message_box(contact);
         });
    },
    
    contact_search_observer: function() {
        Event.observe('zim-contact-search', 'keyup', function(event) {
                var matches = Array();
                var partial = Array();
                if (event.element().value == '') {
                     $$('#zim-block-contacts li.zim-contact').each(function(item) {
                             item.show();
                     });
                };
                Zim.contacts.each(function(item) {
                        if (item.uid == Zim.my_uid) return;
                        var pos = (item.uname).indexOf(event.element().value) 
                        item.pos = pos;
                        if (pos < 0) {return;}
                        if (pos == 0) {
                            matches.unshift(item);
                            return;
                        } else {
                            partial.push(item);
                            //TODO sort by position or something
                        }
                });
                //TODO do i realkly need to concat?
                matches = matches.concat(partial);
                matches.sort(function sortNumber(a,b) {
                 return a.pos - b.pos;
                });
                $$('#zim-block-contacts li.zim-contact').each(function(item) {
                        var found = false;
                        matches.each(function(match) {
                            var itemid = item.id.replace('contact_', '');
                            if (match.uid == itemid) {
                                found = true;
                                return;
                            }
                        });
                        if (!found) {
                            item.hide();
                        } else {
                            item.show();
                        }
                });
        });
    
    },
    
    add_message_box: function(contact) {
        if (has_open_message(contact.uid)) {
            var src ='';
            if ($('zim-message-body-' + contact.uid).visible()) {
                $('zim-message-body-' + contact.uid).hide();
                src = $('zim-message-hide-' + contact.uid).readAttribute('src');
                src = src.replace('minus', 'plus');
                $('zim-message-hide-' + contact.uid).writeAttribute('src',src);
            } else {
                $('zim-message-body-' + contact.uid).show();
                src = $('zim-message-hide-' + contact.uid).readAttribute('src');
                src = src.replace('plus', 'minus');
                $('zim-message-hide-' + contact.uid).writeAttribute('src',src);
            }   
            return false;
        }
        if (contact.uid == Zim.my_uid) {
            return false;
        }
        var message_box = document.createElement('li');
        Element.extend(message_box);
        message_box.addClassName('zim-block-message-box');
        message_box.id = 'zim-block-message-'+contact.uid;
        var color = Zim.status_colors[contact.status];
        var show = {uname: contact.uname, uid: contact.uid, color: color};
        message_box.update(Zim.message_template.evaluate(show));
        $('zim-block-message-container').insert(message_box);
        new Draggable('zim-block-message-' +contact.uid, {
                handle: 'zim-message-header-'  + contact.uid
        });
        Event.observe('zim-message-hide-' + contact.uid, 'click', function(event) {
            var src = '';
            if ($('zim-message-body-' + contact.uid).visible()) {
                $('zim-message-body-' + contact.uid).blindUp();
                src = $('zim-message-hide-' + contact.uid).readAttribute('src');
                src = src.replace('minus', 'plus');
                $('zim-message-hide-' + contact.uid).writeAttribute('src',src);
            } else {
                $('zim-message-body-' + contact.uid).blindDown();
                src = $('zim-message-hide-' + contact.uid).readAttribute('src');
                src = src.replace('plus', 'minus');
                $('zim-message-hide-' + contact.uid).writeAttribute('src',src);
            }   
        });
        Event.observe('zim-message-close-' + contact.uid, 'click', function(event) {
            $('zim-block-message-'+ contact.uid).remove();
            Zim.state.remove_window(contact.uid);
        });
        Event.observe('zim-message-textbox-'+contact.uid, 'keypress', function(event) {
            if (Event.KEY_RETURN == event.keyCode) {
                var contents = $F('zim-message-textbox-'+contact.uid);
                if (contents.replace(/[\r\n]+/g, "") == ''){
                    $('zim-message-textbox-'+contact.uid).clear();
                    return false;
                }
                Zim.send_message(contact.uid, contents);
            }
        });
        Zim.state.add_window(contact.uid);
        if (contact.status != '0' || Zim.settings.allow_offline_msg == '1') {
            Form.Element.focus('zim-message-textbox-'+contact.uid);
        } else {
            $('zim-message-textbox-'+contact.uid).disable();
        }
    },
    send_message: function(uid,message) {
        var pars = "message=" + message +"&to=" + uid;
        new Zikula.Ajax.Request("ajax.php?module=Zim&type=message&func=send_new_message", {
            parameters: pars,
            onCreate: function() {
                $('zim-message-textbox-' + uid).clear();
                $('zim-message-textbox-' + uid).disable();
            },
            onComplete : function(req) {
                $('zim-message-textbox-' + uid).enable();
                if (!req.isSuccess()) {
                    Zikula.showajaxerror(req.getMessage());
                    return;
                }
                var data = req.getData();
                var msg = {
                    msg_to: uid,
                    msg_from: Zim.my_uid,
                    from: {uname:''},
                    message: message
                };
                var date = new Date();
                var created_at = date.getFullYear() + '-' + date.getMonth() +
                    '-' + date.getDate() +
                    ' ' + date.getHours() +
                    ':' + date.getMinutes() +
                    ';' + date.getSeconds();
                msg.created_at = created_at;
                Zim.add_message(msg);
            }
        });
    },
    
    get_messages: function() {
        var pars = Zim.state.params();
        if ((Zim.messages_to_confirm).length > 0) {
            var str = '';
            Zim.messages_to_confirm.each(function(item) {
                    str += "&confirm[]=" +item;
            });
            pars += str;
        }
        new Zikula.Ajax.Request("ajax.php?module=Zim&type=message&func=get_new_messages", {
            parameters: pars,
            onComplete : function(req) {
                if (!req.isSuccess()) {
                    Zikula.showajaxerror(req.getMessage());
                    return;
                }
                Zim.state.clear();
                Zim.messages_to_confirm.clear();
                var data = req.getData();
                var messages = data.messages;
                messages.each(function(message) {
                    Zim.add_message(message);
                });
            }
        });
    },
    
    add_message: function(message) {
        if (message.msg_from == message.msg_to) {
            Zim.confirm_message(message.mid);
            return;
        }
        var color = '';
        var to_uname = '';
        var window_uid = '';
        if (message.msg_from == Zim.my_uid) {
            window_uid = message.msg_to;
            to_uname = Zim.my_uname;
            color = '#0000FF';
        } else {
            window_uid = message.msg_from;
            to_uname = message.from.uname;
            user_status = message.from.status;
            color = '#FF0000';
        }
        var status = has_open_message(window_uid);
        if (!status) {
            status = false;
            Zim.contacts.each(function(item) {
                if (item.uid == window_uid) {
                    if (Zim.add_message_box(item) == false) {
                        //cant add window stop trying, last resort
                        Zim.confirm_message(message.mid);
                        return;
                    }
                    status = true;
                    throw $break;
                }
            });
            if (!status) {
                var contact = {
                    uid: window_uid,
                    uname: to_uname,
                    status: user_status
                };
                if (Zim.add_message_box(contact) == false) {
                    alert("could not add box");
                    return;
                }
            }
        }
        var show = {uname: to_uname, message: message.message.emote(), color: color, title: message.created_at};
        var element = (Zim.sentmessage_template.evaluate(show));
        
        $('zim-message-message-'+ window_uid).insert(element);
        if (!$('zim-message-body-' + window_uid).visible()) { 
            //$('zim-message-body-' + window_uid).show();
        }
        $('zim-message-message-'+ window_uid).scrollTop = $('zim-message-message-'+window_uid).scrollHeight;
        if (message.msg_to == Zim.my_uid) {
            Zim.confirm_message(message.mid);
            if (!Zim.init_in_progress) {
                var restore_color = '#ffffff';
                if (!$('zim-message-body-' + window_uid).visible()) {
                    restore_color = '#CAE2FC';
                    Event.observe('zim-message-header-' +  window_uid, 'mouseover', function(event) {
                        Event.stopObserving('zim-message-header-' +  window_uid, 'mouseover');
                        $('zim-message-header-' + window_uid).highlight({
                            startcolor: '#CAE2FC',
                            endcolor: '#ffffff',
                            restorecolor: '#ffffff'
                        });
                    });
                }
                $('zim-message-header-' + window_uid).highlight({
                    endcolor: '#CAE2FC',
                    restorecolor: restore_color
                });
            }
        }
        
        $$('#zim-message-message-' + window_uid +' div').each(function(msg) {
                new Tooltip(msg, {delay:750});
        });

    },
    
    confirm_message: function(mid) {
        (Zim.messages_to_confirm).push(mid);
    },
    
    get_contact: function(uid) {
        var pars = "uid=" + uid;
        new Zikula.Ajax.Request("ajax.php?module=Zim&type=contact&func=get_contact", {
            parameters: pars,
            onComplete : function(req) {
                if (!req.isSuccess()) {
                    Zikula.showajaxerror(req.getMessage());
                    return;
                }
                var data = req.getData();
                return data;
            }
        });
    },
    status_observer: function() {
         var context_status = new Control.ContextMenu('zim-my-status',{
                 leftClick: true,
                 animation: false
         });
         context_status.addItem({
                 label: '<img src="' + Zim.status_colors[1] + '" style="vertical-align: text-bottom;"/> Online',
                 callback: function(){Zim.set_status(1);}
         });
         context_status.addItem({
                 label: '<img src="' + Zim.status_colors[2] + '" style="vertical-align: text-bottom;"/> Away',
                 callback: function(){Zim.set_status(2);}
         });
         context_status.addItem({
                 label: '<img src="' + Zim.status_colors[3] + '" style="vertical-align: text-bottom;"/> Invisible',
                 callback: function(){Zim.set_status(3);}
         });
         context_status.addItem({
                 label: '<img src="' + Zim.status_colors[0] + '" style="vertical-align: text-bottom;"/> Offline',
                 callback: function(){Zim.set_status(0);}
         });
    },
    
    set_status: function(status) {
        if (status == Zim.status) {
        	return;
        } else if (status == 0) {
            Zim.periodical_update_contact.stop();
            Zim.contacts.each(function(item) {
                item.status = 0;
                Zim.toggle_contact_state(item);
            });
            Zim.uname_editor.dispose();
            ($('zim-block-message-container').childElements()).each(function(item) {
                $(item.id).remove();
            });
            $$('#zim-block-contacts li.zim-contact').each(function(item) {
                item.remove();
            });
        } else if (Zim.status == 0) {
            Zim.status = status;
            return Zim.init();
        }
        var pars = "status=" + status;
        new Zikula.Ajax.Request("ajax.php?module=Zim&type=contact&func=update_status", {
            parameters: pars,
            onComplete : function(req) {
                if (!req.isSuccess()) {
                    Zikula.showajaxerror(req.getMessage());
                    return;
                }
                var data = req.getData();
                Zim.status = data.status;
                Zim.set_status_image();
            }
        });
    },
    
    set_status_image: function() {
        var color = Zim.status_colors[Zim.status];
        var colours = Object.values(Zim.status_colors).concat(Array('images/ajax/indicator_circle.gif'));
        var src = ($('zim-my-status').readAttribute('src')).replace(new RegExp('(' + colours.join('|') + ')', 'g'), color);
        $('zim-my-status').writeAttribute({src: src});
    },
    
    toggle_contact_state: function(contact, groupid) { 
        if (contact.uid == Zim.my_uid) return;
        var color = Zim.status_colors[contact.status];
        var colours = Object.values(Zim.status_colors);
        if(!$('contact_'+contact.uid)) {
            var show = {uname: contact.uname, uid: contact.uid,color: color};
            if (typeof groupid == 'undefined') {
            	$('zim-block-contacts').insert(Zim.contact_template.evaluate(show));
            } else {
            	$('zim-group-list-' + groupid).insert(Zim.contact_template.evaluate(show));
            	//TODO: add user to existing group
            }
            Zim.add_contact_observer(contact);
        } else {
            var src = ($('zim_contact_status_img_'+contact.uid).readAttribute('src')).replace(new RegExp('(' + colours.join('|') + ')', 'g'), color);
            $('zim_contact_status_img_'+contact.uid).writeAttribute({src: src});
        }
        
        //Only update uname if the user was online before this stops from detecting invis users
        var old_element = Zim.contacts.find(function(c) {return c.uid == contact.uid});
        if (typeof(old_element) !== 'undefined' && (old_element.status !== 0 && contact.status !== 0)) {
            var uname = $$('#contact_'+contact.uid+' div');
            if(typeof(uname) !== 'undefined'){
                uname = uname.first().innerHTML;
                if (uname !== contact.uname) {
                    $$('#contact_'+contact.uid+' div').first().update(contact.uname);
                }
            }
            //update windows as well
            if (has_open_message(contact.uid)) {
                var box = $$('#zim-block-message-' + contact.uid + ' div.zim-message-contact-uname');
                if(typeof(box) !== 'undefined'){
                    uname = box.first().innerHTML;
                    if (uname !== contact.uname) {
                        $$('#zim-block-message-' + contact.uid + ' div.zim-message-contact-uname').first().update(contact.uname);
                    }
                }
            }
        }
        
        if (has_open_message(contact.uid)) {
            if (contact.status == "0" && !Zim.settings.allow_offline_msg) {
                $('zim-message-textbox-' + contact.uid).disable();
            } else {
                $('zim-message-textbox-' + contact.uid).enable();
            }
            var src = ($('zim-message-window-' + contact.uid).readAttribute('src')).replace(new RegExp('(' + colours.join('|') + ')', 'g'), color);
            $('zim-message-window-' + contact.uid).writeAttribute({src: src});
        }
           
    },
    
    state: {
        windows: Array(),
   
        windows_to_add: Array(),
        windows_to_del: Array(),
        
        add_window: function(uid) {
            Zim.state.windows_to_add.push(uid);
            Zim.state.windows_to_add.uniq();
            
            var window_found = false;
            Zim.state.windows.each(function(item){
            	if (item.user == uid) {window_found = true; throw $break;}
            });
            if (!window_found){
            	var sw = new StateWindow(uid);
            	Zim.state.windows.push(sw);
            }
            
            Zim.state.windows.push(uid);
            Zim.state.windows.uniq();
        },
        
        remove_window: function(uid) {
        	Zim.state.windows_to_del.push(uid);
            Zim.state.windows_to_del.uniq();
            
            var idx = 0;
            Zim.state.windows.each(function(item){
            	if (item.user == uid) {
            		Zim.state.windows.splice(idx,1);
            		throw $break;
            	}
            	idx = idx + 1;
            });
        },
        
        clear: function() {
            Zim.state.windows_to_add.clear();
            Zim.state.windows_to_del.clear();
        },
        
        params: function() {
            var state = '';
            Zim.state.windows_to_add.each(function(item) {
            	state = state + "&state_add[]=" + item;
            });
            Zim.state.windows_to_del.each(function(item) {
            	state = state + "&state_del[]=" + item;
            });
            Zim.state.windows.each(function(item) {
            	state = state + "&state_windows["+ item.user+"]=" + item.start_msg;
            });
            return state;
        }
    },

    open_settings_window: function() {
        var zim_settings_menu = document.createElement('div');
        Element.extend(zim_settings_menu);
        zim_settings_menu.addClassName('zim-settings');
        zim_settings_menu.setAttribute('id', 'zim-settings-menu');
        zim_settings_menu.update(Zim.settingsmenu_template);
        var top_offset = $('zim-settings-button').getHeight();
        
        zim_settings_menu.setStyle({
           top: top_offset + "px",
           left: $('zim-settings-button').positionedOffset().left + "px"
        });
        $('zim-block-head').appendChild(zim_settings_menu);
        if (zim_settings_menu.cumulativeOffset().left + zim_settings_menu.getWidth() > document.viewport.getDimensions().width) {
        	$('zim-settings-menu').setStyle({
                left: "0px"
             });
        }
        
        $('zim-settings-button').setStyle({
           'color': '#44bbff'
        });
        Event.stopObserving('zim-settings-button', 'click');
        var close_settings_window = function(event) {
            $('zim-settings-menu').remove();
            Event.stopObserving('zim-settings-button', 'click');
            Event.observe('zim-settings-button', 'click', function(event) {
                Zim.open_settings_window();
            });
            $('zim-settings-button').setStyle({
                'color': ''
            });
        };
        Event.observe('zim-settings-button', 'click', close_settings_window);
        
        if ($('zim-view-history') != undefined)
        {
        	Event.observe('zim-view-history', 'click', function(event){
        		close_settings_window();
        		if ($('zim-block-history-box') != undefined) {
        			return;
        		}
                new Zikula.Ajax.Request("ajax.php?module=Zim&type=history&func=get_template", {
                    onComplete : function(req) {
                        if (!req.isSuccess()) {
                            Zikula.showajaxerror(req.getMessage());
                            $('zim-block-history-box').remove();
                            return;
                        }
                        var data = req.getData();
                        var history_box = document.createElement('div');
                        Element.extend(history_box);
                        history_box.addClassName('zim-block-history-box');
                        history_box.id = 'zim-block-history-box';
                        history_box.update(data.template);
                        $(document.body).insert(history_box);
                        var user_nodes = $('zim-block-history-contacts').getElementsBySelector('li');
                        user_nodes.each(function(node){
                        	Event.observe(node.id, 'click', function(event){
                        		var pars = "contact=" + (node.id).replace('contact_history_user', '');
                        		new Zikula.Ajax.Request("ajax.php?module=Zim&type=history&func=get_history", {
                                    parameters: pars,
                                    onComplete : function(req) {
                                        if (!req.isSuccess()) {
                                            Zikula.showajaxerror(req.getMessage());
                                            return;
                                        }
                                        var data = req.getData();
                                        $('zim-block-history-messages').update(data.template);
                                    }
                                });
                        	});
                        	var del = node.getElementsBySelector('img');
                        	Event.observe(del[0], 'click', function(event){
                        		Event.stopObserving(node.id, 'click');
                        		var pars = '&uid=' + (node.id).replace('contact_history_user', '');
                        		new Zikula.Ajax.Request("ajax.php?module=Zim&type=history&func=delete", {
                                    parameters: pars,
                                    onComplete : function(req) {
                                        if (!req.isSuccess()) {
                                            Zikula.showajaxerror(req.getMessage());
                                            return;
                                        }
                                        node.remove();
                                        $('zim-block-history-messages').update('');
                                    }
                                });
                        	});
                        });
                        new Draggable('zim-block-history-box', {
                            handle: 'zim-block-history-box-header'
                        });
                        Event.observe('zim-block-history-close', 'click', function(event){
                        	history_box.remove();
                        });
                    }
                });
        	});
        }
        
        if ($('zim-group-create') != undefined) {
        	Event.observe('zim-group-create', 'click', function(event){
        		close_settings_window();
        		if ($('zim-block-group-box') != undefined) {
        			return;
        		}
        		var group_box = document.createElement('div');
                Element.extend(group_box);
                group_box.addClassName('zim-block-group-box');
                group_box.id = 'zim-block-group-box';
                group_box.update(Zim.groupadd_template);
                $(document.body).insert(group_box);
                new Draggable('zim-block-group-box', {
                    handle: 'zim-block-groupdrag'
                });	
                Event.observe('zim-block-group-submit', 'click', function(event){
                	Event.stop(event);
                	var pars = "&groupname=" + $('zim-block-groupname').getValue();
                	new Zikula.Ajax.Request("ajax.php?module=Zim&type=group&func=create_group", {
                		parameters: pars,
                		onComplete : function(req) {
	                		if (!req.isSuccess()) {
	                            Zikula.showajaxerror(req.getMessage());
	                            return;
	                        }
	                        var data = req.getData();
	                        var show = {groupname: data.groupname, gid: data.gid};
	                        $('zim-block-contacts').insert(Zim.group_template.evaluate(show));
	                        //TODO: group was added
	                        $('zim-block-group-box').remove();
	                        
                		}
                	});
                });
                Event.observe('zim-block-group-cancel', 'click', function(event){
                	Event.stop(event);
                	$('zim-block-group-box').remove();
                });
        	});
        }
    }
};

StateWindow = Class.create();
StateWindow.prototype = {
	start_msg: null,
	user: null,
	
	initialize: function(user) {  
		this.user = user;
		this.start_msg = null;
	},

	add_msg: function(message) {
		if (message.uid !== this.user || message.uid !== Zim.my_uid) {
			return;
		}
		if (message.mid > this.start_msg) {
			this.start_msg = message.mid;
		}
	}
}

function contact_in_list(uid, contacts) {
    var result = false;
    contacts.each(function(item) {
            if (item.uid == uid) {
                result = true;
                return;
            }
    });
    return result;
}

function has_open_message(uid) {
    var status = false;
    ($('zim-block-message-container').childElements()).each(function(item) {
        var mid = (item.id).replace('zim-block-message-', '');
        if (mid == uid) {
            status = true;
        }
    });
    return status;
}
