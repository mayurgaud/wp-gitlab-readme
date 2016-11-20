<div class="wrap">
<h2>Gitlab Readme</h2>

<form method="post" action="options.php">
    <?php settings_fields('gitlab-readme'); ?>
    <?php do_settings_sections('gitlab-readme'); ?>
    <table class="form-table">
        <tr valign="top">
			<th scope="row">Gitlab URL</th>
			<td>
				<input type="text" name="gitlab_url" value="<?php echo get_option('gitlab_url', 'URL'); ?>" />
				<p class="description">URL to your gitlab server</p>
			</td>
        </tr>
    </table>
    <p></p>
    <table class="form-table">
        <tr valign="top">
			<th scope="row">Gitlab API Key</th>
			<td>
				<input type="text" name="gitlab_api_key" value="<?php echo get_option('gitlab_api_key', 'API Key'); ?>" />
				<p class="description">API key to your gitlab installation</p>
			</td>
        </tr>
    </table>
	<p></p>	
	<?php submit_button(); ?>
</form>
</div>