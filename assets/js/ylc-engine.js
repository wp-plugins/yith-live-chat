function string_replace( str, args ){

    for (var key in args){
        if ( args.hasOwnProperty( key ) ) {

            var regex = new RegExp( 'ylc\.' + key , 'gm' );
            str = str.replace( regex, args[ key ] )
        }
    }

    return str;
}

(function ( $, window, document, undefined ) {
	
	var YLC = "ylc",

		// The name of using in .data()
		data_plugin = "plugin_" + YLC,

        //Premium methods

		// Default options
		defaults = {
			app_id 				: "", 			// App ID
            render              : true,         // Render chatbox UI?
            users_list_id 		: "#YLC_users", // List users in HTML element or leave it blank if no list
            display_login 		: true,
            user_info 			: { 			// Default user info used when related field isn't sent by login/contact form
                id 				: null,
                name 			: null,
                email 			: null
            },
            styles              : {
                colors          : {
                    primary     : "#009edb",
                    link 	    : "#459ac4"
                },
                border_radius   : "5px 5px 0 0",
                anim            : {
                    type        : "bounceInUp",
                    anim_delay  : 1000
                },
                popup_width			: 370,      // px
                btn_width           : 260,
                form_width          : 260,
                company_avatar   	: ''
            },
            before_load 		: $.noop, // Called before starting to load content
            after_load	 		: $.noop, // Called after plugin/content is loaded
            on_connect			: $.noop, // Called when Firebase connection succeeded
            on_disconnect		: $.noop, // Called when Firebase connection failed
            auth				: $.noop, // Called when user authenticated in Firebase, but not logged in yet
            auth_error 			: $.noop, // Called when error occurred while user authenticated
            logged_in 			: $.noop, // Called when user logged in successfully
            logged_out 			: $.noop, // Called when user logged out
            offline 			: $.noop, // Current user is offline now!
            new_msg				: $.noop, // New message received in any conversation
            user_online			: $.noop, // New user is online
            user_offline		: $.noop, // A user appeared offline
            user_created		: $.noop, // New user created
            user_failed			: $.noop, // Failed to create new user
            cnv_msgs_loaded		: $.noop  // Current conversation messages loaded after calling reload_cnv() function

        };

	// The Plugin constructor
	function Plugin() {

        this.opts = $.extend( {}, defaults ); // Plugin instantiation : You already can access element here using this.el

    }
	
	Plugin.prototype = {
	
		init : function ( opts ) {

			// Extend opts ( http://api.jquery.com/jQuery.extend/ )
			$.extend( this.opts, opts );

			// Data holds variables to use in plugin
			this.data = {
                auth            : null, 		// Firebase auth reference
                ref             : null, 		// Firebase chat reference
                is_mobile       : false,
                active_user_id  : 0,
                mode            : "offline",    // Current mode
                logged          : false,        // Logged in?
                assets_url      : ylc.plugin_url,
                guest_prefix 	: "Guest-",
                primary_fg      : null, 		// Primary foreground
                primary_hover   : null, 		// Primary hover color
                link_fg         : null,         // Link foreground
                link_hover      : null, 		// Link hover color
                popup_status 	: "close",      // Popup status: open, close
                user 			: {}, 	        // User data
                current_form 	: {}, 	        // Current form data
                online_ops      : {} 	        // Online operators list
            };

            this.strings = {
                months          : [
                    ylc.strings.months.jan,
                    ylc.strings.months.feb,
                    ylc.strings.months.mar,
                    ylc.strings.months.apr,
                    ylc.strings.months.may,
                    ylc.strings.months.jun,
                    ylc.strings.months.jul,
                    ylc.strings.months.aug,
                    ylc.strings.months.sep,
                    ylc.strings.months.oct,
                    ylc.strings.months.nov,
                    ylc.strings.months.dec
                ],
                months_short    : [
                    ylc.strings.months_short.jan,
                    ylc.strings.months_short.feb,
                    ylc.strings.months_short.mar,
                    ylc.strings.months_short.apr,
                    ylc.strings.months_short.may,
                    ylc.strings.months_short.jun,
                    ylc.strings.months_short.jul,
                    ylc.strings.months_short.aug,
                    ylc.strings.months_short.sep,
                    ylc.strings.months_short.oct,
                    ylc.strings.months_short.nov,
                    ylc.strings.months_short.dec
                ],
                time            : {
                    suffix  : ylc.strings.time.suffix,
                    seconds : ylc.strings.time.seconds,
                    minute  : ylc.strings.time.minute,
                    minutes : ylc.strings.time.minutes,
                    hour    : ylc.strings.time.hour,
                    hours   : ylc.strings.time.hours,
                    day     : ylc.strings.time.day,
                    days    : ylc.strings.time.days,
                    month   : ylc.strings.time.month,
                    months  : ylc.strings.time.months,
                    year    : ylc.strings.time.year,
                    years   : ylc.strings.time.years
                },
                fields          : {
                    name        : ylc.strings.fields.name,
                    name_ph     : ylc.strings.fields.name_ph,
                    email       : ylc.strings.fields.email,
                    email_ph    : ylc.strings.fields.email_ph,
                    message       : ylc.strings.fields.message,
                    message_ph    : ylc.strings.fields.message_ph
                },
                msg             : {
                    chat_title          : ylc.strings.msg.chat_title,
                    prechat_msg         : ylc.strings.msg.prechat_msg,
                    welc_msg            : ylc.strings.msg.welc_msg,
                    start_chat          : ylc.strings.msg.start_chat,
                    offline_body        : ylc.strings.msg.offline_body,
                    busy_body           : ylc.strings.msg.busy_body,
                    close_msg           : ylc.strings.msg.close_msg,
                    close_msg_user      : ylc.strings.msg.close_msg_user,
                    reply_ph            : ylc.strings.msg.reply_ph,
                    send_btn            : ylc.strings.msg.send_btn,
                    no_op               : ylc.strings.msg.no_op,
                    no_msg              : ylc.strings.msg.no_msg,
                    sending             : ylc.strings.msg.sending,
                    connecting          : ylc.strings.msg.connecting,
                    writing             : ylc.strings.msg.writing,
                    please_wait         : ylc.strings.msg.please_wait,
                    chat_online         : ylc.strings.msg.chat_online,
                    chat_offline        : ylc.strings.msg.chat_offline,
                    your_msg            : ylc.strings.msg.your_msg,
                    end_chat            : ylc.strings.msg.end_chat,
                    conn_err            : ylc.strings.msg.conn_err,
                    you                 : ylc.strings.msg.you,
                    online_btn          : ylc.strings.msg.online_btn,
                    offline_btn         : ylc.strings.msg.offline_btn,
                    field_empty         : ylc.strings.msg.field_empty,
                    invalid_email       : ylc.strings.msg.invalid_email,
                    op_not_allowed      : ylc.strings.msg.op_not_allowed,
                    user_email          : ylc.strings.msg.user_email,
                    user_ip             : ylc.strings.msg.user_ip,
                    user_page           : ylc.strings.msg.user_page,
                    connect             : ylc.strings.msg.connect,
                    disconnect          : ylc.strings.msg.disconnect,
                    you_offline         : ylc.strings.msg.you_offline,
                    save_chat           : ylc.strings.msg.save_chat,
                    ntf_close_console   : ylc.strings.msg.ntf_close_console,
                    new_msg             : ylc.strings.msg.new_msg,
                    new_user_online     : ylc.strings.msg.new_user_online,
                    saving              : ylc.strings.msg.saving,
                    waiting_users       : ylc.strings.msg.waiting_users
                }
            };

            if ( ylc.is_premium ) {
               //this.premium = $.extend( {}, premium );
            }

			// Common objects
			this.objs = {
				btn				: null,
				popup 			: null,
				popup_header 	: null,
				cnv 	 		: null
			};

			// Callback: Before load
			if( false === this.trigger( "before_load" ) )
				return;

			var self = this;

			// Is mobile?
			if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test( navigator.userAgent ) ) {
				this.data.is_mobile = true;
			}

			// Get application token
			this.post( 'get_token', {}, function( r ) {
				if( !r.error ) {
					self.data.auth_token = r.token;

					self.run(); // Wait for token and then build UI

					if( ylc.is_op && ! ylc.is_front_end ){
                        self.auth();
					}
				}
			});
		},
        run : function() {

            this.render_btn();      // Render chat button
            this.render_popup();    // Render popup
            this.check_ntf();       // Check desktop notifications

        },
        /**
         * Authentication
         */
        auth : function( callback ) {

            var self = this;

            // Check if app id provided
            if( !this.opts.app_id ) {
                console.error( "App ID isn't provided" );
                return;
            }

            // Create app references
            this.data.ref       = new Firebase( "https://" + this.opts.app_id + '.firebaseIO.com' );
            this.data.ref_conn  = new Firebase( 'http://' + this.opts.app_id + '.firebaseIO.com/.info/connected' );
            this.data.ref_cnv   = new Firebase( 'http://' + this.opts.app_id + '.firebaseIO.com/chat_sessions' );
            this.data.ref_msgs  = new Firebase( 'http://' + this.opts.app_id + '.firebaseIO.com/chat_messages' );
            this.data.ref_users = new Firebase( 'http://' + this.opts.app_id + '.firebaseIO.com/chat_users' );

            // Check if Firebase connected or lost
            this.data.ref.child( '.info/connected' ).on( 'value', function( snap ) {

                if ( snap.val() === true ) {
                    self.trigger( 'on_connect' ); // Callback: Connected
                } else {
                    self.trigger( 'on_disconnect' ); // Callback: Connected
                }

            });

            // Log in
            if( this.opts.display_login )
                this.login( false, true, callback ); // Render button here
            else
                this.login( true, true, callback ); // Render button here

            // Callback: After plugin load
            this.trigger( 'after_load' );

        },
        /**
         * Login
         */
        login : function( new_user, render, callback ) {

            var self = this;

            this.manage_conn(); // Check user connection

            // Create new user now or it is just page refresh to check authentication status
            this.data._new_user = new_user;

            this.data.auth = this.data.ref.authWithCustomToken( this.data.auth_token, function( error ) { // Authenticate user

                if( error ) { // An error occurred while attempting login

                    console.error( error.code, error.message );

                    self.trigger( 'auth_error', error ); // Callback: Authentication error

                    self.display_ntf( self.strings.msg.conn_err, 'error' ); // Display error

                } else { // Authentication is succeed

                    self.trigger( 'auth' ); // Callback: Authenticated in Firebase, but not logged in yet

                    self.data.logged = true; // Now logged in

                    self.data.ref_users.once( 'value', function( snap ) { // Get operators and check current user

                        var users = snap.val(),
                            i = 0,
                            guests = 0,
                            wait = false,
                            title = self.strings.msg.chat_title;

                        if( users !== null ) {

                            var total_user = Object.keys( users ).length;

                            $.each( users, function( user_id, user ) {

                                i = i + 1; // Increase index

                                if( user ) {

                                    // If operator is online, save in operators list
                                    if( user.type == 'operator' && user.status === 'online' ) {

                                        self.data.online_ops[user.id] = user; // Increase total number of operators

                                    } else {

                                        if( user.name !== undefined ) {
                                            guests = guests + 1;
                                        }

                                    }

                                }

                                if( i === total_user ) { // Last index in the while

                                    if( !self.total_online_ops() ) { // Is there any online operator?
                                        // Offline mode
                                       self.show_offline(); // Show offline form

                                    } else { // Online mode

                                        if( guests >= ylc.max_guests ){

                                            wait = true;

                                            self.show_offline( true );

                                        } else {

                                            if ( self.opts.display_login ) {
                                                // Show login form
                                                self.show_login();

                                                // Online mode
                                            } else {

                                                // Show conversation
                                                self.show_cnv( true );

                                            }
                                        }

                                    }

                                    self.check_user( self.opts.user_info.id, wait ); // Get user from Firebase

                                }

                            });

                        } else {

                            self.show_offline(); // Show offline form

                            self.check_user( self.opts.user_info.id, false ); // Get user from Firebase

                        }

                        if( callback )
                            callback();


                    });

                }

            });

        },
        /**
         * Logout from Firebase
         */
        logout : function( logout_msg, end_chat ) {

            var self = this;

            if( this.data.user.id ) {

                // Save transcript and delete data from Firebase
                this.save_user_data( this.data.user.cnv_id, false, function() {

                    if ( end_chat ) {

                        self.push_msg( '-- ' + self.strings.msg.close_msg_user + ' --' );

                    }

                    self.data.ref_user.off();   // Don't listen current user

                    self.data.ref_users.off();  // Don't listen users

                    self.data.ref_msgs.off();   // Don't listen message anymore

                    //self.data.ref.unauth();    // Log user out from Firebase
                    //self.be_offline();          // Be offline

                    // Set mode
                    self.data.mode = 'offline';

                    if( self.data.ref_user ) {

                        // Set status offline in Firebase
                        self.data.ref_user.child( 'status' ).set( 'offline' );

                        // Set last online
                        self.data.ref_user.child( 'last_online' ).set( Firebase.ServerValue.TIMESTAMP );

                    }

                    self.trigger( 'logged_out', logout_msg ); // Callback: Logged out

                });

            }

            // Display offline form
            self.objs.popup_body
                .css( 'width', self.opts.styles.form_width + 'px' )
                .removeClass()
                .addClass( 'chat-body chat-form' )
                .empty()
                .html( self.get_template( 'offline', {
                    lead 		: self.strings.msg.close_msg,
                    form 		: ''
                }));

            // Resize window to ensure chat box is responsive
            $(window).trigger( 'resize' );

            setTimeout( function() {

                self.minimize();
                self.check_mode( true );

            }, 1000 );

            // Callback: Current user is offline now
            self.trigger( 'offline' );
        },
        /**
         * Just be offline, don't logout completely
         */
        be_offline : function() {

            // Set mode
            this.data.mode = 'offline';

            if( this.data.ref_user ) {

                // Set status offline in Firebase
                this.data.ref_user.child( 'status' ).set( 'offline' );

                // Set last online
                this.data.ref_user.child( 'last_online' ).set( Firebase.ServerValue.TIMESTAMP );

            }

            // Force user to be offline
            this.check_mode( true );

            // Callback: Current user is offline now
            this.trigger( 'offline' );

        },
        /**
         * Change mode if necessary!
         */
        check_mode : function( force_offline ) {

            if( ylc.is_front_end ) {

                var last_mode = this.data.mode;

                if( force_offline ) {

                    // Show offline
                    this.show_connecting();

                    // Update mode
                    this.data.mode = 'offline';

                    // No operators online!
                } else if( !this.total_online_ops() ) {

                    switch( last_mode ) { // Last mode

                        // Visitor is trying to login
                        case 'login':

                            this.show_offline(); // Show offline

                            break;

                        // Visitor is in conversation
                        case 'online':

                            // Disable reply box
                            if( this.opts.display_login ) {

                                $('#YLC_cnv_reply').addClass('chat-disabled')
                                    .attr( 'disabled', 'disabled' );

                                // No operators online!
                                this.display_ntf( this.strings.msg.no_op + '!', 'error' );

                                // If no login form, show user contact form
                            } else {

                                // Show offline
                                this.show_offline();

                            }

                            break;
                    }

                    // Update mode
                    this.data.mode = 'offline';


                    // Some operator(s) online now!
                } else {

                    // If last mode was online,
                    // re-activate reply box and clean notifications
                    if( last_mode === 'offline' ) {

                        // Disable reply box
                        $('#YLC_cnv_reply').removeClass('chat-disabled').removeAttr( 'disabled' );

                        this.clean_ntf(); // Clean notification

                    }

                    // Update mode
                    this.data.mode = ( this.opts.display_login && last_mode != 'online' ) ? 'login' : 'online';

                }

            }
        },
        /**
         * Save user data into DB
         */
        save_user_data : function( cnv_id, delete_from_app, callback ) {

            var self = this,
                r = null; // Response

            // First get conversation data
            this.data.ref_cnv.child( cnv_id ).once( 'value', function( snap_cnv ) {

                var cnv = snap_cnv.val();

                if( !cnv )
                    return;

                // Get user id
                var user_id = cnv.user_id;

                // Get user data
                self.data.ref_users.child( user_id ).once( 'value', function( snap_user ) {

                    var user_data = snap_user.val();

                    // Include conversation created time into user data
                    user_data.cnv_time = cnv.created_at;

                    // Get users messages from Firebase
                    self.data.ref_msgs.once( 'value', function( snap_msgs ) {

                        var msgs = snap_msgs.val(),
                            total_msgs = msgs ? Object.keys( msgs ).length : 0,
                            i = 0,
                            msgs_data = {};

                        if( msgs ) {

                            $.each( msgs, function( msg_id, msg ) {

                                // Increase index
                                i = i + 1;

                                if( msg.cnv_id === cnv_id ) {

                                    // Add user message into data
                                    msgs_data[msg_id] = msg;

                                    // Delete msg from app if requested
                                    if( delete_from_app )
                                        self.data.ref_msgs.child( msg_id ).remove();

                                }

                                if( total_msgs === i ) { // Last index

                                    // Add all user message into user data
                                    user_data.msgs = msgs_data;

                                    self.post( 'save_chat', user_data, function( r ) {

                                        if( callback )
                                            callback( r ); // Trigger callback

                                    });

                                }

                            });

                            // No message for checking...
                        } else if( callback ) {
                            callback( {} ); // Response is null here
                        }

                        if( delete_from_app ) {

                            // Delete user from Firebase
                            self.data.ref_users.child( user_id ).remove();

                            // Delete conversation from app if requested
                            self.data.ref_cnv.child( cnv_id ).remove();
                        }

                    });

                });

            });

        },
        /**
         * Show offline popup
         */
        show_offline : function( busy ) {

            var self = this;

            this.data.mode = 'offline'; // Update mode

            if( ylc.is_front_end ) {

                // Allow displaying?
                if (!this.allow_chatbox())
                    return;

                // Update popup header
                self.objs.popup_header.find('.chat-title').html(this.strings.msg.chat_title);

                // Update popup wrapper
                self.objs.popup.parent().removeClass().addClass('chat-offline');

                // Render popup body
                self.objs.popup_body
                    .css('width', this.opts.styles.form_width + 'px')
                    .removeClass()
                    .addClass('chat-body chat-form')
                    .empty()
                    .html(self.get_template('offline', {
                        lead: ( busy ) ? self.strings.msg.busy_body : self.strings.msg.offline_body,
                        form: ylc.is_premium ? self.premium.show_offline_form({
                            btn_text     : self.strings.msg.start_chat,
                            btn_color    : self.data.primary_fg,
                            btn_bg       : self.opts.styles.colors.primary,
                            field_name   : self.strings.fields.name,
                            name_ph      : self.strings.fields.name_ph,
                            field_email  : self.strings.fields.email,
                            email_ph     : self.strings.fields.email_ph,
                            field_message: self.strings.fields.message,
                            message_ph   : self.strings.fields.message_ph
                        }) : ''
                    }));

                // Resize window to ensure chat box is responsive
                $(window).trigger('resize');

                setInterval( function() {
                    var guests = 0;

                    self.data.ref_users.once( 'value', function( snap ) {

                        var users = snap.val();

                        if( users !== null ) {

                            $.each( users, function( user_id, user ) {

                                if( user ) {

                                    if(  user.type != 'operator' && user.status === 'online' ) {

                                        guests = guests + 1; // Increase index
                                    }

                                }

                            });

                        }


                    });

                    if ( guests < ylc.max_guests ) {

                        // Log in
                        if( self.opts.display_login )
                            self.login( false, true ); // Render button here
                        else
                            self.login( true, true ); // Render button here

                    }

                }, 5000 );

            }

        },
        /**
         * Show connecting popup
         */
        show_connecting : function() {

            // Turn back to "connecting" popup
            this.objs.popup_body
                .css( 'width', this.opts.styles.form_width + 'px' )
                .html( this.get_template( 'connecting', {
                lead: this.strings.msg.connecting + '...'
            }));

        },
        /**
         * Show login form in chat box
         */
        show_login : function( login_lead_msg, minimize ) {

            var self = this;

            if( ylc.is_front_end ) {

                // Allow displaying?
                if( !this.allow_chatbox() )
                    return;

                // Is it possible to show up login form?
                if( this.opts.display_login && this.total_online_ops() && this.objs.popup ) {
                    // Update mode
                    this.data.mode = 'login';

                    // Update popup header
                    this.objs.popup_header.find('.chat-title').html( this.strings.msg.chat_title );

                    // Update popup wrapper
                    this.objs.popup.parent().removeClass().addClass( 'chat-login' );

                    // Render popup body
                    this.objs.popup_body
                        .css( 'width', this.opts.styles.form_width + 'px' )
                        .removeClass()
                        .addClass('chat-body chat-form')
                        .empty()
                        .html( this.get_template( 'login', {
                            lead 	    	: login_lead_msg || this.strings.msg.prechat_msg,
                            btn_text 	    : this.strings.msg.start_chat,
                            btn_color    	: this.data.primary_fg,
                            btn_bg 		    : this.opts.styles.colors.primary,
                            field_name      : this.strings.fields.name,
                            name_ph         : this.strings.fields.name_ph,
                            field_email     : this.strings.fields.email,
                            email_ph        : this.strings.fields.email_ph
                        }));

                    // Resize window to ensure chat box is responsive
                    $(window).trigger( 'resize' );

                    // Login button hover
                    $( '#YLC_login_btn' ).hover(
                        function() {
                            $(this).css('background-color', self.data.primary_hover );
                        },
                        function() {
                            $(this).css('background-color', self.opts.styles.colors.primary );
                        }
                    );

                    // Send login form
                    $( '#YLC_login_btn' ).click( function() {

                       self.send_login_form();

                    });

                    // If user click enter in login form, send login form
                    $( '#YLC_popup_form' ).keydown( function( e ) {

                        // When clicks ENTER key (but not shift + ENTER )
                        if ( e.keyCode == 13 && !e.shiftKey ) {
                            e.preventDefault();

                            self.send_login_form();
                        }

                    });


                    // Login can't be shown up right now,
                    // So show current mode
                } else {

                    if( self.data.mode === 'online' )
                        this.show_cnv();
                    else
                        this.show_offline();

                }

                // Minimize?
                if( minimize )
                    this.minimize();

            }

        },
        /**
         * Send login form
         */
        send_login_form : function() {

            var self = this;

            // Display "Connecting" message
            this.display_ntf( this.strings.msg.connecting + '...', 'sending' );

            // Get login form data
            var form_data = $( '#YLC_popup_form' ).serializeArray(),
                form_length = form_data.length - 1;

            // Validate login form
            $.each( form_data, function( i, f ) {

                // Update current form data
                self.data.current_form[f.name] = f.value;

                    // Is empty?
                    if( !f.value ) {
                        self.display_ntf( self.strings.msg.field_empty, 'error' );

                        return false;
                    }

                    // Is valid email?
                    if( f.name === 'email' ) {

                        // Invalid email!
                        if( !self.validate_email( f.value ) ) {

                            self.display_ntf( self.strings.msg.invalid_email, 'error' );

                            return false;

                        } else {

                            // Create gravatar from email and add current form data
                            self.data.current_form.gravatar = self.md5( f.value );

                        }
                    }

                // Log user in now (form is valid)

                setTimeout( function() {

                    if( i === form_length ) {
                        self.login( true );
                    }

                }, 10000 );


            });

            return;

        },
        /**
         * Check user if exists in Firebase
         */
        check_user : function( user_id, wait ) {

            var self = this;

            // User reference
            this.data.ref_user = this.data.ref_users.child( user_id );

            if( wait ) {

                this.data.ref_user.child( 'status' ).set( 'wait' );

            } else {

            // Get user
            this.data.ref_user.once( 'value', function( snap ) {

                var user_data = snap.val();

                // User data must always be object
                if( !user_data )
                    user_data = {};

                // Get user now
                self.get_user( user_id, user_data );

            }); }

            // Check current user connectivity
            this.data.ref_user.on( 'child_removed', function( snap ) {

                var user = snap.val();

                if( !user )
                    return;

                if( self.data.mode === 'online' && !user.status )
                    self.logout();

            });
        },
        /**
         * Get user from Firebase. If not exists, create new one
         */
        get_user : function( user_id, user_data, callback ) {

            var self = this;

            // Get current user data
            if( user_data.id ) {

                // Get user data
                this.data.user = user_data;

                // Update current mode in Firebase
                this.data.ref_user.child( 'status' ).set( 'online' );

                // Update other user data
                this.data.ref_user.child( 'ip' ).set( ylc.ip );
                this.data.ref_user.child( 'current_page' ).set( ylc.current_page );

                // Also update basic user information in any case
                if( ylc.user_name && !ylc.is_front_end ) {
                    this.data.ref_user.child( 'name' ).set( ylc.user_name );
                    this.data.ref_user.child( 'email' ).set( ylc.user_email );
                    this.data.ref_user.child( 'gravatar' ).set( ylc.user_email_hash );
                }

                // Show conversation
                if( this.total_online_ops() ) {
                    this.show_cnv();
                } else {
                    this.show_offline();
                }

                // Check user connection
                this.manage_conn();

                // Callback: Logged in successfully
                self.trigger( 'logged_in', this.data.user );

                // Now listen users activity
                self.listen_users();

                if( callback )
                    callback();


                // Create new user
            } else if( this.data._new_user === true ) {

                // Create new conversation
                var cnv = this.data.ref_cnv.push({
                        user_id 	: user_id,
                        created_at 	: Firebase.ServerValue.TIMESTAMP
                    }),

                // Prepare user data
                    data = {
                        id 				: user_id,
                        cnv_id 			: cnv.key(),
                        ip 				: ylc.ip,
                        is_mobile 		: this.data.is_mobile,
                        current_page 	: ylc.current_page,
                        type 			: ylc.is_op ? 'operator' : 'visitor',
                        status 			: 'online' // Connection status
                    };

                // Merge with default user data
                for ( var d in this.opts.user_info ) { data[d] = this.opts.user_info[d]; }


                // Merge with login form data
                for ( var d in this.data.current_form ) { data[d] = this.data.current_form[d]; }

                // Name field is empty? Find a name for user
                if( !data.name ) {

                    // Use email localdomain part
                    if( data.email ) {
                        data.name = data.email.substring( 0, data.email.indexOf( '@' ) );

                        // Give user a random name
                    } else {
                        data.name = this.data.guest_prefix + this.random_id( 1000, 5000 );

                    }
                }

                // Update user data
                this.data.user = data;

                // Create user in Firebase
                this.data.ref_user.set( data, function( error ) {

                    if( !error ) {

                        // Show conversation
                        self.show_cnv();

                        // Callback: New user created
                        self.trigger( 'user_created', self.data.user );

                        // Callback: Logged in successfully
                        self.trigger( 'logged_in', self.data.user );

                        // Check this new user connection again
                        self.manage_conn();

                        // Now listen users activity
                        self.listen_users();

                    } else {

                        // Callback: Failed to create new user
                        self.trigger( 'user_failed', error );

                    }

                    if( callback )
                        callback();

                });


            } else {

                // Now listen users activity
                self.listen_users();

            }

        },
        /**
         * Show conversation in chat box
         */
        show_cnv : function( no_anim ) {

            var self = this;

            // Update mode
            this.data.mode = 'online';

            if( ylc.is_front_end ) {

                // Allow displaying?
                if( !this.allow_chatbox() )
                    return;

                // Update popup header
                this.objs.popup_header.find('.chat-title').html( this.strings.msg.chat_title );

                // Update popup wrapper
                this.objs.popup.parent().removeClass().addClass( 'chat-online' );

                // Render popup body
                this.objs.popup_body
                    .css( 'width', this.opts.styles.popup_width + 'px' )
                    .removeClass()
                    .addClass('chat-body chat-online')
                    .empty()
                    .html( this.get_template('online-user', {
                            reply_ph 	: this.strings.msg.reply_ph,
                            welc 		: this.strings.msg.welc_msg,
                            end_chat 	: this.strings.msg.end_chat
                        }
                    ) );

                this.objs.cnv = $( '#YLC_cnv' );

                // Autosize and focus reply box
                if( !no_anim ) {

                    $( '#YLC_cnv_reply' ).focus().autosize( { append: '' } ).trigger( 'autosize.resize' );

                } else {

                    setTimeout( function() {

                        $( '#YLC_cnv_reply' ).focus().autosize( { append: '' } ).trigger( 'autosize.resize' );

                    }, this.opts.styles.anim.anim_delay);

                }

                // Resize window to ensure chat box is responsive
                $(window).trigger( 'resize' );

                // Listen messages
                this.listen_msgs();

                // Logout (End chat)
                $( '#YLC_tool_end_chat' ).click( function() {

                    self.logout( '', true );

                    return;

                });

                this.manage_reply_box(); // Manage reply box

            }

        },
        /**
         * Get users
         */
        listen_users : function() {

            var self = this;

            this.data.last_changed_id = null;

            // Prepare user list
            if( this.opts.users_list_id ) {

                // Clean list if already exists
                $( this.opts.users_list_id + ' > ul' ).remove();

                // Add ul list
                $( this.opts.users_list_id ).append( '<ul></ul>' );

                // Select list
                this.data.user_list = $( this.opts.users_list_id + ' > ul' );

            }

            // Listen users once in the beginning of page load
            this.data.ref_users.once( 'value', function( snap ) {

                var users = snap.val(),
                    i = 0;

                if( users !== null ) {

                    var total_user = Object.keys( users ).length;

                    // Reset total ops
                    self.data.online_ops = {};

                    $.each( users, function( user_id, user ) {

                        // Increase index
                        i = i + 1;

                        if( user ) {

                            if( user.type === 'operator' ) {

                                // Check operator connection
                                if( user.status === 'online' ) {
                                    self.data.online_ops[user.id] = user;
                                } else
                                    delete self.data.online_ops[user.id];

                            }

                            // Add user item into the list
                            self.add_user_item( user );

                        }

                        if( i === total_user ) { // Last index in the while

                            // Change mode if necessary!
                            self.check_mode();

                            // Listen new users
                            self.listen_new_users();

                        }

                    });

                }

            });


        },
        /**
         * Listen new users
         */
        listen_new_users : function( callback ) {

            var self = this;

            // Add users
            this.data.ref_users.on( 'value', function( snap ) {

                // Clear list now
                $( '#YLC_users > ul' ).empty();

                var users = snap.val();

                $.each( users, function( user_id, user ) {

                    self.update_user( user );

                });

            });

        },
        /**
         * Update user info in Firebase
         */
        update_user : function( user, prev_id ) {

            if( user ) {

                // User is not ready for adding wait for all information added into Firebase
                if( !user.id ) {
                    return;
                }
            }

            if( user ) {

                if( user.cnv_id ) {

                    // Add user item into the list
                    this.add_user_item( user );

                    if( user.type === 'operator' ) { // Don't repeat same changes triggered more than once

                        // Increase total operator number
                        if( user.status === 'online' ) {
                            this.data.online_ops[user.id] = user;

                            // Decrease total number of operator
                        } else {
                            delete this.data.online_ops[user.id];
                        }

                    }

                    // Change mode if necessary!
                    this.check_mode();

                    // Callback: New user is online!
                    if( !prev_id )
                        this.trigger( 'user_online', user );

                    // Update user active page url
                    if( !ylc.is_front_end && this.data.active_user_id === user.id )
                        $( '#YLC_active_page' ).attr( 'href', user.current_page ).find('span').html( user.current_page );

                    // Remove user. It is trash! Because it doesn't have cnv_id
                } else {

                    // Save user data, and then delete from Firebase
                    this.clean_user_data( user.id );

                }
            }

            // Update last changed id
            this.data.last_changed_id = prev_id;

        },
        /**
         * Clean user data from Firebase
         */
        clean_user_data : function( user_id ) {

            var self = this,
                ref_user = this.data.ref_users.child( user_id );

            // Remove user from users list
            ref_user.once( 'value', function( snap ) {

                var user = snap.val();

                // Remove user reference
                ref_user.remove();

                // Clean user conversation
                if( user.cnv_id ) {
                    self.ref_cnv.child( user.cnv_id );
                }

                // Remove user messages
                self.data.ref_msgs.once( 'value', function( msg_snap ) {

                    var msgs = msg_snap.val();

                    if( msgs ) {
                        $.each( msgs, function( msg_id, msg ) {

                            if( msg.user_id === user_id ) {
                                self.data.ref_msgs.child( msg_id ).remove();
                            }

                        });
                    }

                });

            });

        },
        /**
         * Add user into the list
         */
        add_user_item : function( user ) {

            var self = this;

            // If no list or user_id, don't try to add user into the list also delete from the list
            if( !user.id || !this.data.user_list )
                return;

            var last_online = ( user.status === 'offline' ) ? ' - <span class="last-online" data-time="'+ user.last_online + '">' + this.timeago( user.last_online ) + '</span>' : '';

            // First remove user item from the list if exists
            $( '#YLC_chat_user_' + user.id ).remove();

            // Render user item
            this.data.user_list.append( this.get_template( 'user-item', {
                id 			: user.id,
                class 		: 'user-' + user.status + ' user-' + user.type,
                color 		: user.color || 'transparent',
                username	: user.name || user.email || 'N/A',
                avatar  	: '<img src="' + this.set_avatar( user.gravatar, user.type ) + '" />',
                cnv_id 		: user.cnv_id,
                meta 		: user.type + last_online
            } ) );

        },
        /**
         * Dekstop Notifications
         */
        notify : function( title, msg, callback, tag ) {

            // No notification support and don't show it on front end
            if( !Notification || ylc.is_front_end )
                return;

            // Check if browser supports notifications
            // And don't notify in front-end
            if ( ! ( "Notification" in window ) || ylc.is_front_end ) {
                return;

                // Display notification if possible!
            } else if ( Notification.permission === "granted" ) {

                // If it's okay let's create a notification
                var notification = new Notification( title, {
                    body: msg,
                    icon: ylc.plugin_url + '/images/ylc-ico.png',
                    tag: tag
                });

                if( callback )
                    notification.onclick = function() { callback(); };
                else
                    notification.close();

                // Hide notification after for a while
                setTimeout( function() {
                    notification.close();
                }, 4000 );

                // Otherwise, we need to ask the user for permission
                // Note, Chrome does not implement the permission static property
                // So we have to check for NOT 'denied' instead of 'default'
            } else if ( Notification.permission !== 'denied' ) {
                Notification.requestPermission(function (permission) {

                    // Whatever the user answers, we make sure we store the information
                    if (!('permission' in Notification)) {
                        Notification.permission = permission;
                    }

                    // If the user is okay, let's create a notification
                    if (permission === "granted") {

                        // If it's okay let's create a notification
                        var notification = new Notification( title, {
                            body: msg
                        });

                        if( callback )
                            notification.onclick = function() { callback(); };
                        else
                            notification.close();

                        // Hide notification after for a while
                        setTimeout( function() {
                            notification.close();
                        }, 4000 );

                    }

                });
            }
        },
        /**
         * Set avatar for user or operator
         */
        set_avatar: function( email_hash, is_op){

            var user_type       = ( is_op == 'operator' ) ? 'admin' : 'user',
                default_avatar  = this.data.assets_url + '/images/default-avatar-' + user_type + '.png';

            if ( ylc.is_premium ) {


            } else {

                    return default_avatar;

            }


        },
        /**
         * Time template
         */
        time : function( t, n ) {

            return this.strings.time[t] && this.strings.time[t].replace( /%d/i, Math.abs( Math.round( n ) ) );

        },
        /**
         * Time ago function
         */
        timeago : function( time ) {

            if ( !time )
                return '';

            var now = new Date(),
                seconds = ( ( now.getTime() - time ) * 0.001 ) >> 0,
                minutes = seconds / 60,
                hours = minutes / 60,
                days = hours / 24,
                years = days / 365;

            return (
            seconds < 45 && this.time( 'seconds', seconds ) ||
            seconds < 90 && this.time( 'minute', 1 ) ||
            minutes < 45 && this.time( 'minutes', minutes ) ||
            minutes < 90 && this.time( 'hour', 1 ) ||
            hours < 24 && this.time( 'hours', hours ) ||
            hours < 42 && this.time( 'day', 1 ) ||
            days < 30 && this.time( 'days', days ) ||
            days < 45 && this.time( 'month', 1 ) ||
            days < 365 && this.time( 'months', days / 30 ) ||
            years < 1.5 && this.time( 'year', 1 ) ||
            this.time( 'years', years )
            ) + ' ' + this.strings.time.suffix;

        },
        /**
         * Listen message
         */
        listen_msgs : function() {

            var self = this;

            // Clear previous listen
            this.data.ref_msgs.off();

            // Get current messages
            this.data.ref_msgs.once( 'value', function( snap ) {

                var msgs = snap.val(),
                    total_msgs = msgs ? Object.keys( msgs ).length : 0,
                    i = 1;


                // Load old messages after page refresh
                if( msgs ) {

                    $.each( msgs, function( msg_id, msg) {

                        // Update current conversation (front-end only)
                        if( ylc.is_front_end && self.data.user.cnv_id == msg.cnv_id) {

                            msg.id = msg_id; // Include msg id

                            self.add_msg( msg ); // Add message

                        }

                        // First load
                        msg.first_load = true;

                        // Callback: New message arrived at initial state
                        self.trigger( 'new_msg', msg );

                        // Last msg id
                        if( total_msgs == i ) {

                            self.listen_new_msgs( msg_id ); // Listen new messages

                        }

                        // Increase index
                        i = i + 1;
                    });

                } else {

                    self.listen_new_msgs();

                }

            });

        },
        /**
         * Listen new messages
         */
        listen_new_msgs : function( msg_id ) {

            var self = this,
                ref_msgs = !msg_id ? self.data.ref_msgs : self.data.ref_msgs.startAt( null, msg_id ),
                first = true;

            // Don't ignore first message when you check all messages
            if( !msg_id )
                first = false;

            ref_msgs.on( 'child_added', function( new_snap ) {

                var new_msg = new_snap.val(),
                    new_msg_id = new_snap.key();

                // Include message id
                new_msg.id = new_msg_id;

                // Update current conversation (front-end only)
                if( ylc.is_front_end && self.data.user.cnv_id == new_msg.cnv_id ) {

                    // Ignore first message
                    if( !first )
                        self.add_msg( new_msg );

                }

                // Show popup when new message arrived!
                if( !first )
                    self.show_popup();

                // Callback: New message arrived
                self.trigger( 'new_msg', new_msg );

                // Not first message anymore
                first = false;

            });

        },
        /**
         * Add message into conversation
         */
        add_msg : function( msg, last_user_id, last_msg_id ) {

            var now = new Date(),
                d = new Date( msg.time ), // Chat message date
                t = d.getHours() + ':' + ( d.getMinutes() < 10 ? '0' : '' ) + d.getMinutes(), // Chat message time
                msg_content = this.sanitize_msg( msg.msg ),

            // Set message time either time or short date like '21 May'
                msg_time = ( d.toDateString() == now.toDateString() ) ? t : d.getUTCDate() + ' ' + this.strings.months_short[ d.getUTCMonth() ] + ', ' + t;

            // Render chat line
            chat_line = this.get_template( 'chat-line', {
                msg_id 		: msg.id,
                time 		: msg_time,
                date 		: d.getUTCDate() + ' ' + this.strings.months[ d.getUTCMonth() ] + ' ' + d.getUTCFullYear() + ' ' + t,
                color 		: 'transparent',
                avatar  	: '<img src="' + this.set_avatar( msg.gravatar, msg.user_type ) + '" />',
                name 		: msg.name,
                msg 		: msg_content,
                class 		: ( msg.user_id == this.data.user.id ) ? ' chat-you' : ''
            });

            // Hide welcome message
            if( ylc.is_front_end )
                this.objs.cnv.find( '.chat-welc' ).hide();

            if( this.objs.cnv ) {

                this.objs.cnv.append( chat_line ).scrollTop(10000);

            }

        },
        /**
         * Add message into conversation
         */
        sanitize_msg : function( str ) {

            var msg, pattern_url, pattern_pseudo_url, pattern_email;

            //URLs starting with http://, https://, or ftp://
            pattern_url = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
            msg = str.replace(pattern_url, '<a href="$1" target="_blank">$1</a>');

            //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
            pattern_pseudo_url = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
            msg = msg.replace(pattern_pseudo_url, '$1<a href="http://$2" target="_blank">$2</a>');

            //Change email addresses to mailto:: links.
            pattern_email = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
            msg = msg.replace(pattern_email, '<a href="mailto:$1">$1</a>');

            return msg;


        },
        /**
         * Manage reply box
         */
        manage_reply_box : function( last_cnv_id ) {

            var self = this,
                writing = false,
                obj_reply = $( '#YLC_cnv_reply' ),

                /**
                 * Delay for a specified time
                 */
                fn_delay = ( function(){

                    var timer = 0;

                    return function(callback, ms){
                        clearTimeout (timer);
                        timer = setTimeout(callback, ms);
                    };

                } )();

            // First clean typing list in any case!
            this.data.ref_cnv.child( this.data.user.cnv_id +  '/typing' ).remove();

            // Manage reply box
            obj_reply.keydown( function(e) {

                // When clicks ENTER key (but not shift + ENTER )
                if ( e.keyCode === 13 && !e.shiftKey ) {

                    e.preventDefault();

                    var msg = $(this).val();

                    if( msg ) {

                        // Clean reply box
                        $(this).val('').trigger( 'autosize.resize' );

                        // Send message to Firebase
                        self.push_msg( msg );

                        // User isn't typing anymore
                        self.data.ref_cnv.child( self.data.user.cnv_id +  '/typing/' + self.data.user.id ).remove();

                    }

                    // Usual writing..
                } else {

                    // Check if current user (operator & visitor) is typing...
                    if( !writing ) {

                        // Don't listen some keys
                        switch( e.keyCode ) {
                            case 17: // ctrl
                            case 18: // alt
                            case 16: // shift
                            case 9: // tab
                            case 8: // backspace
                            case 224: // cmd (firefox)
                            case 17:  // cmd (opera)
                            case 91:  // cmd (safari/chrome) Left Apple
                            case 93:  // cmd (safari/chrome) Right Apple
                                return;
                        }

                        // Add user typing list in current conversation
                        self.data.ref_cnv.child( self.data.user.cnv_id + '/typing/' + self.data.user.id ).set( self.data.user.name );

                        // User is writing now
                        writing = true;

                    }

                    // Remove user from typing list after the user has stopped typing
                    // for a specified amount of time
                    fn_delay( function() {

                        // User isn't typing anymore
                        self.data.ref_cnv.child( self.data.user.cnv_id +  '/typing/' + self.data.user.id ).remove();

                        // User isn't writing anymore
                        writing = false;

                    }, 1300 );

                }



            });

            // Stop listen last conversation
            if( last_cnv_id ) {
                this.data.ref_cnv.child( last_cnv_id + '/typing' ).off();
            }

            // Check if a user is typing in current conversation...
            this.data.ref_cnv.child( this.data.user.cnv_id + '/typing' ).on( 'value', function( snap ) {

                var i = 0,
                    users = snap.val(),
                    total_users = ( users ) ? Object.keys( users ).length : 0;

                if( !users ) {
                    self.clean_ntf();

                    return;
                }

                $.each( users, function( user_id, user_name ) {

                    // Hmm.. someone else writing
                    if( user_id && user_id !== self.data.user.id ) {

                        // Show notification
                        self.display_ntf( self.strings.msg.writing.replace( /%s/i, user_name ), 'typing' );

                        return; // Don't check other writers
                    }

                    if( total_users === i ) { // Last index
                        self.clean_ntf();
                    }

                    i = i + 1; // Increase index

                });
            });

            if( ylc.is_front_end ) { // Additional functions for front-end chat box

                // Focus on reply box when user click around it
                this.objs.popup.find('.chat-cnv-reply').click( function() {
                    obj_reply.focus();
                });

            }

        },
        /**
         * Read current conversation messages and update cnv area (reload messages)
         * It is good to use when user open empty conversation box on user interface
         * and show up old messages
         */
        reload_cnv : function( cnv_id ) {

            var self = this;

            // Get current conversation messages
            this.data.ref_msgs.once( 'value', function( snap ) {

                var now = new Date(),
                    all_msgs = snap.val(),
                    total_msgs = all_msgs ? Object.keys( all_msgs ).length : 0,
                    total_user_msgs = 0,
                    i = 1;

                if( all_msgs ) {

                    $.each( all_msgs, function( msg_id, msg ) {

                        if( msg.cnv_id == cnv_id ) {

                            // This message from chat history
                            msg.old_msg = true;

                            // Callback: New message arrived
                            self.trigger( 'new_msg', msg );

                            // Increase total number of user messages
                            total_user_msgs = total_user_msgs + 1;

                        }

                        if( total_msgs == i ) { // Last index

                            // Callback: All conversation messages loaded
                            self.trigger( 'cnv_msgs_loaded', total_user_msgs );
                        }

                        // Increase index
                        i = i + 1;

                    });

                } else { // No message

                    // Callback: All conversation messages loaded
                    self.trigger( 'cnv_msgs_loaded', 0 );

                }


            });

        },
        /**
         * Create new message
         */
        push_msg : function( msg ) {

            // Push message to Firebase
            this.data.ref_msgs.push({
                user_id		: this.data.user.id,
                user_type	: this.data.user.type,
                cnv_id		: this.data.user.cnv_id,
                name 		: this.data.user.name || this.data.user.email,
                gravatar 	: this.data.user.gravatar,
                msg 		: msg,
                time 		: Firebase.ServerValue.TIMESTAMP
            });

        },
        /**
         * Get template to render
         */
        get_template: function( template, params ){

            var html;

            switch( template ){

                // Chat Popup
                case 'chat-popup':
                    html = ylc.templates.chat_popup;
                    break;

                //Connecting
                case 'connecting':
                    html = ylc.templates.connecting;
                    break;

                //Button
                case 'btn':
                    html = ylc.templates.btn;
                    break;

                //Offline
                case 'offline':
                    html = ylc.templates.offline;
                    break;

                //Login popup
                case 'login':
                    html = ylc.templates.login;
                    break;

                //Online popup - frontend
                case 'online-user':
                    html = ylc.templates.online_user;
                    break;

                //Online - backend
                case 'online-basic':
                    html = ylc.templates.online_basic
                    break;

                //User item - backend
                case 'user-item':
                    html = ylc.templates.user_item;
                    break;

                //Chat lines
                case 'chat-line':
                    html = ylc.templates.chat_line;
                    break;

                //User meta info - backend
                case 'chat-user-meta-info':
                    html = ylc.templates.chat_user_meta_info;
                    break;

                //User meta tools - backend
                case 'chat-user-meta-tools':
                    html = ylc.templates.chat_user_meta_tools;
                    break;

                //User meta page - backend
                case 'chat-user-meta-page':
                    html = ylc.templates.chat_user_meta_page;
                    break;

                default:
                    html = '';

            }


            return string_replace( html, params );

        },
        /**
         * Get a user data
         */
        get_user_data : function( user_id, callback ) {

            this.data.ref_users.child( user_id ).once( 'value', function( snap ) {

                var user = snap.val();

                // Just run callback
                callback( user );

            });
        },
        /**
         * Render button before showing up
         */
        render_btn : function() {

            var self = this;

            if( !ylc.is_front_end ) return;

            // Find secondary colors
            this.data.primary_fg = this.use_white( this.opts.styles.colors.primary ) ? '#ffffff' : '#444444';
            this.data.link_fg = this.use_white( this.opts.styles.colors.link ) ? '#ffffff' : '#444444';
            this.data.primary_hover = this.shade_color( this.opts.styles.colors.primary, 7 );
            this.data.link_hover = this.shade_color( this.opts.styles.colors.link, 7 );

            // Render button
            this.el.html( this.get_template( 'btn', {
                title 			: this.strings.msg.chat_title,
                color 			: this.data.primary_fg,
                bg_color 		: this.opts.styles.colors.primary,
                width 			: ( this.opts.styles.btn_width == 'auto' ) ? 'auto' : this.opts.styles.btn_width + 'px',
                radius          : this.opts.styles.border_radius
            } ) );

            this.objs.btn = $( '#YLC_chat_btn' );

            // Chat button hover
            this.objs.btn.hover(
                function() {
                    $(this).css('background-color', self.data.primary_hover );
                },
                function() {
                    $(this).css('background-color', self.opts.styles.colors.primary );
                }
            );

            // Manage button
            this.objs.btn.click( function() {

                var obj_btn = $(this),
                    obj_btn_title = $(this).find('.chat-title');

                // Update button title
                obj_btn_title.html( self.strings.msg.please_wait + '...' );

                // Hide button
                obj_btn.hide();

                // Show popup
                self.show_popup();

                self.auth( function() {

                    // Update title
                    obj_btn_title.html( self.strings.msg.chat_title );

                });


            });

            setTimeout( function() {
                self.show_btn();
            }, this.opts.styles.anim.anim_delay );

        },
        /**
         * Render popup
         */
        render_popup : function() {

            var self = this;

            if( !ylc.is_front_end ) return;

            this.el.append ( this.get_template( 'chat-popup', {
                title 		: this.strings.msg.connecting + '...',
                color 		: this.data.primary_fg,
                bg_color 	: this.opts.styles.colors.primary,
                body_class 	: 'chat-form',
                radius 		: this.opts.styles.border_radius,
                body 		: this.get_template( 'connecting', {
                    lead: this.strings.msg.connecting + '...'
                } )
            } ) );

            this.objs.popup = $( '#YLC_chat' );
            this.objs.popup_header = $( '#YLC_chat_header' );
            this.objs.popup_body = $( '#YLC_chat_body' );

            this.objs.popup_body.css( 'width', this.opts.styles.form_width + 'px' )

            // Send button hover
            $(document).on( 'hover', '#YLC_send_btn',
                function() {
                    $(this).css('background-color', self.data.link_hover );
                },
                function() {
                    $(this).css('background-color', self.opts.styles.colors.link );
                }
            );

            // Manage popup header
            this.objs.popup_header.click( function() {

                // Just be offline, don't logout completely
                self.be_offline();

                // Minimize popup
                self.minimize();

            });


            // Set height of chat popup
            $(window).resize(function() {

                var w = window,
                    d = document,
                    e = d.documentElement,
                    g = d.getElementsByTagName('body')[0],
                    x = w.innerWidth || e.clientWidth || g.clientWidth,
                    y = w.innerHeight|| e.clientHeight|| g.clientHeight,
                    pop_h_y = self.objs.popup_header.innerHeight(), // Popup header height
                    pop_b = parseInt( self.objs.popup.css( 'bottom' ), 10 ); // Popup bottom

                // Set max height
                var default_y = ( self.data.mode === 'online' ) ? 370 : 450,
                    max_y = ( default_y < y ) ? default_y : y - pop_h_y - pop_b;

                self.objs.popup_body.css( 'max-height', max_y );


            }).trigger('resize');
        },
        /**
         * Show popup
         */
        show_popup : function() {

            // Don't re-open popup
            if( this.data.popup_status == 'open' || !ylc.is_front_end ) return;

            var self = this;

            // Set cookie
            this.cookie( 'ylc_chat_widget_status', 'open' );

            // Display popup
            this.objs.popup.show();

            // Show popup with animation
            this.animate( this.objs.popup, this.opts.styles.anim.type );

            // Focus on first field in the form
            setTimeout( function() {

                switch( self.data.mode ) {

                    // Online mode
                    case 'online':

                        // Focus reply box
                        $( '#YLC_cnv_reply' ).focus();

                        // Scroll down conversation if necessary
                        self.objs.cnv.scrollTop(10000);


                        break;

                    // Offline or login mode
                    case 'offline':
                    case 'login':

                        // Focus first input in the form
                        $( '#YLC_popup_form .chat-line:first-child input').focus();

                        break;
                }


                // Update popup status
                self.data.popup_status = 'open';

            }, this.opts.styles.anim.anim_delay );

        },
        /**
         * Show button
         */
        show_btn : function( title ) {

            if( ylc.is_front_end ) {

                var self = this;

                // Allow displaying?
                if( !this.allow_chatbox() )
                    return;

                // Just show btn
                this.objs.btn.show();

                // Update title
                this.objs.btn.find( '.chat-title' ).html( title );

                // Show and animate
                this.animate( this.objs.btn, this.opts.styles.anim.type);

            }

        },
        /**
         * Minimize popup
         */
        minimize : function() {

            // Set cookie
            this.cookie( 'ylc_chat_widget_status', 'minimized' );

            // Update popup status
            this.data.popup_status = 'close';

            // Hide popup
            if( this.objs.popup )
                this.objs.popup.hide();


            this.objs.btn.show();

            // Display button
            this.animate( this.objs.btn, this.opts.styles.anim.type );

        },
        /**
         * Manage connections
         */
        manage_conn : function() {

            var self = this;

            if( !this.data.ref_user ) {
                return;
            }

            // Manage connections
            this.data.ref_conn.on( 'value', function( snap ) {

                // User is connected (or re-connected)!
                // and things happen here that should happen only if online (or on reconnect)
                if( snap.val() === true ) {

                    // Add this device to user's connections list
                    var conn = self.data.ref_user.child('connections').push( true );

                    // When user disconnect, remove this device
                    conn.onDisconnect().remove();

                    // Set online
                    self.data.ref_user.child( 'status' ).set( 'online' );

                    // Update user connection status when disconnect
                    self.data.ref_user.child( 'status' ).onDisconnect().set( 'offline' );

                    // Update last time user was seen online when disconnect
                    self.data.ref_user.child( 'last_online' ).onDisconnect().set( Firebase.ServerValue.TIMESTAMP );

                    // Remove user typing list on disconnect
                    self.data.ref_cnv.child( self.data.user.cnv_id +  '/typing/' + self.data.user.id ).onDisconnect().remove();

                }

            });

        },
        /**
         * Custom POST wrapper
         */
        post : function ( mode, data, callback ) {

            var self = this;

            $.post( ylc.ajax_url + '?action=ylc_ajax_callback&mode=' + mode, data, callback, 'json' )
                .fail(function (jqXHR) {

                    // Log error
                    console.log(mode, ': ', jqXHR);

                    return false;

                });

        },
        /**
         * Trigger
         */
        trigger : function( event, p ) {

            var ret = this.opts[event].call(this, p);

            if( ret === false )
                return false;

        },
        /**
         * Check if browser supports notifications
         */
        check_ntf : function() {

            // No notification support and don't show it on front end
            if( !( "Notification" in window ) || ylc.is_front_end ) {
                return;

                // Otherwise, we need to ask the user for permission
                // Note, Chrome does not implement the permission static property
                // So we have to check for NOT 'denied' instead of 'default'
            } else if ( Notification.permission !== 'denied' ) {
                Notification.requestPermission(function (permission) {

                    // Whatever the user answers, we make sure we store the information
                    if (!('permission' in Notification)) {
                        Notification.permission = permission;
                    }

                });
            }

        },
        /**
         * Display notification
         */
        display_ntf : function( ntf, type ) {

            var icon;

            switch ( type ){

                case 'success':
                    icon = '<i class="fa fa-check"></i> ';
                    break;
                case 'error':
                    icon = '<i class="fa fa-exclamation-triangle"></i> ';
                    break;
                case 'typing':
                    icon = '<i class="fa fa-pencil-square-o"></i> ';
                    break;
                default:
                    icon='';

            }

            $( '#YLC_popup_ntf' ).removeClass().addClass('chat-ntf chat-' + type).html( icon + ntf ).fadeIn(300);

        },
        /**
         * Clean notification
         */
        clean_ntf : function() {

            $( '#YLC_popup_ntf' ).html('').hide();

        },
        /**
         * Create or read cookie
         */
        cookie : function( name, value, days ) {

            // Create new cookie
            if( value || days === -1 ) {

                if (days) {
                    var date = new Date();
                    date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
                    var expires = '; expires=' + date.toGMTString();

                } else
                    var expires = '';


                document.cookie = name + '=' + value + expires + '; path=/';

                // Read cookie
            } else {

                var name_eq = name + "=";
                var ca = document.cookie.split(';');

                for(var i=0;i < ca.length;i++) {
                    var c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1,c.length);
                    if (c.indexOf(name_eq) === 0) return c.substring(name_eq.length,c.length);
                }

                return null;
            }

        },
        /**
         * Total number of online operators
         */
        total_online_ops : function() {

            if( this.data.online_ops )
                return Object.keys(this.data.online_ops).length;
            else
                return 0;

        },
        /**
         * Chatbox allowed to show up?
         */
        allow_chatbox : function() {

            return this.opts.render ? true : false;

        },
        /**
         * Animate
         */
        animate : function( obj, anim ) {

            $(window).trigger('resize'); // Resize window to ensure chat box is responsive

            obj.addClass( 'chat-anim chat-hinge chat-' + anim );

            // Remove CSS animation
            setTimeout( function() {
                obj.removeClass( 'chat-anim chat-hinge chat-' + anim );
            }, this.opts.styles.anim.anim_delay );
        },
        /**
         * Shade color original code: Pimp Trizkit (http://stackoverflow.com/a/13542669/272478)
         */
        shade_color : function( color, percent ) {
            var num = parseInt(color.slice(1),16),
                amt = Math.round(2.55 * percent),
                R = (num >> 16) + amt,
                B = (num >> 8 & 0x00FF) + amt,
                G = (num & 0x0000FF) + amt;

            return "#" + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (B<255?B<1?0:B:255)*0x100 + (G<255?G<1?0:G:255)).toString(16).slice(1);
        },
        /**
         * Check if foreground color should be white? original code: Alnitak (http://stackoverflow.com/a/12043228/272478)
         */
        use_white : function( c ) {
            var c = c.substring(1);      // strip #
            var rgb = parseInt(c, 16);   // convert rrggbb to decimal
            var r = (rgb >> 16) & 0xff;  // extract red
            var g = (rgb >>  8) & 0xff;  // extract green
            var b = (rgb >>  0) & 0xff;  // extract blue

            var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709

            if ( luma < 180 )
                return true; // use white

            return false; // use black
        },
        /**
         * Validate email
         */
        validate_email : function( email ) {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test( email );
        },
        /**
         * MD5 hash (http://www.webtoolkit.info/javascript-md5.html)
         */
        md5 : function(e) {
            function h(a,b){var c,d,e,f,g;e=a&2147483648;f=b&2147483648;c=a&1073741824;d=b&1073741824;g=(a&1073741823)+(b&1073741823);return c&d?g^2147483648^e^f:c|d?g&1073741824?g^3221225472^e^f:g^1073741824^e^f:g^e^f}function k(a,b,c,d,e,f,g){a=h(a,h(h(b&c|~b&d,e),g));return h(a<<f|a>>>32-f,b)}function l(a,b,c,d,e,f,g){a=h(a,h(h(b&d|c&~d,e),g));return h(a<<f|a>>>32-f,b)}function m(a,b,d,c,e,f,g){a=h(a,h(h(b^d^c,e),g));return h(a<<f|a>>>32-f,b)}function n(a,b,d,c,e,f,g){a=h(a,h(h(d^(b|~c),
                e),g));return h(a<<f|a>>>32-f,b)}function p(a){var b="",d="",c;for(c=0;3>=c;c++)d=a>>>8*c&255,d="0"+d.toString(16),b+=d.substr(d.length-2,2);return b}var f=[],q,r,s,t,a,b,c,d;e=function(a){a=a.replace(/\r\n/g,"\n");for(var b="",d=0;d<a.length;d++){var c=a.charCodeAt(d);128>c?b+=String.fromCharCode(c):(127<c&&2048>c?b+=String.fromCharCode(c>>6|192):(b+=String.fromCharCode(c>>12|224),b+=String.fromCharCode(c>>6&63|128)),b+=String.fromCharCode(c&63|128))}return b}(e);f=function(b){var a,c=b.length;a=
                c+8;for(var d=16*((a-a%64)/64+1),e=Array(d-1),f=0,g=0;g<c;)a=(g-g%4)/4,f=g%4*8,e[a]|=b.charCodeAt(g)<<f,g++;a=(g-g%4)/4;e[a]|=128<<g%4*8;e[d-2]=c<<3;e[d-1]=c>>>29;return e}(e);a=1732584193;b=4023233417;c=2562383102;d=271733878;for(e=0;e<f.length;e+=16)q=a,r=b,s=c,t=d,a=k(a,b,c,d,f[e+0],7,3614090360),d=k(d,a,b,c,f[e+1],12,3905402710),c=k(c,d,a,b,f[e+2],17,606105819),b=k(b,c,d,a,f[e+3],22,3250441966),a=k(a,b,c,d,f[e+4],7,4118548399),d=k(d,a,b,c,f[e+5],12,1200080426),c=k(c,d,a,b,f[e+6],17,2821735955),
                b=k(b,c,d,a,f[e+7],22,4249261313),a=k(a,b,c,d,f[e+8],7,1770035416),d=k(d,a,b,c,f[e+9],12,2336552879),c=k(c,d,a,b,f[e+10],17,4294925233),b=k(b,c,d,a,f[e+11],22,2304563134),a=k(a,b,c,d,f[e+12],7,1804603682),d=k(d,a,b,c,f[e+13],12,4254626195),c=k(c,d,a,b,f[e+14],17,2792965006),b=k(b,c,d,a,f[e+15],22,1236535329),a=l(a,b,c,d,f[e+1],5,4129170786),d=l(d,a,b,c,f[e+6],9,3225465664),c=l(c,d,a,b,f[e+11],14,643717713),b=l(b,c,d,a,f[e+0],20,3921069994),a=l(a,b,c,d,f[e+5],5,3593408605),d=l(d,a,b,c,f[e+10],9,38016083),
                c=l(c,d,a,b,f[e+15],14,3634488961),b=l(b,c,d,a,f[e+4],20,3889429448),a=l(a,b,c,d,f[e+9],5,568446438),d=l(d,a,b,c,f[e+14],9,3275163606),c=l(c,d,a,b,f[e+3],14,4107603335),b=l(b,c,d,a,f[e+8],20,1163531501),a=l(a,b,c,d,f[e+13],5,2850285829),d=l(d,a,b,c,f[e+2],9,4243563512),c=l(c,d,a,b,f[e+7],14,1735328473),b=l(b,c,d,a,f[e+12],20,2368359562),a=m(a,b,c,d,f[e+5],4,4294588738),d=m(d,a,b,c,f[e+8],11,2272392833),c=m(c,d,a,b,f[e+11],16,1839030562),b=m(b,c,d,a,f[e+14],23,4259657740),a=m(a,b,c,d,f[e+1],4,2763975236),
                d=m(d,a,b,c,f[e+4],11,1272893353),c=m(c,d,a,b,f[e+7],16,4139469664),b=m(b,c,d,a,f[e+10],23,3200236656),a=m(a,b,c,d,f[e+13],4,681279174),d=m(d,a,b,c,f[e+0],11,3936430074),c=m(c,d,a,b,f[e+3],16,3572445317),b=m(b,c,d,a,f[e+6],23,76029189),a=m(a,b,c,d,f[e+9],4,3654602809),d=m(d,a,b,c,f[e+12],11,3873151461),c=m(c,d,a,b,f[e+15],16,530742520),b=m(b,c,d,a,f[e+2],23,3299628645),a=n(a,b,c,d,f[e+0],6,4096336452),d=n(d,a,b,c,f[e+7],10,1126891415),c=n(c,d,a,b,f[e+14],15,2878612391),b=n(b,c,d,a,f[e+5],21,4237533241),
                a=n(a,b,c,d,f[e+12],6,1700485571),d=n(d,a,b,c,f[e+3],10,2399980690),c=n(c,d,a,b,f[e+10],15,4293915773),b=n(b,c,d,a,f[e+1],21,2240044497),a=n(a,b,c,d,f[e+8],6,1873313359),d=n(d,a,b,c,f[e+15],10,4264355552),c=n(c,d,a,b,f[e+6],15,2734768916),b=n(b,c,d,a,f[e+13],21,1309151649),a=n(a,b,c,d,f[e+4],6,4149444226),d=n(d,a,b,c,f[e+11],10,3174756917),c=n(c,d,a,b,f[e+2],15,718787259),b=n(b,c,d,a,f[e+9],21,3951481745),a=h(a,q),b=h(b,r),c=h(c,s),d=h(d,t);return(p(a)+p(b)+p(c)+p(d)).toLowerCase()
        },
        /**
         * Random ID
         */
        random_id : function( min, max ) {

            return Math.floor( Math.random() * ( max - min + 1 ) ) + min;

        }

	};

	/*
	 * Plugin wrapper, preventing against multiple instantiations and allowing any public function to be called via the jQuery plugin
	 */
	$.fn[YLC] = function ( arg ) {

		var args, instance;
		
		// only allow the plugin to be instantiated once
		if ( !( this.data( data_plugin ) instanceof Plugin ) ) {

			// if no instance, create one
			this.data( data_plugin, new Plugin( this ) );
		}
		
		instance = this.data( data_plugin );
		
		/*
		 * because this boilerplate support multiple elements using same Plugin instance, so element should set here
		 */
		instance.el = this;

		// Is the first parameter an object (arg), or was omitted, call Plugin.init( arg )
		if (typeof arg === 'undefined' || typeof arg === 'object') {
			
			if ( typeof instance['init'] === 'function' ) {
				instance.init( arg );
			}
			
		// checks that the requested public method exists
		} else if ( typeof arg === 'string' && typeof instance[arg] === 'function' ) {
		
			// copy arguments & remove function name
			args = Array.prototype.slice.call( arguments, 1 );
			
			// call the method
			return instance[arg].apply( instance, args );
			
		} else {
		
			$.error('Method ' + arg + ' does not exist on jQuery.' + YLC);
			
		}
	};

}(jQuery, window, document));