/*
* 2007-2015 Deindo
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Deindo Ideas SL <contacto@deindo.es>
*  @copyright  2007-2020 Deindo Ideas SL
*  @license    http://www.deindo.es
*/


$(document).ready(function() {
						
    $('.prod_autocomplete')
            .autocomplete(pr_customs_route, {
                    minChars: 3,
                    max:20,
                    scroll:false,
                    cacheLength:0,
                    matchContains: false,
					mustMatch: false,
                    dataType: "json",
					formatItem: function(data, i, max, value, term) {
						return value;
					},
					parse: function(data) {
						var mytab = new Array();
						for (var i = 0; i < data.length; i++)
							mytab[mytab.length] = { data: data[i], value: data[i].id + ' - ' + data[i].name };
						return mytab;
					},
            }).result(function(event, data, formatted) {
			     if (data.id == null)
			     {
			        $('#id_' + event.target.id).val(0);
			        $('#' + event.target.id).val('');
			        return false;
			     }
			     else
			     {
			        $('#id_' + event.target.id).val(data.id);
			        $('#' + event.target.id).val(data.name);
			        return true;
			     }   
			});

	$('.customs_autocomplete')
            .autocomplete(customs_route, {
                    minChars: 3,
                    max:20,
                    scroll:false,
                    cacheLength:0,
                    matchContains: false,
					mustMatch: false,
                    dataType: "json",
					formatItem: function(data, i, max, value, term) {
						return value;
					},
					parse: function(data) {
						var mytab = new Array();
						for (var i = 0; i < data.length; i++)
							mytab[mytab.length] = { data: data[i], value: data[i].id + ' - ' + data[i].name };
						return mytab;
					},
            }).result(function(event, data, formatted) {
			     if (data.id == null)
			     {
			        $('#id_' + event.target.id).val(0);
			        $('#' + event.target.id).val('');
			        return false;
			     }
			     else
			     {
			        $('#id_' + event.target.id).val(data.id);
			        $('#' + event.target.id).val(data.name);
			        return true;
			     }   
			});
});                                                                                     
