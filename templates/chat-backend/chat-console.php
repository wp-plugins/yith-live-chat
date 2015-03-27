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
        <div class="sidebar-footer">
            <span><?php echo date( 'Y' ); ?> YITH Live Chat</span>
        </div>
    </div>
    <div id="YLC_popup_cnv" class="chat-content chat-welcome"></div>
    <div id="YLC_sidebar_right" class="console-sidebar-right">
    </div>
</div>
<script>

    (function ($) {
        $(document).ready(function() {

            /* Set console window sizes */
            $(window).resize(function() {

                var wpbody_h = $('#wpbody').height();

            }).trigger('resize');

        });
    } (window.jQuery || window.Zepto));

</script>