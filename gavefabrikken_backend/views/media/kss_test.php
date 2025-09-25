<!DOCTYPE html>
<html>
    139617
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<head>
  <title>GF - SYSTEM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <script src="views/lib/jquery.min.js"></script>
  <script src="views/lib/jquery-ui/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="views/lib/jquery-ui/style.css">
  <link rel="stylesheet" href="views/lib/jquery-ui/jquery-ui.css">
<!--  <script src="views/js/kmain_kss.js"></script>-->
  <script src="views/js/kss_temp/kss.js"></script>
  <script src="views/js/main.js"></script>
  <script src="views/lib/handlebars.js"></script>
</head>

<script>
    function onResult(data) {
        $("#response").empty();
        $("#response").html('<pre>'+JSON.stringify(data,null,2)+'</pre>')
    }
     function showRequest(data) {
        $("#response").empty();
        $("#request").empty();
        $("#request").html('<pre>'+JSON.stringify(data,null,2)+'</pre>')
    }
</script>

<body onload="onDocumentLoaded();">
<div style="height:50px">
<div style="height:50px;position:absolute" id="prod">
</div>
</div>

<?php echo __SITE_PATH ?>

<img height="200" width="200" src="../gavefabrikken_backend/thirdparty/phpqrcode/index.php?value=12345" />

<ul>
   <a href="http://40.113.94.34/developer/#" target="_blank" >Code Generator</a>

       <fieldset>
    <legend>Log</legend>
      <li><a href="#" onclick="systemLog.readAll()">Vis Sidste 100</a></li>
      <li><a href="#" onclick="systemLog.readError()">Vis fejl</a></li>
      <li><a href="#" onclick="systemLog.replay();">Replay</a> <input type="input" id ="replay"/>
      </li>
      <li><a href="#" onclick="systemLog.deleteAll();">Slet alle</a></li>
      <li><a href="#" onclick="systemLog.deleteErrors();">Slet fejllogs</a></li>
      <li><a href="#" onclick="systemLog.enableFullTrace()">Enable Full Trace</a></li>
      <li><a href="#" onclick="systemLog.disableFullTrace()">Disable Full Trace</a></li>

    </fieldset>

   <fieldset>
    <legend>Mail</legend>
       <li>
		  <table>
		    <tr><td><label><input type="checkbox" checked id="autoupdate" value="first_checkbox">Autoupdate</label></td></tr>
		    <tr><td>Last run</td><td id="last_run"></td></tr>
		    <tr><td>In Queue</td><td id="mails_queue"></td></tr>
			<tr><td>Sent</td><td id="mails_sent"></td></tr>
			<tr><td>Error</td><td id="mails_error"></td></tr>
		  </table>
      </li>
      <li><a href="http://cron-job.org" target="_blank">CRON-Job.Org</a></li>
      <li><a href="#" onclick="mail.createMailQueue()">Opret mail kø</a></li>
      <li><a href="#" onclick="mail.parseQueue()">Afvikle kø</a></li>
      <li><a href="#" onclick="mail.resendOrderMail()">Gensend ordremail</a></li>

    </fieldset>



    <fieldset>
    <legend>Gaver</legend>
      <li><a href="#" onclick="present.delete();">Slet gave</a></li>
      <li><a href="#" onclick="present.activate();">Aktiver gave</a></li>
      <li><a href="#" onclick="present.deactivate();">Deaktiver gave</a></li>
      <li><a href="#" onclick="present.read();">Vis gave</a></li>
      <li><a href="#" onclick="present.readAll()">Vis alle</a></li>
      <li><a href="#" onclick="present.readTop10()">Vis Top 10</a></li>
      <li><a href="#" onclick="present.readVariants()">Vis varianter</a></li>
      <li><a href="#" onclick="present.searchPresents()">Søg varer</a></li>
      <li><a href="#" onclick="present.searchVariants()">Søg varianter</a></li>

    </fieldset>

    <fieldset>
    <legend>Login</legend>
      <li><a href="#" onclick="login.loginSystemUser()">Login Systembruger</a></li>
      <li><a href="#" onclick="login.loginShopUser()">Login Shop bruger</a></li>
      <li><a href="#" onclick="login.testBackendToken()">Test Backend token</a></li>
      <li><a href="#" onclick="login.testShopToken()">Test Shop token</a></li>
      <li><a href="#" onclick="login.testCustomerToken()">Test kunde token</a></li>
    </fieldset>

     <fieldset>
    <legend>Shops</legend>
     <li><a href="#" onclick="shop.read()">Vis Shop</a></li>
	 <li><a href="#" onclick="shop.readSimple()">Vis Shop Simple</a></li>

     <li><a href="#" onclick="shop.readCompanyShops()">Vis alle Valg Shops</a></li>
     <li><a href="#" onclick="shop.readGiftcertificateShops()">Vis alle Gavekort Shops</a></li>
     <li><a href="#" onclick="shop.getShopCompanies()">Vis alle shop-virksomheder</a></li>
	 <li><a href="#" onclick="shop.getShopUsers();">Hent Brugere</a></li>
     <li><a href="#" onclick="shop.getUsersBatch();">Hent Brugere Batch</a></li>
     <li><a href="#" onclick="shop.searchUsers();">Søg brugere</a></li>

	 <li><a href="#" onclick="shop.sendMailsToUsersWithNoOrders();">Send Mail til der ikke har valgt</a></li>
 	 <li><a href="#" onclick="shop.sendMailsToUsersHowHasNotPickedUpPresents();">Send Mail til Brugere der ikke har afhentet deres gave</a></li>
 	 <li><a href="#" onclick="shop.getToUsersHowHasNotPickedUpPresents();">Vis Brugere der ikke har afhentet deres gave</a></li>


     <li><a href="#" onclick="shop.getShopPresents()">Vis Shop Gaver</a></li>
     <li><a href="#" onclick="shop.addPresent();">Tilføj gave til shop</a></li>
     <li><a href="#" onclick="shop.removePresent();">Fjern gave fra shop</a></li>
     <li><a href="#" onclick="shop.createCompanyShop();">Opret valgshop</a></li>
     <li><a href="#" onclick="shop.delete();">Delete</a></li>
	 <li><a href="#" onclick="shop.getPresentProperties()">Vis shop_gave properties</a></li>
	 <li><a href="#" onclick="shop.setPresentProperties()">Sæt shop_gave properties</a></li>



     <li>attributes</li>
     <li><a href="#" onclick="shop.addAttribute();">Tilføj Attribute</a></li>
     <li><a href="#" onclick="shop.updateAttribute();">Opdater Attribute</a></li>
     <li><a href="#" onclick="shop.removeAttribute();">Fjern Attribute</a></li>
	 <li><a href="#" onclick="shop.getShopAttributes();">Vis Attributes</a></li>
     <li>Users</li>

     <li><a href="#" onclick="shop.createShopUser();">Opret Shop User(temp)</a></li>
     <li><a href="#" onclick="shop.updateShopUser();">Opdater Shop User(temp)</a></li>
     <li><a href="#" onclick="shop.removeShopUser();">Fjern Shop User</a></li>
   	 <li><a href="#" onclick="shop.getUsersWithNoOrders()">Vis bruger uden gavevalg</a></li>

    </fieldset>

     <fieldset>
     <legend>Shop Test</legend>
       <li><a href="#" onclick="shop.testLogins()">Login Test</a></li>
	   <li><a href="#" onclick="shop.testGiftSelections()">Gift Selection Test</a></li>
    </fieldset>

    <fieldset>
    <legend>Company</legend>
         <li><a href="#" onclick="company.createGiftCertificateCompany();">Opret gavekort virksomhed</a></li>
         <li><a href="#" onclick="company.readGiftCertificateCompany();">Vis gavekort virksomhed</a></li>
         <li><a href="#" onclick="company.searchGiftCertificateCompany();">Søg gavekort virksomhed</a></li>
         <li><a href="#" onclick="company.getUsers();">Vis virksomhed brugere</a></li>
         <li><a href="#" onclick="company.addGiftCertificates();">Tilføj gavekort virksomhed</a></li>
         <li><a href="#" onclick="company.getCompanyReservations();">Vis gave reservationer</a></li>
         <li><a href="#" onclick="company.getCompanyOrders();">Vis gavekortbestillinger</a></li>
         <li><a href="#" onclick="company.setCompanyOrderPrinted();">Opdater bestilling udskrevet</a></li>
        <li><a href="#" onclick="company.setCompanyOrderShipped();">Opdater bestilling sendt</a></li>
         <li><a href="#" onclick="company.getCompanyImports();">Vis importede bestillinger</a></li>
    </fieldset>

    <fieldset>
    <legend>Ordre</legend>
     <li><a href="#" onclick="order.create()">Opret ordre</a></li>
     <li><a href="#" onclick="systemLog.removeOrderData()">Slet Ordredata</a></li>
     <li><a href="#" onclick="order.changePresent()">Ændre gave</a></li>
     <li><a href="#" onclick="order.getReceipt()">Hent Kvittering</a></li>
     <li><a href="#" onclick="order.resendReceipt()">Gensend Kvittering</a></li>

    </fieldset>


	 <fieldset>
    <legend>Gavekort</legend>
      <li><a href="#" onclick="giftcertificate.createBatch();">Opret</a></li>
	  <li><a href="http://bitworks.dk/gf/index.php?rt=report/gavekortReport" onclick="">Download List(prod)</a></li>
      <li><a href="#" onclick="giftcertificate.addToShop();">Tilføj kort til shop</a></li>
      <li><a href="#" onclick="giftcertificate.removeFromShop();">Fjern kort fra shop</a></li>
      <li><a href="#" onclick="giftcertificate.findBatch();">Find gavekort batch</a></li>
    </fieldset>

    <fieldset>
      <legend>Ekstern</legend>
       <li><a href="#" onclick="external.newGiftCertificateOrder();">Opret gavekortordre</a></li>

    </fieldset>

	 <fieldset>
    <legend>Report</legend>
      <li><a href="http://bitworks.dk/gf/index.php?rt=report/genericReport" onclick="">Report1(prod)</a></li>
      <li><a href="http://bitworks.dk/gf/index.php?rt=report/userReport&shop_id=47" onclick="">User Report(prod)</a></li>
    </fieldset>

    <!--
    <li><a href="#" onclick="present_media.show();">Vis Present_Media</a></li>
    -->

    <fieldset>
    <legend>Log</legend>
      <li><a href="#" onclick="systemLog.readAll()">Vis Sidste 100</a></li>
      <li><a href="#" onclick="systemLog.readError()">Vis fejl</a></li>
      <li><a href="#" onclick="systemLog.replay();">Replay</a></li>
      <li><a href="#" onclick="systemLog.deleteAll();">Slet alle</a></li>
      <li><a href="#" onclick="systemLog.deleteErrors();">Slet fejllogs</a></li>
      <li><a href="#" onclick="systemLog.enableFullTrace()">Enable Full Trace</a></li>
      <li><a href="#" onclick="systemLog.disableFullTrace()">Disable Full Trace</a></li>
      <li><a href="#" onclick="systemLog.getLoginActivity()">Logins Sidste Minut</a></li>

    </fieldset>
    <fieldset>
    <legend>Test</legend>
          <li><a href="#" onclick="test.test();">test</a></li>
    </fieldset>

    <fieldset>
    <legend>Generic Test</legend>
          <li>Controller:<input type="text" id="controller"></li>
          <li>Action:<input type="text" id="action"></li>
          <li>data:<textarea rows="4" cols="50" id="data">{}</textarea></li>
          <li><a href="#" onclick="test.generictest();">Invoke</a></li>
    </fieldset>

</ul>
         <div id="main"></div>
         <div id="error"></div>

         <fieldset>
           <legend>Request</legend>
           <div id="request"></div>
         </fieldset>

        <fieldset>
          <legend>Response</legend>
          <div id="response"></div>
        </fieldset>
        <a href="#" onclick="$('#response').empty();$('#request').empty();  ">Clear</a>
    
</body >


</html>
