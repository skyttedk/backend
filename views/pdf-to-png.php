<?php
//$_GET["shopId"];

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale = 1.0, maximum-scale = 1.0, user-scalable=no">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="lib/pdf/pdf.js"></script>
<script src="lib/pdf/pdf.worker.js"></script>
<style type="text/css">

#upload-button {
	width: 150px;
	display: block;
	margin: 20px auto;
}

#file-to-upload {
	display: none;
}

#pdf-main-container {
	width: 1000px;
	margin: 20px auto;
}

#pdf-loader {
	display: none;
	text-align: center;
	color: #999999;
	font-size: 13px;
	line-height: 100px;
	height: 100px;
}

#pdf-contents {
	display: none;
}

#pdf-meta {
	overflow: hidden;
    position: absolute;
    top:50px;
    left: 50px;

}

#pdf-buttons {
	float: left;
}

#page-count-container {
	float: right;
}

#pdf-current-page {
	display: inline;
}

#pdf-total-pages {
	display: inline;
}

#pdf-canvas {
	border: 1px solid rgba(0,0,0,0.2);
	box-sizing: border-box;
    position: absolute;
    top:-3000px;
}

#page-loader {
	height: 100px;
	line-height: 100px;
	text-align: center;
	display: none;
	color: #999999;
	font-size: 13px;
}

#download-image {
	width: 150px;
	display: block;
	margin: 20px auto 0 auto;
	font-size: 13px;
	text-align: center;
}

</style>
</head>

<body>

<button id="upload-button">Select PDF</button>


<div id="sceenshot" style="width: 100px;">

</div>

<input type="file" id="file-to-upload" accept="application/pdf" />

<div id="pdf-main-container">
	<div id="pdf-loader">Loading document ...</div>
	<div id="pdf-contents">
		<div id="pdf-meta">

			<div id="page-count-container">Side  <div id="pdf-current-page"></div> ud af  <div id="pdf-total-pages"> behandles</div></div>
		</div>
		<canvas id="pdf-canvas" width="1800"></canvas>
		<div id="page-loader">Loading page ...</div>

	</div>
</div>

<script>
var shopId = <?php echo $_GET["shopId"]; ?>

var __PDF_DOC,
	__CURRENT_PAGE,
	__TOTAL_PAGES,
	__PAGE_RENDERING_IN_PROGRESS = 0,
	__CANVAS = $('#pdf-canvas').get(0),
	__CANVAS_CTX = __CANVAS.getContext('2d');

function showPDF(pdf_url) {

	$("#pdf-loader").show();

	PDFJS.getDocument({ url: pdf_url }).then(function(pdf_doc) {
		__PDF_DOC = pdf_doc;
		__TOTAL_PAGES = __PDF_DOC.numPages;

		// Hide the pdf loader and show pdf container in HTML
		$("#pdf-loader").hide();
		$("#pdf-contents").show();
		$("#pdf-total-pages").text(__TOTAL_PAGES);

		// Show the first page
		showPage(1);
	}).catch(function(error) {
		// If error re-show the upload button
		$("#pdf-loader").hide();
		$("#upload-button").show();

		alert(error.message);
	});;
}

function showPage(page_no) {
	__PAGE_RENDERING_IN_PROGRESS = 1;
	__CURRENT_PAGE = page_no;

	// Disable Prev & Next buttons while page is being loaded
	$("#pdf-next, #pdf-prev").attr('disabled', 'disabled');

	// While page is being rendered hide the canvas and show a loading message
	$("#pdf-canvas").hide();
	$("#page-loader").show();
	$("#download-image").hide();

	// Update current page in HTML
	$("#pdf-current-page").text(page_no);
	
	// Fetch the page
	__PDF_DOC.getPage(page_no).then(function(page) {
		// As the canvas is of a fixed width we need to set the scale of the viewport accordingly
		var scale_required = __CANVAS.width / page.getViewport(1).width;

		// Get viewport of the page at required scale
		var viewport = page.getViewport(scale_required);

		// Set canvas height
		__CANVAS.height = viewport.height;

		var renderContext = {
			canvasContext: __CANVAS_CTX,
			viewport: viewport
		};

		// Render the page contents in the canvas
		page.render(renderContext).then(function() {
			__PAGE_RENDERING_IN_PROGRESS = 0;

			// Re-enable Prev & Next buttons
			$("#pdf-next, #pdf-prev").removeAttr('disabled');

			// Show the canvas and hide the page loader
			$("#pdf-canvas").show();
			$("#page-loader").hide();
			$("#download-image").show();
		});
	});
}

// Upon click this should should trigger click on the #file-to-upload file input element
// This is better than showing the not-good-looking file input element
$("#upload-button").on('click', function() {

    $("#file-to-upload").trigger('click');
});

// When user chooses a PDF file
$("#file-to-upload").on('change', function() {
	// Validate whether PDF
    if(['application/pdf'].indexOf($("#file-to-upload").get(0).files[0].type) == -1) {
        alert('Error : Not a PDF');
        return;
    }

	$("#upload-button").hide();

	// Send the object url of the pdf
	showPDF(URL.createObjectURL($("#file-to-upload").get(0).files[0]));
         setTimeout(function () {
       grab()
              }, 500);


});

// Previous page of the PDF

function grab(){
    if(__CURRENT_PAGE <= __TOTAL_PAGES){
              showPage(__CURRENT_PAGE);
              setTimeout(function () {
                  makeSceenshot().then(function(d){


                  grab()
                  })
              }, 500);
    } else {
      window.history.go(-1)
    }
}
function makeSceenshot()
{
        return new Promise(function (resolve, reject) {
            console.log(__CURRENT_PAGE+"shot")
        var node = document.getElementById('pdf-canvas');
            domtoimage.toPng(node)
                .then(function (dataUrl) {
                    var img = new Image();
                    img.src = dataUrl;

                     saveImage(node.toDataURL(),__CURRENT_PAGE).then(function(){
                        resolve();
                         ++__CURRENT_PAGE
                     })

                })
        })
}
function saveImage(data,page){
    var data = data;
    var page = page;
  return new Promise(function (resolve, reject) {
  postData = {
             shopId:shopId,
             order:page,
             data:data

          }

          $.ajax(
            {
            url: '../index.php?rt=ptimage/save',
            type: 'POST',
            dataType: 'json',
            data:postData
            }
          ).done(function(res) {
                  resolve();
            }
          )

           })
}



!function(a){"use strict";function b(a,b){return b=b||{},Promise.resolve(a).then(function(a){return e(a,b.filter)}).then(f).then(g).then(function(a){return b.bgcolor&&(a.style.backgroundColor=b.bgcolor),a}).then(function(b){return h(b,a.scrollWidth,a.scrollHeight)})}function c(a,b){return i(a,b||{}).then(function(a){return a.toDataURL()})}function d(a,b){return i(a,b||{}).then(n.canvasToBlob)}function e(b,c){function d(a){return a instanceof HTMLCanvasElement?n.makeImage(a.toDataURL()):a.cloneNode(!1)}function f(a,b,c){function d(a,b,c){var d=Promise.resolve();return b.forEach(function(b){d=d.then(function(){return e(b,c)}).then(function(b){b&&a.appendChild(b)})}),d}var f=a.childNodes;return 0===f.length?Promise.resolve(b):d(b,n.asArray(f),c).then(function(){return b})}function g(b,c){function d(){function d(a,b){function c(a,b){n.asArray(a).forEach(function(c){b.setProperty(c,a.getPropertyValue(c),a.getPropertyPriority(c))})}a.cssText?b.cssText=a.cssText:c(a,b)}d(a.window.getComputedStyle(b),c.style)}function e(){function d(d){function e(b,c,d){function e(a){var b=a.getPropertyValue("content");return a.cssText+" content: "+b+";"}function f(a){function b(b){return b+": "+a.getPropertyValue(b)+(a.getPropertyPriority(b)?" !important":"")}return n.asArray(a).map(b).join("; ")+";"}var g="."+b+":"+c,h=d.cssText?e(d):f(d);return a.document.createTextNode(g+"{"+h+"}")}var f=a.window.getComputedStyle(b,d),g=f.getPropertyValue("content");if(""!==g&&"none"!==g){var h=n.uid();c.className=c.className+" "+h;var i=a.document.createElement("style");i.appendChild(e(h,d,f)),c.appendChild(i)}}[":before",":after"].forEach(function(a){d(a)})}function f(){b instanceof HTMLTextAreaElement&&(c.innerHTML=b.value)}function g(){c instanceof SVGElement&&c.setAttribute("xmlns","http://www.w3.org/2000/svg")}return c instanceof Element?Promise.resolve().then(d).then(e).then(f).then(g).then(function(){return c}):c}return c&&!c(b)?Promise.resolve():Promise.resolve(b).then(d).then(function(a){return f(b,a,c)}).then(function(a){return g(b,a)})}function f(a){return p.resolveAll().then(function(b){var c=document.createElement("style");return a.appendChild(c),c.appendChild(document.createTextNode(b)),a})}function g(a){return q.inlineAll(a).then(function(){return a})}function h(a,b,c){return Promise.resolve(a).then(function(a){return a.setAttribute("xmlns","http://www.w3.org/1999/xhtml"),(new XMLSerializer).serializeToString(a)}).then(n.escapeXhtml).then(function(a){return'<foreignObject x="0" y="0" width="100%" height="100%">'+a+"</foreignObject>"}).then(function(a){return'<svg xmlns="http://www.w3.org/2000/svg" width="'+b+'" height="'+c+'">'+a+"</svg>"}).then(function(a){return"data:image/svg+xml;charset=utf-8,"+a})}function i(a,c){function d(a){var b=document.createElement("canvas");return b.width=a.scrollWidth,b.height=a.scrollHeight,b}return b(a,c).then(n.makeImage).then(n.delay(100)).then(function(b){var c=d(a);return c.getContext("2d").drawImage(b,0,0),c})}function j(){function b(){var a="application/font-woff",b="image/jpeg";return{woff:a,woff2:a,ttf:"application/font-truetype",eot:"application/vnd.ms-fontobject",png:"image/png",jpg:b,jpeg:b,gif:"image/gif",tiff:"image/tiff",svg:"image/svg+xml"}}function c(a){var b=/\.([^\.\/]*?)$/g.exec(a);return b?b[1]:""}function d(a){var d=c(a).toLowerCase();return b()[d]||""}function e(a){return-1!==a.search(/^(data:)/)}function f(a){return new Promise(function(b){for(var c=window.atob(a.toDataURL().split(",")[1]),d=c.length,e=new Uint8Array(d),f=0;d>f;f++)e[f]=c.charCodeAt(f);b(new Blob([e],{type:"image/png"}))})}function g(a){return a.toBlob?new Promise(function(b){a.toBlob(b)}):f(a)}function h(b,c){var d=a.document.implementation.createHTMLDocument(),e=d.createElement("base");d.head.appendChild(e);var f=d.createElement("a");return d.body.appendChild(f),e.href=c,f.href=b,f.href}function i(){var a=0;return function(){function b(){return("0000"+(Math.random()*Math.pow(36,4)<<0).toString(36)).slice(-4)}return"u"+b()+a++}}function j(a){return new Promise(function(b,c){var d=new Image;d.onload=function(){b(d)},d.onerror=c,d.src=a})}function k(a){var b=3e4;return new Promise(function(c,d){function e(){if(4===g.readyState){if(200!==g.status)return void d(new Error("Cannot fetch resource "+a+", status: "+g.status));var b=new FileReader;b.onloadend=function(){var a=b.result.split(/,/)[1];c(a)},b.readAsDataURL(g.response)}}function f(){d(new Error("Timeout of "+b+"ms occured while fetching resource: "+a))}var g=new XMLHttpRequest;g.onreadystatechange=e,g.ontimeout=f,g.responseType="blob",g.timeout=b,g.open("GET",a,!0),g.send()})}function l(a,b){return"data:"+b+";base64,"+a}function m(a){return a.replace(/([.*+?^${}()|\[\]\/\\])/g,"\\$1")}function n(a){return function(b){return new Promise(function(c){setTimeout(function(){c(b)},a)})}}function o(a){for(var b=[],c=a.length,d=0;c>d;d++)b.push(a[d]);return b}function p(a){return a.replace(/#/g,"%23").replace(/\n/g,"%0A")}return{escape:m,parseExtension:c,mimeType:d,dataAsUrl:l,isDataUrl:e,canvasToBlob:g,resolveUrl:h,getAndEncode:k,uid:i(),delay:n,asArray:o,escapeXhtml:p,makeImage:j}}function k(){function a(a){return-1!==a.search(e)}function b(a){for(var b,c=[];null!==(b=e.exec(a));)c.push(b[1]);return c.filter(function(a){return!n.isDataUrl(a)})}function c(a,b,c,d){function e(a){return new RegExp("(url\\(['\"]?)("+n.escape(a)+")(['\"]?\\))","g")}return Promise.resolve(b).then(function(a){return c?n.resolveUrl(a,c):a}).then(d||n.getAndEncode).then(function(a){return n.dataAsUrl(a,n.mimeType(b))}).then(function(c){return a.replace(e(b),"$1"+c+"$3")})}function d(d,e,f){function g(){return!a(d)}return g()?Promise.resolve(d):Promise.resolve(d).then(b).then(function(a){var b=Promise.resolve(d);return a.forEach(function(a){b=b.then(function(b){return c(b,a,e,f)})}),b})}var e=/url\(['"]?([^'"]+?)['"]?\)/g;return{inlineAll:d,shouldProcess:a,impl:{readUrls:b,inline:c}}}function l(){function a(){return b(document).then(function(a){return Promise.all(a.map(function(a){return a.resolve()}))}).then(function(a){return a.join("\n")})}function b(){function a(a){return a.filter(function(a){return a.type===CSSRule.FONT_FACE_RULE}).filter(function(a){return o.shouldProcess(a.style.getPropertyValue("src"))})}function b(a){var b=[];return a.forEach(function(a){try{n.asArray(a.cssRules||[]).forEach(b.push.bind(b))}catch(c){console.log("Error while reading CSS rules from "+a.href,c.toString())}}),b}function c(a){return{resolve:function(){var b=(a.parentStyleSheet||{}).href;return o.inlineAll(a.cssText,b)},src:function(){return a.style.getPropertyValue("src")}}}return Promise.resolve(n.asArray(document.styleSheets)).then(b).then(a).then(function(a){return a.map(c)})}return{resolveAll:a,impl:{readAll:b}}}function m(){function a(a){function b(b){return n.isDataUrl(a.src)?Promise.resolve():Promise.resolve(a.src).then(b||n.getAndEncode).then(function(b){return n.dataAsUrl(b,n.mimeType(a.src))}).then(function(b){return new Promise(function(c,d){a.onload=c,a.onerror=d,a.src=b})})}return{inline:b}}function b(c){function d(a){var b=a.style.getPropertyValue("background");return b?o.inlineAll(b).then(function(b){a.style.setProperty("background",b,a.style.getPropertyPriority("background"))}).then(function(){return a}):Promise.resolve(a)}return c instanceof Element?d(c).then(function(){return c instanceof HTMLImageElement?a(c).inline():Promise.all(n.asArray(c.childNodes).map(function(a){return b(a)}))}):Promise.resolve(c)}return{inlineAll:b,impl:{newImage:a}}}var n=j(),o=k(),p=l(),q=m();a.domtoimage={toSvg:b,toPng:c,toBlob:d,impl:{fontFaces:p,images:q,util:n,inliner:o}}}(this);
</script>

</body>
</html>