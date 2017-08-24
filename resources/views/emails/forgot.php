<!DOCTYPE html>
<html>

<body style="padding: 0;margin: 0 auto;width: 800px;font-family: Arial;">

<div style="background: #f0f0f0;">
    <div class="container">

        <div style="padding: 20px;line-height: 1.50;margin: 0 0 20px 0;">

            <p>
                Dear <?php echo $name ?>,<br/>
                Kindly click on this button to reset your password.
            </p>

            <a style="display: block;width: 200px;margin: 0 auto;line-height: 40px;background: #23566a;border-radius: 3px;color: #ffffff;text-align: center;text-decoration: none;" href="<?php echo env('SITE_PATH').'reset_password/'.$token; ?>">Reset Password</a>
        </div>

    </div>
</div>

</body>
