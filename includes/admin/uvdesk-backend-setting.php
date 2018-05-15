
		<style>
			.wrap {
				background-color: #fff;
				padding: 20px;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
			}

			.wrap label {
				display: block;
				margin: 30px 0px 10px 0px;
				font-weight: bold;
			}
			.wrap input[type="text"] {
				padding: 8px;
				width: 50%;
			}
		</style>

		<div class="wrap">

			<h1>UVDesk Settings</h1>

			<form method="post" action="options.php">

				<?php settings_fields('webkul-uvdesk-settings-group'); ?>

				<div class="inner-wrap">

	                <label for="uvdesk_access_token">Access Token</label>

	                <input id="uvdesk_access_token" type="text" name="uvdesk_access_token"  value="<?php echo get_option('uvdesk_access_token');?>"/>

	                <p class="description">You need to create access token from <a href="https://www.uvdesk.com">Uvdesk Site</a></p>

	                <br />


	                <h1><strong>Company Domain</strong></h1>

	                <input id="uvdesk_company_domain" type="text" name="uvdesk_company_domain"  value="<?php echo get_option('uvdesk_company_domain');?>"/>

	                <p class="description">This field is the domain of your organization.</p>

	                <br />

	                <h1><strong>Setup Recaptcha</strong></h1>

	                <div class="form-group">

		                <label for="uvdesk_client_key" > Client Key OR Site Key</label>

		                <input type="text" id="uvdesk_client_key" name="uvdesk_client_key" value="<?php echo get_option('uvdesk_client_key');?>" placeholder="eg :-12***********12">
	                	<p class="description">You can create recaptcha keys from <a href="https://www.google.com/recaptcha/intro/index.html">Google RECAPTCHA</a> Site</p>

		                <label for="uvdesk_secret_key" > Secret Key</label>

		                <input type="text" id= "uvdesk_secret_key" name="uvdesk_secret_key" value="<?php echo get_option('uvdesk_secret_key');?>" placeholder="eg :-12***********34">

	            	</div>

	            </div>

                <?php

			        submit_button();

			    ?>

			</form>

		</div>
