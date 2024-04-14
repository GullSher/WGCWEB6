

<div class="tab-pane" id="tabs-4" role="tabpanel">
    <div class="ftp_Settings_section3 blur">

        <div class="ftp_settings_item">
            <label for="ftp_host3">FTP host</label>
            <input type="text" id="ftp_host3" name="ftp_host" placeholder="Host" value="<?php echo $host; ?>">
        </div>
        <div class="ftp_settings_item">
            <label for="ftp_user3">FTP user</label>
            <input type="text" id="ftp_user3" name="ftp_user" placeholder="User" value="<?php echo $user; ?>">
        </div>
        <div class="ftp_settings_item">
            <label for="ftp_pass3">FTP password</label>
            <input type="password" id="ftp_pass3" name="ftp_pass" placeholder="Password" value="<?php echo $pass; ?>">
        </div>
        <div class="ftp_settings_item">
            <label for="ftp_path3">FTP upload path (deafult)</label>
            <input type="text" id="ftp_path3" name="ftp_path" placeholder="Upload path" value="<?php echo $path; ?>">
        </div>


        <div class="ftp_status_section"><span class="ftp_status_text">FTP connection status: </span><span class="ftp_status">
                                            <?php
                                            if ( $ftp_status == 'connected' ): ?>
                                                <span class="ftp_connected">Connected</span>
                                                <span class="ftp_not_connected" style="display: none;">Not Connected</span>

                                            <?php else: ?>
                                                <span class="ftp_connected" style="display: none;">Connected</span>
                                                <span class="ftp_not_connected">Not Connected</span>
                                            <?php endif ?>
                                        </span>

        </div>
        <div class="ftp_authentication_failed" style="<?php if ( $ftp_status == 'connected' || $ftp_status == '' ): ?>
            display: none;
        <?php endif ?>">
            <span style="font-weight: bold;">Error: </span>Host name or username or password is wrong. Please check and try again!
        </div>

        <button id="test_ftp_connection" class="btn btn--radius-2 btn--green" style="margin-top: 15px;">Test Connection</button>
    </div>

    <div class="eh_premium">
        This option available for premium version only
        <div class="go_pro2">
            <select id="licenses">
                <option value="1" selected="selected">Single Site License</option>
                <option value="3">3-Site License</option>


                <option value="unlimited">Unlimited Site License</option>
            </select>
            <button id="purchase" class="location">Upgrade Now</button>

            <script>
                var $ = jQuery;
            </script>
            <script src="https://checkout.freemius.com/checkout.min.js"></script>
            <script>
                var handler = FS.Checkout.configure({
                    plugin_id:  '8170',
                    plan_id:    '13516',
                    public_key: 'pk_6dc0a25d3672a637db3b8c45379ab',
                    image:      'https://s3-us-west-2.amazonaws.com/freemius/plugins/8170/icons/6ea0f8afeeaf904d8258b7ebb40e4cd3.png',
                    //trial: true,
                });

                $('#purchase.location').on('click', function (e) {
                    handler.open({
                        name     : 'Export wp page to static html pro',
                        licenses : $('#licenses').val(),
                        // You can consume the response for after purchase logic.
                        success  : function (response) {
                            // alert(response.user.email);
                        },

                        purchaseCompleted : function(r){
                            console.log(r);
                        }
                    });
                    e.preventDefault();
                });
            </script>
        </div>
    </div>
</div>