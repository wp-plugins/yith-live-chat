<div id="YLC_console" class="yith-live-chat-console">
    <div id="YLC_sidebar_left" class="console-sidebar-left">
        <div class="sidebar-header">
            <?php _e( 'Users', 'ylc' ); ?>
            <a href="" id="YLC_connect" class="connect button button-disabled">
                <?php _e( 'Please wait', 'ylc' ); ?>
            </a>
        </div>
        <div id="YLC_users" class="sidebar-users">
            <div id="YLC_queue" class="sidebar-queue"></div>
            <div id="YLC_notify" class="sidebar-notify">
                <?php _e( "Please wait", 'ylc' ); ?>...
            </div>
        </div>

    </div>
    <div class="console-footer">
        <span><?php echo date( 'Y' ); ?> YITH Live Chat</span>
    </div>
    <div id="YLC_popup_cnv" class="chat-content chat-welcome"></div>
    <div id="YLC_sidebar_right" class="console-sidebar-right">
    </div>
</div>
<script type="text/javascript">

    ( function ( $ ) {
        $( document ).ready( function() {

            var last_cnv_id         = null,
                last_user_id        = null,
                last_msg_id         = null,
                ls_ntf              = $( '#YLC_notify' ),
                conn_btn            = $( '#YLC_connect' ),
                right_sidebar_html,
                list_interval,
                working             = false,
                checked_user_ids    = [],
                new_msgs_count      = {},
                fn_show_welcome_popup = function() {

                    $( '#YLC_popup_cnv' ).addClass( 'chat-welcome' ).html( '' );
                    $( '#YLC_sidebar_right' ).html( '' ).hide();

                };

            /**
             * Use plugin
             */
            $( 'body' ).ylc({
                app_id 			: ylc.app_id,
                user_info 		: {
                    user_id         : ylc.user_id,
                    user_name 	    : ylc.user_name,
                    user_email 	    : ylc.user_email,
                    user_type       : ylc.user_type,
                    avatar_type     : ylc.avatar_type,
                    avatar_image    : ylc.avatar_image
                },
                render 			: false,
                /**
                 * Before load
                 */
                before_load     : function () {

                    var self = this;

                    this.data.current_form = {
                        user_name   : ylc.user_name,
                        user_email  : ylc.user_email,
                        gravatar 	: ylc.user_email_hash
                    };

                    conn_btn.click( function( e ) {

                        e.preventDefault();

                        ls_ntf.show().html( self.strings.msg.connecting + '...' );

                        if( ! $( this ).data( 'logged' ) ) {

                           self.login( true );

                        } else if( $( this ).data( 'status', 'online' ) ) {

                            self.be_offline();

                        }

                    });
                },
                /**
                 * Current user is offline
                 */
                offline         : function () {

                    if ( ylc.is_premium ) { this.trigger_premium( 'play_sound', 'disconnected' ); }

                    ls_ntf.html( this.strings.msg.you_offline );

                    conn_btn.html( '<i class="fa fa-check-circle" style="color:#e54045;"></i> ' + this.strings.msg.offline_btn )
                        .data( 'logged', 0 )
                        .data( 'status', 'offline ')
                        .removeClass( 'button-disabled' );
                },
                /**
                 * Authentication error
                 */
                auth_error      : function ( error ) {

                    conn_btn.removeClass( 'button-disabled' );

                    ls_ntf.hide().html( error.message ).fadeIn( 200 );

                },
                /**
                 * Authenticated in Firebase, not logged in yet
                 */
                auth            : function () {

                    ls_ntf.html( this.strings.msg.you_offline );

                    conn_btn.html( this.strings.msg.connect )
                        .data( 'logged', 0 )
                        .removeClass( 'button-disabled' );

                },
                /**
                 * Logged in successfully
                 */
                logged_in       : function ( user ) {

                    if ( ylc.is_premium ) { this.trigger_premium( 'play_sound', 'connected' ); }

                    this.listen_msgs();

                    conn_btn.removeClass( 'button-disabled' );

                    ls_ntf.hide().empty();

                    conn_btn.html( '<i class="fa fa-check-circle" style="color:#acc327;"></i> ' + this.strings.msg.online_btn )
                        .data( 'logged', 1 )
                        .data( 'status', 'online' )
                        .removeClass( 'button-disabled' );

                    this.purge_firebase();

                },
                /**
                 * Logged out
                 */
                logged_out      : function ( ) {

                    if ( ylc.is_premium ) { this.trigger_premium( 'play_sound', 'offline' ); }

                    ls_ntf.html( this.strings.msg.you_offline );

                    conn_btn.html( this.strings.msg.connect )
                        .data( 'logged', 0 )
                        .data( 'status', 'offline' )
                        .removeClass( 'button-disabled' );

                    fn_show_welcome_popup();

                },
                /**
                 * New user is online now
                 */
                user_online     : function ( user ) {

                    if( $.inArray( user.user_id, checked_user_ids ) == -1 && user.user_id != this.data.user.user_id ) {

                         if ( ylc.is_premium ) { this.trigger_premium( 'play_sound', 'online' ); }

                        if ( user.user_type != 'operator' )
                            this.notify( this.strings.msg.new_user_online, user.user_name + ' (' + user.user_type + ')', null, 'user_online' );

                        checked_user_ids.push( user.user_id );

                    }

                },
                /**
                 * New message sent to any online user
                 */
                new_msg         : function ( msg ) {

                    var obj_user    = $( '#YLC_chat_user_' + msg.user_id ),
                        obj_count   = obj_user.find( '.chat-count' ),
                        total_msg   = parseInt( obj_user.data( 'count' ) );

                    if( ! msg.old_msg && ! msg.first_load && msg.user_id != this.data.user.user_id && msg.user_type != 'operator' ) {

                        total_msg = total_msg + 1;

                        new_msgs_count[ msg.user_id ] = total_msg;

                        obj_user.addClass( 'new-msg' ).data( 'count', total_msg );
                        obj_count.html( '(' + total_msg + ')' );

                        if ( ylc.is_premium ) { this.trigger_premium( 'play_sound', 'new-msg' ); }

                        this.notify( this.strings.msg.new_msg, msg.user_name + ': ' + msg.msg, null, 'new_msg' );

                    }

                    if( this.data.user.conversation_id == msg.conversation_id ) {

                        $( '#YLC_load_msg' ).remove();

                        this.add_msg( msg, last_user_id, last_msg_id );

                        last_user_id = msg.user_id;

                        if( last_user_id != msg.user_id || !last_msg_id )
                            last_msg_id = msg.msg_id;

                    }

                },
                /**
                 * Conversation messages loaded
                 */
                cnv_msgs_loaded : function ( total_msgs ) {

                    if( ! total_msgs )
                        $( '#YLC_load_msg' ).html( this.strings.msg.no_msg + '.' );
                    else
                        $( '#YLC_load_msg' ).empty();

                },
                /**
                 * User added
                 */
                user_added      : function ( user_id ) {

                    var obj_user = $( '#YLC_chat_user_' + user_id );

                    if ( user_id == this.data.active_user_id) {

                        obj_user.addClass( 'chat-active' ).removeClass( 'new-msg' ).data( 'count', 0 ).find( '.chat-count' ).empty();
                        if ( new_msgs_count[ user_id ] != null )
                            new_msgs_count[ user_id ] = 0;

                    } else {

                        var msg_count = ( new_msgs_count[ user_id ] != null ) ? new_msgs_count[ user_id ] : 0;

                        if ( msg_count > 0 )
                            obj_user.data( 'count', msg_count ).find( '.chat-count' ).html( '(' + msg_count + ')' );

                    }

                },
                /**
                 * After load
                 */
                after_load      : function () {

                    var self = this;

                    $( document ).on( 'keydown', '#YLC_cnv_reply', function() {
                        $( this ).trigger( 'autosize.resize' );
                        $( window ).trigger( 'resize' );
                    } );

                    $( document ).on( 'focus', '#YLC_cnv_reply', function() {
                        $( this ).autosize( {
                            append: ''
                        } );
                    } );

                    /**
                     * When click user on the users list
                     */
                    $( document ).on( 'click', '#YLC_users li.free, #YLC_users li.busy', function() {

                        var obj_user = $( this );

                        if ( list_interval != null )
                            clearInterval( list_interval );

                        self.get_user_data( $( this ).data( 'id' ), function( user ) {

                            if( self.data.active_user_id )
                                $( '#YLC_chat_user_' + self.data.active_user_id ).removeClass( 'chat-active' );

                            obj_user.addClass( 'chat-active' ).removeClass( 'new-msg' ).data( 'count', 0 ).find( '.chat-count' ).empty();

                            $( '#YLC_popup_cnv' ).removeClass( 'chat-welcome' )
                                .html( self.get_template( 'console-conversation', {
                                    reply_ph 	: self.strings.msg.reply_ph,
                                    load_msg 	: self.strings.msg.please_wait + '...',
                                    avatar  	: self.set_avatar( self.data.user.user_type, {
                                        gravatar    :  self.data.user.gravatar,
                                        avatar_type :  self.data.user.avatar_type,
                                        avatar_image:  self.data.user.avatar_image
                                    } )
                                } ) );

                            $( '#YLC_cnv_reply' ).focus();

                            if( obj_user.data( 'id' ) !== self.data.user.user_id ) {

                                right_sidebar_html = self.get_template( 'console-user-tools', {
                                    save_chat_btn   : ( ! ylc.is_premium ) ? '' : self.trigger_premium( 'show_save_button', obj_user.data( 'cnv-id' ) ),
                                    obj_user_data   : obj_user.data( 'cnv-id' ),
                                    button_text     : self.strings.msg.end_chat
                                } );

                            }

                            right_sidebar_html = right_sidebar_html + self.get_template( 'console-user-info', {
                                ip_title            : self.strings.msg.user_ip,
                                ip_address          : user.user_ip ,
                                info_title          : self.strings.msg.user_email,
                                email               : user.user_email,
                                page_title          : self.strings.msg.user_page,
                                href_current_page   : ( user.current_page ) ? user.current_page : '#',
                                current_page        : ( user.current_page ) ? user.current_page : 'N/A'
                            } );

                            self.objs.cnv                   = $( '#YLC_cnv' );
                            self.data.user.conversation_id  = obj_user.data( 'cnv-id' );
                            self.data.active_user_id        = obj_user.data( 'id' );

                            self.reload_cnv( obj_user.data( 'cnv-id' ) );
                            self.manage_reply_box( last_cnv_id );

                            self.data.ref_cnv.child( obj_user.data( 'cnv-id' ) ).on( 'child_added', function( new_snap ) {

                                if ( new_snap.val() == 'closed' ) {

                                    $( '#YLC_cnv_reply').attr( 'disabled', 'disabled' );
                                    $( '.chat-cnv-input' ).addClass( 'chat-disabled' );
                                }

                            });

                            last_cnv_id = obj_user.data( 'cnv-id' );

                            $( '#YLC_sidebar_right' ).html( right_sidebar_html ).show();

                            if ( obj_user.data( 'chat' ) == 'free' ) {

                               self.data.ref_users.child( obj_user.data( 'id' ) ).child( 'chat_with' ).set( self.data.user.user_id );

                            } else {

                                if (  obj_user.data( 'chat' ) ==  self.data.user.user_id ) {
                                    $( '#YLC_end_chat').show();
                                } else {
                                    $( '#YLC_end_chat').hide();
                                }



                            }

                            if ( ylc.is_premium ) {
                               self.trigger_premium( 'show_chat_timer', obj_user.data( 'cnv-id' ) )
                            }


                            $( window ).trigger( 'resize' );

                        });

                    });

                    /**
                     * End chat
                     */
                    $( document ).on( 'click', '#YLC_save, #YLC_end_chat', function( e ) {

                        var btn             = $( this ),
                            ntf             = $( '#YLC_save_ntf' ),
                            delete_from_app = ( $( this ).attr( 'id' ) === 'YLC_end_chat' ),
                            now = new Date(),
                            end_chat = now.getTime();

                        if( working ) {
                            ntf.html( self.strings.msg.please_wait + '...' );
                            return;
                        }

                        working = true;

                        $( this ).addClass( 'button-disabled' );

                        ntf.html( self.strings.msg.saving + '...' );

                        if ( delete_from_app ) {

                            if ( list_interval != null )
                                clearInterval( list_interval );

                        }

                        if ( ylc.is_premium ){

                            self.trigger_premium( 'end_chat_console', $( this ).data( 'cnv-id' ), delete_from_app, end_chat, btn, ntf );

                        } else {

                            self.clear_user_data( $( this ).data( 'cnv-id' ), function() {

                                working = false;

                                btn.removeClass( 'button-disabled' );

                                setTimeout( function() {

                                    ntf.fadeOut( 500 );

                                }, 100 );

                                setTimeout( function() {

                                    fn_show_welcome_popup();

                                }, 1000 );

                            } );
                        }

                    });

                    /**
                     * Remove active user highlight when visitor already mouseover on the conversation
                     */
                    $( '#YLC_popup_cnv' ).mouseover( function() {

                        $( '#YLC_chat_user_' + self.data.active_user_id ).removeClass( 'new-msg' )
                            .data( 'count', 0 )
                            .find( '.chat-count' ).empty();

                    });

                    setInterval( function() {

                        $( '.chat-last-online' ).each( function( i ) {

                            $( this ).html( self.timeago( $( this ).data( 'time' ) ) );

                        });

                    }, 60000 );

                    var wait = 0;

                    setInterval( function() {

                        var new_wait = 0;

                        self.data.ref_users.once( 'value', function( snap ) {

                            var users = snap.val();

                            if( users !== null ) {

                                $.each( users, function( user_id, user ) {

                                    if( user ) {

                                        if(  user.status === 'wait' ) {

                                            new_wait = new_wait + 1;
                                        }

                                    }

                                });

                            }

                        });

                        wait = new_wait;

                        if ( new_wait > 0 ) {

                            $( '#YLC_queue' ).html( self.strings.msg.waiting_users.replace( /%d/i, new_wait ) ).show();

                        } else {

                            $( '#YLC_queue').hide();

                        }

                    }, 15000 );

                    window.onbeforeunload = function ( e ) {
                        var ev = e || window.event;

                        //IE & Firefox
                        if ( ev ) {
                            ev.returnValue = self.strings.msg.ntf_close_console;
                        }

                        // For Safari
                        return self.strings.msg.ntf_close_console;
                    };

                }

            },
                {
                <?php apply_filters( 'ylc_js_premium', '') ?>
                }
            );

            $( window ).resize(function() {


                var win_h       = $( window ).height(),
                    win_w       = $( window ).width(),
                    console_h   = win_h - 74;

                console.log( );


                <?php apply_filters( 'ylc_js_console', '') ?>


            }).trigger('resize');

        });
    } (window.jQuery || window.Zepto));

</script>