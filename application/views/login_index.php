<?php $this->load->view("template_header_login"); ?>


<!-- START displaying server-side validation errors -->
<?php
$fullErrorText = validation_errors();
if (isset($credentials_error)) {
    $fullErrorText = $fullErrorText . $credentials_error;
}

if (strlen($fullErrorText) > 0) {
    ?>
    <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <div class="errors"> 
            <?php
            echo $fullErrorText;
            ?>
        </div>
    </div>
    <?php
}
?>
<!-- END displaying server-side validation errors -->

<div class="left-login"> 


    <?php
//first check if the user has an img mapping in the db
    echo "<a href=http://www.cs.fiu.edu>";
    $src = '/img/fiulogo3.png';
    echo img(array(
        'id' => 'img-user-profile',
        'src' => $src . '?x=' . time(),
        'class' => 'fiu-logo'
    ));

    echo "</a>";
    $src = '/img/logo.png';
    echo img(array(
        'id' => 'img-user-profile',
        'src' => $src . '?x=' . time(),
        'class' => 'logo'
    ));
    $src = '/img/spw.png';

    echo img(array(
        'id' => 'img-user-profile',
        'src' => $src . '?x=' . time(),
        'class' => 'spw'
    ));
    ?>    

</div>

<div class="spw_title">
    <h3><span>Welcome to the Senior Project Website</span></h3>

</div>

<div class="indent-home">
    <div >
        <!-- top text<-->
        <!-- Students Access<-->
        <div id="phanter-login" class="form-signin stud-login">
            <h3>Students Access</h3>
            <div class="login-service">
                <a href="<?php echo base_url('/login/regularLogin') ?>">
                    <?php echo img(array('src' => '/img/fiu_login.png', 'alt' => 'Panther Mail Login')) ?>
                </a>
            </div> 

            <div class="login-service">
                <a href="<?php echo base_url('guest/') ?>">
                    <?php echo img(array('src' => '/img/guest.png', 'alt' => 'Guest Acces')) ?>
                </a>
            </div> 

        </div>

        <!-- Faculty Access<-->
        <div class="prof-login">
            <?php echo form_open('admin', array('class' => 'form-signin')) ?>

            <h3>Faculty Access</h3>

            <?php
//echo form_input('email_address',set_value('email_address'),'id="email_address"');
            echo form_input(array(
                'id' => 'email_address',
                'name' => 'email_address',
                'type' => 'email',
                'class' => 'input-block-level input-large',
                'placeholder' => 'Email address',
                'required' => '',
                'title' => 'Email address'
            ));

//<input type="password" class="input-block-level" placeholder="Password">
//echo form_password('password','','id="password"');
            echo form_password(array(
                'id' => 'password',
                'name' => 'password',
                'class' => 'input-block-level input-large',
                'placeholder' => 'Password',
                'required' => '',
                'title' => 'Password'
            ));

//<button class="btn btn-large btn-primary" type="submit">Sign in</button>
//echo form_submit('accounts','Log In');
            echo form_submit(array(
                'id' => 'accounts',
                'name' => 'accounts',
                'type' => 'Submit',
                'class' => 'btn btn-large btn-primary',
                'value' => 'Log In'
            ));
            ?>
            <?php echo form_close() ?>
        </div>

        <!-- bottom text<-->
        <div class="loging-footer">
            <h5>
                <span style="letter-spacing:0em;">This site is for FIU Computer Science students enrolled in the Senior Project course. If you are a student please log in using your FIU email. If you are a Mentor, please login using Faculty Access form.</span>
            </h5>
        </div>



    </div>

</div>



<?php $this->load->view("template_footer"); ?>
