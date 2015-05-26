<?php if(isset($success)): ?>
    <div class="alert alert-success" role="alert">
        User added successfully
    </div>
<?php endif; ?>

<?php if(isset($_errors['flash'])): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $_errors['flash']; ?>
    </div>
<?php endif; ?>

<h1>Add User</h1>

<form method="post" action="/sample/user/create" class="col-md-6 col-md-offset-3 text-left">
    
    <!--
    <div class="form-group">
        <label for="full-name">Full Name</label>
        <input id="full-name" type="text" name="full_name" class="form-control">
    </div>
    <div class="form-group">
        <label for="username">Username</label>
        <input id="username" type="text" name="username" class="form-control" />
    </div>
    -->
    <?php
    $error = isset($_errors['full_name']) ? $_errors['full_name'] : null;
    Helper_Bootstrap::text_input_tag(array(
        'label' => 'Full Name',
        'id' => 'full-name',
        'name' => 'full_name',
        'placeholder' => 'First Name Last Name',
        'error' => $error
    ));
    
    $error = isset($_errors['username']) ? $_errors['username'] : null;
    Helper_Bootstrap::text_input_tag(array(
        'label' => 'Username',
        'id' => 'username',
        'name' => 'username',
        'placeholder' => 'Enter username',
        'error' => $error
    ));
    ?>
    
    <div class="form-group">
        <label for="passwd">Password</label>
        <input id="passwd" type="password" name="passwd" class="form-control" />
    </div>
    
    <button type="submit" class="btn btn-default">Submit</button>
    &nbsp;
    <a href="/sample/user"><span class="glyphicon glyphicon-chevron-left"></span>Return to list</a>

</form>
<div class="clearfix"></div>