<?php

$plugin_opts = ylc_get_plugin_options();

?>

<div id="YLC"></div>
<script type="text/javascript">

    (function ($) {

        $(document).ready(function () {

            $( '#YLC' ).ylc(
                {
                    <?php ylc_print_plugin_options( $plugin_opts ); ?>
                },
                {
                    <?php apply_filters( 'ylc_js_premium', '') ?>
                }
            );

        });

    } (window.jQuery || window.Zepto));

</script>