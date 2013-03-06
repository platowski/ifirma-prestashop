<fieldset style="margin-top: 20px;">
	<legend><img src="../img/admin/invoice.gif"> Fakturowanie - ifirma.pl</legend>
	{if $can_make_invoice}
	<a href="../modules/ifirma/main/api_request.php?cart_order_id={$id_order}&h={$hash}" title="Wystaw fakturę">Wystaw fakturę &raquo;</a>
	{else}
		<a href="../modules/ifirma/main/download.php?cart_order_id={$id_order}&h={$hash}" title="Pobierz fakturę"><img src="../img/admin/pdf.gif"/> Pobierz fakturę &raquo;</a>
	{/if}
</fieldset>