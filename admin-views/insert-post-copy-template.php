<table class="form-table">
    <tr>
        <th>
            Post Type
        </th>
        <td>
            <select name="alchemyst-forms-notification[{id}][post_type]">
                <?php foreach (get_post_types(array(), 'objects') as $post_type => $obj) : ?>
                    <option value="<?=$post_type?>"><?=$obj->labels->singular_name?></option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            Post Status
        </th>
        <td>
            <select name="alchemyst-forms-notification[{id}][post_status]">
                <?php foreach (get_post_stati() as $post_status) : ?>
                    <option value="<?=$post_status?>"><?=$post_status?></option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            Post Title
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[{id}][post_title]">
        </td>
    </tr>
    <tr>
        <th>
            Post Content
        </th>
        <td>
            <textarea name="alchemyst-forms-notification[{id}][post_content]"></textarea>
        </td>
    </tr>
</table>
