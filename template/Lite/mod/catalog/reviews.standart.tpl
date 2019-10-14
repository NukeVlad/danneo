<div class="comnent-body clearfix">
    <div class="comnent-author">
        {author} <span class="comment-time">{date:1:1}</span>
    </div>
    <div class="comnent-info rate clearfix">
		<div class="comnent-avatar">{user}{guest}</div>
		<div class="comnent-text">{message}</div>
	</div>
    <div class="comnent-foter">
		<span>{state} &nbsp;&#8260;&nbsp; {region}</span> {valrate}: <img src="{site_url}/template/{site_temp}/images/rating/{rate}.gif" alt="{langrate}" />
    </div>
</div>
<!--buffer:guest:0--><img src="{site_url}/up/avatar/blank/guest.png" alt="{guest}"><!--buffer-->
<!--buffer:user:0--><a rel="nofollow" href="{link}"><img src="{site_url}{avatar}" alt="{languser}: {nameuser}<br />{register}: {date:%F j, Y, g:i%}"></a><!--buffer-->
<!--buffer:author:0--><a rel="nofollow" href="{link}" title="{title}">{name}</a><!--buffer-->