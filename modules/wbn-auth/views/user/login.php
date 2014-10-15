<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="col-sm-4">
    <h3>Login Panel</h3>
    <?php echo Form::open() ?>
    <div id="field-username" class="form-group">
        <label for="username" class="control-label">Username</label>
        <input type="text" id="username" class="form-control" placeholder="Username" name="username" value="">
    </div>
    <div id="field-password" class="form-group">
        <label for="password" class="control-label">Password</label>
        <input type="password" id="password" class="form-control" placeholder="Password" name="password">
    </div>
    <input type="submit" class="btn btn-primary" value="Submit">
    <?php if (isset($status) && $status == 'failed'): ?>
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            username or password is invalid.
        </div>
    <?php endif; ?>

    <?php if (isset($errors)): ?>
        <br><br>
        <div class="alert alert-danger">
            <?php foreach ($errors as $message): ?>
                <strong><?php echo $message ?></strong><br>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <?php echo Form::close(); ?>
</div>
<div class="clearfix"></div>