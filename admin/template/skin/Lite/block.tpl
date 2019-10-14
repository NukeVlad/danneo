<td id="aside" class="aside" style="display:{display}">
	<div class="menupanelin"><a href="{adm_path}/index.php?dn=content&amp;ops={hash}">{menu_content}</a></div>
	<div class="menu-content">
		{content}
	</div>
	<div class="menupanelin"><a href="{adm_path}/index.php?dn=system&amp;ops={hash}">{menu_system}</a></div>
	<div class="menu-system">
		{system}
	</div>
	<div class="menupanelin">{menu_server}</div>
	<div class="menu-server">
		{server-link}
		<div id="support" class="server menupanel{open-support}">
			<a href="{adm_path}/index.php?dn=support&amp;ops={hash}"><!--if:icon:yes--><img src="{adm_path}/template/skin/{adm_temp}/images/menu/support.png" alt="" /><!--if-->{support}</a>
		</div>
		<div id="logout" class="server menupanel">
			<a href="{adm_path}/index.php?dn=logout&amp;ops={hash}"><!--if:icon:yes--><img src="{adm_path}/template/skin/{adm_temp}/images/menu/logout.png" alt="" /><!--if-->{logout}</a>
		</div>
	</div>
</td>