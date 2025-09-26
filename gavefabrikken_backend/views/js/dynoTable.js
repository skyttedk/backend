var dynoTable = function(target,header){

    this.url = "tableData.php",
	this.tableId = "dynoTable";
	this.data = {};
    this.header = (header == null || header == "") ? [] : header;
    this.target = (target == null || target == "") ? "dynoTable" : target;
    this.dataRange = {current:0,range:30};
    this.action = "<td><input type=\"checkbox\" /></td><td>action</td>";
                                                       
    self =this;
	this.run = function(){
		this.loadTableData();
	};
	this.loadTableData = function(){
		$.post( "data.php", function(response) {
			self.data = response;
			self.deployTable();
		},"json");
    };
    this.deployTable = function(){
        var header = self.makeHeader();
        var html = self.makeTable();
        var table = "<table id=\""+this.tableId+"\">"+header+html+"</table>";
        $("#"+this.target).html(table);
    };

    this.makeHeader = function()
    {

        var html = "<tr>";
        for(var i=0;i<self.header.length;i++){
            html+="<th>"+self.header[i]+"</th>";
        }
        return html+="</tr>";
    }
    this.makeTable = function(){
		var html = "";
        // header
        $.each(self.data.tableData, function( index, value ) {
            html+="<tr id='"+index+"'>";
            $.each(value, function( key, value ) {
                html+="<td class='"+key+"'>"+value+"</td>";
            });
            html+= self.action;

        });
        return html;
	}


}
dynoTable = new dynoTable("dynoTable",["head1","head2","head3","active","action"]);
dynoTable.run();


