(function ($) {
	
	$(document).ready(function() {

		var last_cnv_id = null,
			last_user_id = null,
			last_msg_id = null,
			ls_ntf = $('#YLC_notify'),
			conn_btn = $('#YLC_connect'),
			checked_user_ids = [],
            fn_show_welcome_popup = function() {

            $('#YLC_popup_cnv').addClass('chat-welcome').html( '' );
            $('#YLC_sidebar_right').html('').hide();

		};

		/**
		 * Use plugin
		 */
		$('body').ylc({
			app_id 			: ylc.app_id,
			user_info 		: {
				id 		: ylc.user_id,
				name 	: ylc.user_name,
				email 	: ylc.user_email
			},
			render 			: false,
			company_avatar 	: ylc.company_avatar,

			// Before load
			before_load: function() {

				var self = this;

				// Update login form data
				this.data.current_form = {
					name 		: ylc.user_name,
					email 		: ylc.user_email,
					gravatar 	: ylc.user_email_hash
				};

				conn_btn.click(function(e) {
					e.preventDefault();
					
					// Display "Connecting" message
					ls_ntf.show().html( self.strings.msg.connecting + '...' );

					// If already connected, don't try it again
					if( !$(this).data( 'logged' ) )
						self.login( true );
					
					else if( $(this).data( 'status', 'online' ) ) {
						self.be_offline();
					} 
					
				});
			},

			// Current user is offline
			offline : function() {

				// Play sound
				//this.play_sound( 'disconnected' );

				// Display "Connected" message
				ls_ntf.html( "You're offline!" );

				// Show offline button
				conn_btn.html( '<i class="fa fa-check-circle" style="color:#e54045;"></i> ' + this.strings.msg.offline_btn )
                    .data('logged', 0)
                    .data('status', 'offline')
                    .removeClass('button-disabled');
			},

			// Authentication error
			auth_error : function( error ) {

				// Enable button
				conn_btn.removeClass('button-disabled');
				
				// Display error
				ls_ntf.hide().html( error.message ).fadeIn(200);

			},

			// Authenticated in Firebase, not logged in yet
			auth : function() {
				
				// Display "Connected" message
				ls_ntf.html( this.strings.msg.you_offline );

				// Show connect button
				conn_btn.html( this.strings.msg.connect )
                    .data('logged', 0)
                    .removeClass('button-disabled');

			},

			// Logged in successfully
			logged_in: function( user ) {

				// Play sound
				//this.play_sound( 'connected' );

				// Listen messages
				this.listen_msgs();

				// Enable button
				conn_btn.removeClass('button-disabled');
				
				// Hide notification on users list
				ls_ntf.hide().empty();

				// Update online button
				conn_btn.html( '<i class="fa fa-check-circle" style="color:#acc327;"></i> ' + this.strings.msg.online_btn )
                    .data('logged', 1)
                    .data('status', 'online')
                    .removeClass('button-disabled');
			},

			// Logged out
			logged_out: function( logout_msg ) {

				// Play sound
				//this.play_sound( 'disconnected' );
				
				// Display "Connected" message
				ls_ntf.html( "Logged out!" );

				// Show connect button
				conn_btn.html( this.strings.msg.connect )
                    .data('logged', 0)
                    .data('status', 'offline')
                    .removeClass('button-disabled');

				// Show welcome popup
				fn_show_welcome_popup();

			},

			// New user is online now
			user_online : function( user ) {

				if( $.inArray( user.id, checked_user_ids ) && user.id != this.data.user.id ) { // If operator didn't logged out itself

					// Play sound
					//this.play_sound( 'online' );

					// Notify user
					this.notify( this.strings.msg.new_user_online, user.name + ' (' + user.type + ')', null, 'user_online' );

					// Add user in checked users
					checked_user_ids.push( user.id );

				}


			},

			// A user appeared offline
			user_offline : function( user ) {

				// Play sound if operator didn't logged out itself
				//if( user.id != this.data.user.id )
					//this.play_sound( 'offline' );

			},

			// New message sent to any online user
			new_msg : function( msg ) {

				var obj_user = $( '#YLC_chat_user_' + msg.user_id ),
					obj_count = obj_user.find( '.chat-count' ),
					total_msg = parseInt( obj_user.data( 'count' ) );

				// Update total msg if it isn't old message and not user's own message
				if( !msg.old_msg && !msg.first_load && msg.user_id != this.data.user.id ) {

					total_msg = total_msg + 1;

					// Update user item in the list
					obj_user.addClass( 'new-msg' ).data( 'count', total_msg );

					// Update count
					obj_count.html( '(' + total_msg + ')' );

					// Play sound
					//this.play_sound( 'new-msg' );

					// Notify user
					this.notify( this.strings.msg.new_msg, msg.name + ': ' + msg.msg, null, 'new_msg' );

				}

				// Update current conversation area
				if( this.data.user.cnv_id == msg.cnv_id ) {

					// Remove notification
					$( '#YLC_load_msg' ).remove();
					
					// Render message
					this.add_msg( msg, last_user_id, last_msg_id );

					// Update last user id
					last_user_id = msg.user_id;

					// Update last message id
					if( last_user_id != msg.user_id || !last_msg_id )
						last_msg_id = msg.msg_id;

				}

			},

			// Conversation messages loaded
			cnv_msgs_loaded : function( total_msgs ) {
				
				if( !total_msgs )
					$( '#YLC_load_msg' ).html( this.strings.msg.no_msg + '.' ); // No messages found
				else
					$( '#YLC_load_msg' ).empty(); // Hide load msg in anyway

			},

			// After load
			after_load : function() {

				var self = this,
					working = false;

				// Autosize reply input when focussed
				$(document).on('focus', '#YLC_cnv_reply', function() {
					$(this).autosize({
						append: ''
					});
				});

				/**
				 * When click user on the users list
				 */
				$( document ).on( 'click', '#YLC_users li', function() {

					var obj_user = $(this);

					// Get user data
					self.get_user_data( $(this).data( 'id' ), function( user ) {

						// Deactivate last active user
						if( self.data.active_user_id )
							$( '#YLC_chat_user_' + self.data.active_user_id ).removeClass( 'chat-active' );

						// Clean highlights and count
						obj_user.addClass( 'chat-active' ).removeClass( 'new-msg' ).data( 'count', 0 ).find( '.chat-count' ).empty();

						// Show user popup


						$('#YLC_popup_cnv').removeClass('chat-welcome')
                            .html( self.get_template( 'online-basic', {
                                reply_ph 	: self.strings.msg.reply_ph,
                                load_msg 	: self.strings.msg.please_wait + '...',
                                avatar  	: '<img src="' + self.set_avatar( self.data.user.gravatar, self.data.user.type ) + '" />'
                            } ) );

						// Autosize reply input
						$('#YLC_cnv_reply').focus();


                        var user_html = '';

						// Prepare user info sidebar
						if( obj_user.data( 'id' ) !== self.data.user.id ) { // User opened itself conversation

							// Don't show meta tools
                            user_html = self.get_template( 'chat-user-meta-tools', {
                                save_chat_btn   : ylc.is_premium ? self.premium.get_template_premium( 'chat-user-meta-tools-premium', {
                                    obj_user_data   : obj_user.data( 'cnv-id' ),
                                    button_text     : self.strings.msg.save_chat
                                }) : '',
                                obj_user_data   : obj_user.data( 'cnv-id' ),
                                button_text     : self.strings.msg.end_chat
                            } );

						}

                        user_html = user_html + self.get_template( 'chat-user-meta-info', {
                            ip_title    : self.strings.msg.user_ip,
                            ip_address  : user.ip ,
                            info_title  : self.strings.msg.user_email,
                            email       : user.email,
                            page_info   : user.current_page ? self.get_template( 'chat-user-meta-page', {
                                page_title      : self.strings.msg.user_page,
                                current_page    : user.current_page
                            }) : ''
                        } );

						// Update user info
						$('#YLC_sidebar_right').html( user_html ).show();


						// Set conversation area
						self.objs.cnv = $( '#YLC_cnv' );

						// Update current conversation id
						// Now operator's current conversation is the same with the user operator talks
						self.data.user.cnv_id = obj_user.data('cnv-id');

						// Set last active user
						self.data.active_user_id = obj_user.data('id');


						// Reload conversation
						self.reload_cnv( obj_user.data('cnv-id') );

						// Manage reply box
						self.manage_reply_box( last_cnv_id );

						// Update last conversation id
						last_cnv_id = obj_user.data('cnv-id');


						// Resize window
						$(window).trigger('resize');
						
					});

				});

				/**
				 * End chat
				 */
				$(document).on( 'click', '#YLC_save, #YLC_end_chat', function(e) {

					var btn = $(this),
						ntf = $('#YLC_save_ntf'),
						delete_from_app = $(this).attr('id') === 'YLC_end_chat' ? true : false;

					// Don't allow more than one clicks
					if( working ) {
						ntf.html( self.strings.msg.please_wait + '...' ); // Waiting for the next request
						return;
					}

					working = true;

					// Disable button
					$(this).addClass( 'button-disabled' );

					// Display saving notification on user metabar
					ntf.html( self.strings.msg.saving + '...' );

					// Save user data
					self.save_user_data( $(this).data( 'cnv-id' ), delete_from_app, function( r ) {

						working = false; // Not working anymore

						// Reactivate button
						btn.removeClass( 'button-disabled' );

						// Update notification
						if( r.error )
							ntf.html( r.error );
						else
							ntf.html( r.msg ); // Successfully saved!

						// Clean notification after a while
						setTimeout( function() {

							ntf.fadeOut(2000);

						}, 1000 );

						// Show welcome popup if user session ended
						if( delete_from_app ) {

							setTimeout( function() {

								fn_show_welcome_popup();

							}, 3000 );

						}



					});

				});

				// Remove active user highlight when visitor already mouseover on the conversation
				$('#YLC_popup_cnv').mouseover( function() {

					// Remove highlight and reset count
					$('#YLC_chat_user_' + self.data.active_user_id ).removeClass( 'new-msg' )
													  .data( 'count', 0 ) // Reset count
													  .find( '.chat-count' ).empty();

					
				});


				// Update last online times every minute
				setInterval( function() {

					$( '.chat-last-online' ).each( function( i ) {

						$(this).html( self.timeago( $(this).data( 'time' ) ) );

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

                                        new_wait = new_wait + 1; // Increase index
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

				window.onbeforeunload = function (e) {
					var e = e || window.event;

					//IE & Firefox
					if (e) {
						e.returnValue = self.strings.msg.ntf_close_console;
					}

					// For Safari
					return self.strings.msg.ntf_close_console;
				};

			}

		});

		// Align layout
		$(window).resize(function() {

			var win_h = $(window).height();
			var console_h = win_h - 74;

            $('#YLC_console').height( console_h );

            $('#YLC_sidebar_left').height( console_h );

            $('#YLC_popup_cnv').height( console_h );

            $('#YLC_sidebar_right').height( console_h );

            $('#YLC_users').height( console_h - 110 );

			$('#YLC_cnv').height( console_h - 177 );

		}).trigger('resize');

	});

} (window.jQuery || window.Zepto));