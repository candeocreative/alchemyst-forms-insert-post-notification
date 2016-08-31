<p>
    <strong>Note:</strong> All submitted form data well be saved with this post as well under the meta key <code>_alchemyst_forms-request</code>
</p>
<table class="form-table">
    <tr>
        <th>
            Post Type
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][post_type]" list="alchemyst-forms-notification-list-type-<?=$notification->ID?>" value="<?=$notification->post_type?>">
            <datalist id="alchemyst-forms-notification-list-type-<?=$notification->ID?>">
                <?php foreach (get_post_types() as $post_type) : ?>
                    <option value="<?=$post_type?>">
                <?php endforeach; ?>
            </datalist>
        </td>
    </tr>
    <tr>
        <th>
            Post Status
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][post_status]" list="alchemyst-forms-notification-list-status-<?=$notification->ID?>" value="<?=$notification->post_status?>">
            <datalist id="alchemyst-forms-notification-list-status-<?=$notification->ID?>">
                <?php foreach (get_post_stati() as $post_status) : ?>
                    <option value="<?=$post_status?>">
                <?php endforeach; ?>
            </datalist>
        </td>
    </tr>
    <tr>
        <th>
            Post Title
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][post_title]" value="<?=htmlentities($notification->post_title)?>">
        </td>
    </tr>
    <tr>
        <th>
            Post Content
        </th>
        <td>
            <textarea name="alchemyst-forms-notification[<?=$notification->ID?>][post_content]"><?=htmlentities($notification->post_content)?></textarea>
        </td>
    </tr>
</table>
