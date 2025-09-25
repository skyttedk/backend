var bizType = {

    trail : function(action){
        _editShopID = ""
        $("#companyList").hide();
        $("#overskriftValgtShop").html("");

        var html = '<button class="button">Opret ny valgshop</button>';
        if(action == "valgshops"){
            $("#frontMenu").show()
            _editShopID = ""
            html = '<button onclick="company.createNew()" class="button">Opret ny valgshop</button>';

        }
        if(action == "kort"){
            $("#frontMenu").hide()
            //var htmlContent="<iframe  width=100% height=700 src=\"/gavefabrikken_backend/index.php?rt=page/cardShop&token=asdf43sdha4f34o&systemuser_id="+_sysId+"\"></iframe>";
            var htmlContent="<iframe id=\"KortShopApp\" width=100% height=700 src=\"/gavefabrikken_backend/index.php?rt=unit/cardshop/main&token=asdf43sdha4f34o&systemuser_id="+_sysId+"&ram="+Math.floor(Math.random() * 10000)+" \"></iframe>";
            $("#content").html(htmlContent);
            window.onresize = function(event) {
                $("#KortShopApp").height($(window).height()-80)
            };
            $("#KortShopApp").height($(window).height()-80)
//            window.location.href = "http://94.143.10.74/gavefabrikken_backend/index.php?rt=page/cardShop&token=asdf43sdha4f34o";
        }
        if(action == "tilbud"){
            $("#frontMenu").show()
            _editShopID = ""
            html = '<button class="button">Opret nyt tilbud</button>'

        }
        if(action == "systemUser"){
            $("#frontMenu").show()
            _editShopID = ""
            html = ''
            systemUser.show()

        }
        if(action == "lager"){
            //window.location.href = "http://94.143.10.74/gavefabrikken_backend/index.php?rt=lager&login=dsfkjsadhferuifghriuejf3434fhsudif";
            $("#frontMenu").hide()
            var htmlContent="<iframe  width=100% height=700 src=\"/gavefabrikken_backend/index.php?rt=lager&login=dsfkjsadhferuifghriuejf3434fhsudif\"></iframe>";
            $("#content").html(htmlContent);
        }
        if(action == "infoBoard"){
            ajax({},"page/showInfoboard","","#content");

        }
        if(action == "shopboard"){

            window.open('https://gvs.gavefabrikken.dk/iframe/shopboard?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiOTNjOTUzN2Q5ZWJlMTI0OWYyZDA0N2E5OGY0ZWYyMGE4YzQ0ZGZiMGU2MjgwZTQ3MWU1N2MxY2M5NWY0YjhlMjAwY2JiZTRmNzc0MDFiOGUiLCJpYXQiOjE3NDk3NDE5NjMuMjk4NDUzLCJuYmYiOjE3NDk3NDE5NjMuMjk4NDU1LCJleHAiOjE3ODEyNzc5NjMuMjk2NjQ2LCJzdWIiOiIyNzQiLCJzY29wZXMiOltdfQ.kXLmlsZjmwBX2LieNouIlpOeOPRFWOeUvjHsnXXEUyjGFqJprHjR3GnyS5HOR8mHm5p_kRZnnqgjBJdlA1L4cthUKbldGPasxVgDTeyhc3KXl9I9egnjBXCeeI5Kcgfl8TjDHSCTRXrcPGsMCRryZPZu9whgcRCNEya7TiDCA4pwobjl8oaVXq3K5v_HHogPLdHmqO-Gx2Xwqax-xqGbyijIYX7Sp04PraZuq5ibsLM0okCeQ-bJ2iyJ7zQIOSDGsQ9LfYpqazq_aJhGk4F-4mfvVLg0DfNUpnAlJoUS0QhRGUiyalnDpfpVK7On3yHAN0HGgH9UShJCbvCTc6Hpc8o4qaLqHT-3jQGZHMgLiA8Fw6N6CJ7EtgqXehNqVPnD0x8BktYcUSKC_l7nAU26QJ_DbaWtsxUSHlfnr_xXtQyXpafcdFggV6KWkInmZsxJONlHu71aa8MJ6bsUP6YOCUBq7mhX-fp8FkK0FITPfC0J7PmxoKB0dT5paMqSklIGK6WEwVhHacmLWFJRwJHY4pimgTAxmnoM0qd0uqlCTkNNrBlaaopo6ObUO-PZ_8s-Iq4h8u_jCMYrMupRufJnk6Vz3gKnwDH9z5EqSkYhMs-GH_6ApW0mHBF0kkw5bPlgTnzI078ea2NYTItBLfAr_qsxsYXArHd7wRnBt96iGWY', '_blank');
            //window.open('https://gvs.gavefabrikken.dk/iframe/shopboard?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYzQxMjUzZWYzNDIxODU2YTFkYTA3NWM4ZjhjNzk4N2EwYTdmZGM1YjcyMDZhMzYwNGU3MmFlM2I1YmQyNzY0MWY4ZTg2ODc4MGRmNWRhZWUiLCJpYXQiOjE3MTYyOTQ0NDcuNjcwNjAzLCJuYmYiOjE3MTYyOTQ0NDcuNjcwNjA4LCJleHAiOjE3NDc4MzA0NDcuNjY3NTk4LCJzdWIiOiIyNzQiLCJzY29wZXMiOltdfQ.W7j1cc472XMMzj8hFli6UoHg2VNWHz-W9lZ-u_OaKNlGAB9HkCg3wpYEMj7hikVYvreEqlmKtB7rlSPZCAe-xUZDPiXePAM2tBVWZpiGXlfrRAoT2sBEmDLCQ5xKHdHO6UUt-FLs81JUGcXqiu9T9cSshI1x74n1sz7fa0bU6Oyw9EqEDHqjRSj7UF6NyNZm8FVcGqUw0KaVtvAJ_azn21MLUA696ZoLz7f2DR3Y3iIeE36AiRaoS97IxN2TT3rVbZKv96K00PzHBDACXB1FxFosLc4oahWJKp-U9zBO0ugtNyLCJ6NkQFWJ2UoEGGJc0v5FMzldkiNJr1tBFQqBEqafiZMEFfcQ3pMXaSRC7mKmfoZl5G0FN1G4VojXmu4UJrI6KiDWdSHSJ5kRHcVEi0abfiTNFTzh-ujBuiZbvlkZzI1JqbmQqLxZ8f8CYkR19lv423tPNQT3-k7zo2a8dEMkoV-D0x-rSWYlJiBNFXycMrsmEm4AxrxeJB62YFk4dpLmJOh0relCkxbBlQHM74Tfmc1JyOJuG-eL8RKgGDE-ab7dUjFs4xcCJUWy8otM7fR-PpyEGm4Ln9YKEfN2hBpdycTyTaYxC9HCqAnl9spxO9dD_LNZHvpfq-xIoHJHfpreOZGJrbRS7QRp3esbuZ13JmpAbzizykL-cTqzF8w', '_blank');
            //window.open('index.php?rt=shopboard&login=dsfkjsadhferuifghriuejf3434fhsudif', '_blank');
            // ajax({},"page/showShopboard","","#content");

        }
        if(action == "showSuperAdmin"){
            $("#frontMenu").hide();
            var htmlContent="<iframe  width=100% height=700 src=\"/gavefabrikken_backend/index.php?rt=earlypresent&login=dsfkjsadhferuifghriuejf3434fhsudif\"></iframe>";
            $("#content").html(htmlContent);
        }
        if(action == "showMyPage"){
            $("#frontMenu").hide();
            var htmlContent='<iframe style=" overflow: hidden; height: calc(100vh - 80px); width: 95vw;"  src="/gavefabrikken_backend/index.php?rt=myPage&login=dsfkjsadhferuifghriuejf3434fhsudif"></iframe>';
            $("#content").html(htmlContent);
        }
        if(action == "presentComplaint"){
            $("#frontMenu").hide();
            var htmlContent='<iframe id="PresentComplaintApp" width="100%" height="700" src="/gavefabrikken_backend/index.php?rt=unit/cardshop/presentcomplaint&token=asdf43sdha4f34o&systemuser_id='+_sysId+'&ram='+Math.floor(Math.random() * 10000)+'"></iframe>';
            $("#content").html(htmlContent);
            window.onresize = function(event) {
                $("#PresentComplaintApp").height($(window).height()-80)
            };
            $("#PresentComplaintApp").height($(window).height()-80)
        }


        $("#trailContainer").html(html)

    }
}