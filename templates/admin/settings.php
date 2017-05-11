<?php if ( isset($message) && $message != "" ) : ?>
<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
<h2>Settings</h2>
<table class="wp-list-table widefat fixed" cellspacing="0">
	<thead>
        <tr>
            <th scope="col" class="manage-column" style="">Misc. Settings</th>
        </tr>
	</thead>
	<tbody id="the-list">
        <tr>
            <td>
            	<form method="post" name="frm_qe" id="frm_qe" class="frm_qe" action="?page=qe_settings" enctype="multipart/form-data">
                <table width="100%">
                    <tr>
                    	<td width="180">Canvas API URL</td>
                        <td>
                            <input type="text" name="api_url" id="api_url" value="<?php echo $options['api_url'];?>" />
                        </td>
                    </tr>
                    <tr>
                    	<td>Canvas API Authorization Token</td>
                        <td>
                            <input type="text" name="auth_token" id="auth_token" value="<?php echo $options['auth_token'];?>" />
                        </td>
                    </tr>                    
                    <tr>
                        <td></td>
                        <td><input type="submit" name="btnsave" id="btnsave" value="Update" class="button button-primary">
                        </td>
                    </tr>
                </table>
                </form>
            </td>
        </tr>
     </tbody>
</table>
</div>