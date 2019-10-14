<!--if:ajax:yes-->
<script>
$(function(){
    $('#poll-form').submit(function() {
        $('#pollsendbox').show();
        $("#pollerrorbox").html('');
        var value = $(this).serialize();
        $.ajax({
            cache:false,
            type:'POST',
            url:'{site_url}/index.php?dn=poll&re=add&ajax=1',
            data:value,
            error: function(data) { $('#poll-form').submit(); },
            success: function(data) {
                $("#pollsendbox").hide();
                if (data.match(/^<!--pollok ([0-9]+)-->/)) {
                    $("#pollajaxbox").html(data);
                } else {
                    $("#pollerrorbox").html(data);
                }
            }
      })
      return false;
    });
});
</script>
<!--if-->
<div id="pollajaxbox">
    <form action="{post_url}" method="post" id="poll-form">
    <div id="pollerrorbox"></div>
    <div id="pollsendbox" style="display: none" class="notice">
        <img src="{site_url}/template/{site_temp}/images/icon/progress.gif" alt="{all_sends}" /> <span>{all_sends}... </span>
    </div>
    <input name="id" value="{id}" type="hidden" />
    <input name="re" value="add" type="hidden" />
	<article role="article" class="open">
		<header>
			<h2>{subtitle}</h2>
		</header>
        <div class="text-content">{desc}</div>
        <div class="poll-conttext">
            <table class="poll">
                {percent}
            </table>
        </div>
        <div class="ac">
            <button type="submit" id="poll-button" class="sub poll">{button}</button>
        </div>
	</article>
    </form>
</div>
<!--buffer:percent:0-->
    <tr>
        <td>{radio}</td>
        <td>{val_name}</td>
        <td>{val_voc}</td>
        <td>
            <div class="pollbarout" style="border-color: {val_color};">
                <div class="pollbar" style="background-color: {val_color}; width: {val_line};"></div>
				<span>{val_voc} {val_perc} %</span>
            </div>
        </td>
        <td>{val_perc} %</td>
    </tr>
<!--buffer-->
<div class="clear-line"></div>
{comment}
{ajaxbox}
<div id="errorbox"></div>
{comform}