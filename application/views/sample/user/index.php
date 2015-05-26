<h1>Users</h1>

<?php if(isset($arr_users)): ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>S.No.</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Created on</th>
            <th>Last updated on</th>
            <th>Action</th>
        </tr>
    </thead>
    <?php foreach($arr_users as $user): /* @var $user Model_Sample_User */ ?>
    <tbody>
        <tr>
            <td><?php echo $user->id; ?></td>
            <td><?php echo $user->full_name; ?></td>
            <td><?php echo $user->username; ?></td>
            <td><?php echo $user->created_on; ?></td>
            <td><?php echo $user->last_updated_on; ?></td>
            <td>
                <a href="/sample/user/edit/<?php echo $user->id; ?>">Edit</a>&nbsp;
                <a href="/sample/user/delete/<?php echo $user->id; ?>">Delete</a>
            </td>
        </tr>
    </tbody>
    <?php endforeach; ?>
</table>
<?php endif;