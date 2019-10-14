<table width="50%" align="center" class="setup_container" cellpadding="1" cellspacing="1">
<tbody>
  <tr>
    <td class="setup_container_title">
      &nbsp;{title}
    </td>
  </tr>
  <tr>
    <td class="setup_container_center" align="center" valign="top">
      <fieldset class="fieldset">
      <legend>{notice}</legend>
        {text}
      </fieldset>
    </td>
  </tr>
    <td colspan="2" align="center" class="setup_container_copy">
      <form action="update.php" method="post">
        <input name="step" value="{step}" type="hidden" />
        <input class="buttons" value="{btext}" type="submit" />
      </form>
    </td>
  </tr>
  <tr>
    <td class="setup_container_copy" align="right">
      {in} <span class="setup_title">{inproduct}</span><br />{copy}
    </td>
  </tr>
</tbody>
</table>


