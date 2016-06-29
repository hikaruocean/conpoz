<script>
$(function () {

    var validator = new Validator();
    $(document).on('click', '#goSubmit', function (e) {
        e.preventDefault();
        $('.err_msg').html('');
        var errAry = validator.valid($("#form1"));
        if (errAry.length != 0) {
            $.each(errAry, function (k, v) {
                v.object.next().html(v.errMsg);
            });
            return;
        }
        $("#form1").submit();
    });
});

</script>
<div>
    <form action="/index/validate" method="post" name="form1" id="form1">
        id : <input type="text" name="id" <?php echo $validateRule->rta('id'); ?>/><span class="err_msg"></span><br />
        password : <input type="password" name="password" <?php echo $validateRule->rta('password'); ?>/><span class="err_msg"></span><br />
        retype-password : <input type="password" name="retype_password" <?php echo $validateRule->rta('retype_password'); ?>/><span class="err_msg"></span><br />
        birthday : <input type="text" name="birthday" <?php echo $validateRule->rta('birthday'); ?>/><span class="err_msg"></span><br />
        tel : <input type="text" name="tel" <?php echo $validateRule->rta('tel'); ?>/><span class="err_msg"></span><br />
        ip : <input type="text" name="ip" <?php echo $validateRule->rta('ip'); ?>/><span class="err_msg"></span><br />
        number : <input type="text" name="number" <?php echo $validateRule->rta('number'); ?>/><span class="err_msg"></span><br />
        numeric : <input type="text" name="numeric" <?php echo $validateRule->rta('numeric'); ?>/><span class="err_msg"></span><br />
        datetime : <input type="text" name="datetime" <?php echo $validateRule->rta('datetime'); ?>/><span class="err_msg"></span><br />

        <input type="button" value="goSend" name="goSubmit" id="goSubmit"/>
    </form>
</div>