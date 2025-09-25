<!-- Central Modal Small -->
<div class="modal fade" id="modalChangeMultiPriceView" tabindex="-1" role="dialog" aria-labelledby="modalChangeMultiPrice"
     aria-hidden="true">

    <!-- Change class .modal-sm to change the size of the modal -->
    <div class="modal-dialog modal-lg" role="document">


        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title w-100" id="myModalLabel">Priser</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body ">
               <!-- <div id="csv">Download Item list</div>  -->
                <table width=210>
                <tr><td> <label><b>Show / hide all prices</b></label></td><td>    <label class="switch">
                                        <input id="showPrices" type="checkbox">
                                        <span class="slider round"></span>
                                </label></td></tr>

                </table>

            <center>

                <table class="modalChangeMultiPrice" ></table>
            </center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm changeMultiPrice-save">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- Central Modal Small -->