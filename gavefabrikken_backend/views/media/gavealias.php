<div style="float: right; padding-left: 10px;"><button type="button" onclick="openGaveAlias(_editShopID )">Gave alias</button></div>
<div style="display: none;" id="gavealiasDialog"></div>

<script>

	function gaveAliasUpdate(elm,keep)
	{
		var val = $.trimgf($(elm).val());
		$(elm).css('background','#ffffff');

		if(val == "")
		{
			$(elm).css('background','#fff0a0');		
		}

		else if(isNaN(parseInt(val)) || parseInt(val) < 0)
		{
			$(elm).css('background','#FFAAAA');
		}
	
		else
		{
			val = parseInt(val);
			$(elm).val(val)
			var same = 0;
			$('.aliasnumber').each(function() { if($(this).val() == val) same++; });
			if(same  > 1)
			{
				$(elm).css('background','#FFAAAA');	
			}
		}
		var lastcheckval = $(elm).data('lastcheckval');
		if(keep != true) $('.aliasnumber').each(function() { if($(this).val() == lastcheckval && $(this).get(0) != elm) gaveAliasUpdate($(this).get(0),true) });
		$(elm).data('lastcheckval',val);
	
	}

	function gaveAliasReorder()
	{
		var wrapper = $('#gavealiaslist');
		wrapper.find('.aliasrow').sort(function(a, b) {
			var av = parseInt($(a).find('.aliasnumber').val());
			var bv = parseInt($(b).find('.aliasnumber').val());
			if(isNaN(av)) av = 0;
			if(isNaN(bv)) bv = 0;
			if(av == 0) av = 1000;
			if(bv == 0) bv = 1000;
			return av-bv;
		}).appendTo(wrapper);
	}

	function gaveAliasSave()
	{
		var aliaslist = {};
    var modelalias = {};
    $('#gavealiasDialog').find('input.modelaliastext').each(function() { modelalias[$(this).attr('name')] = $(this).val(); });
		$('#gavealiasDialog').find('.aliasnumber').each(function() { aliaslist[$(this).attr('name')] = $(this).val(); });
		$.post('index.php?rt=gavealias/save',{shopid: _editShopID,gavealias: aliaslist, modelalias: modelalias},function(response) {
			if(response.hasOwnProperty('status') && response.status == 1) return $('#gavealiasDialog').dialog( "close" );
			else if(response.hasOwnProperty('error') && response.error != "") alert(response.error);
			else alert('Ukendt fejl, kunne ikke gemme!');
		},'json');
	}

    function openGaveAlias(shopid)
    {

        $('#gavealiasDialog').html('<div style="padding: 20px; text-align: center;">Henter gaver i shop..</div>');
        $.post('index.php?rt=gavealias/dialog',{shopid: shopid},function(response) {
            $('#gavealiasDialog').html(response);
            //$('#gavealiaslist').sortable({handle: '.sorthandle'}).disableSelection();
	    $('.aliasnumber').bind('change',function() { gaveAliasUpdate(this); }).bind('keyup',function() { gaveAliasUpdate(this); }).each(function() { gaveAliasUpdate(this); });
	    //gaveAliasReorder();
        });

        $('#gavealiasDialog').dialog({
            title: 'Gavealias',
            modal: true,
            width:700,
            height:500,
            buttons: {
 		'Sorter efter nr': function() {
                    gaveAliasReorder();
                },
                Annuller: function() {
                    $( this ).dialog( "close" );
                },
                Gem: function() {
                    gaveAliasSave();
                }
            }
        });
    }

</script>